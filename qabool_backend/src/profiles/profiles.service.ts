import { Injectable, NotFoundException, ConflictException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, In } from 'typeorm';
import { User, UserStatus } from '../users/user.entity';
import { Connection, ConnectionStatus } from '../connections/connection.entity';
import { UpdateProfileDto } from './dto/update-profile.dto';
import { FavoritesService } from '../favorites/favorites.service';
import { BlockingService } from '../users/blocking.service';

import { SkippedUser } from './entities/skipped-user.entity';
import * as fs from 'fs';
import * as path from 'path';

@Injectable()
export class ProfilesService {
  constructor(
    @InjectRepository(User)
    private usersRepository: Repository<User>,
    @InjectRepository(Connection)
    private connectionsRepository: Repository<Connection>,
    @InjectRepository(SkippedUser)
    private skippedUserRepository: Repository<SkippedUser>,
    private favoritesService: FavoritesService,
    private blockingService: BlockingService,
  ) { }

  async populateUserExtraFields(user: User, currentUser: User): Promise<User> {
    user.isFavorited = await this.favoritesService.isFavorite(currentUser.id, user.id);

    // Check if skipped
    const skipped = await this.skippedUserRepository.findOne({
      where: { userId: currentUser.id, skippedUserId: user.id },
    });
    user.isSkipped = !!skipped;

    // Calculate isOnline (last 5 minutes)
    if (user.lastSeen) {
      const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
      user.isOnline = user.lastSeen > fiveMinutesAgo;
    } else {
      user.isOnline = false;
    }

    // Connection Status
    const currentId = currentUser.id?.toString();
    const targetId = user.id?.toString();

    if (currentId && targetId) {
      const connection = await this.connectionsRepository.findOne({
        where: [
          { requesterId: currentId, recipientId: targetId },
          { requesterId: targetId, recipientId: currentId },
        ],
      });

      console.log(`Checking connection between ${currentUser.email} (${currentId}) and ${user.email} (${targetId})`);
      if (connection) {
        console.log(`Connection found: status=${connection.status}, requesterId=${connection.requesterId}`);
        const requesterId = connection.requesterId.toString();

        if (connection.status === ConnectionStatus.ACCEPTED) {
          user.connectionStatus = 'ACCEPTED';
        } else if (connection.status === ConnectionStatus.REJECTED) {
          user.connectionStatus = 'NONE';
        } else if (connection.status === ConnectionStatus.PENDING) {
          if (requesterId === currentId) {
            user.connectionStatus = 'PENDING_SENT';
          } else {
            user.connectionStatus = 'PENDING_RECEIVED';
          }
        } else {
          user.connectionStatus = 'NONE';
        }
        user.connectionId = connection.id;
      } else {
        console.log('No connection found');
        user.connectionStatus = 'NONE';
        user.connectionId = undefined;
      }
    } else {
      user.connectionStatus = 'NONE';
    }
    console.log(`Set connectionStatus=${user.connectionStatus}`);

    return user;
  }

  async findAll(
    filters: { religion?: string; region?: string; ageMin?: number; ageMax?: number; gender?: string },
    currentUser: User,
  ) {
    const query = this.usersRepository.createQueryBuilder('user')
      .where('user.status = :status', { status: 'ACTIVE' })
      .andWhere('user.id != :currentUserId', { currentUserId: currentUser.id });

    // Blocking filter
    const blockedIds = await this.blockingService.getBlockedUserIds(currentUser.id);
    if (blockedIds.length > 0) {
      query.andWhere('user.id NOT IN (:...blockedIds)', { blockedIds });
    }

    // Determine target gender if not explicitly provided
    let targetGender = filters.gender;
    if (!targetGender) {
      if (currentUser.gender?.toLowerCase() === 'male') {
        targetGender = 'female';
      } else if (currentUser.gender?.toLowerCase() === 'female') {
        targetGender = 'male';
      }
    }

    if (targetGender) {
      query.andWhere('LOWER(user.gender) = :gender', { gender: targetGender.toLowerCase() });
    }

    if (filters.religion) {
      query.andWhere('user.religion LIKE :religion', { religion: `%${filters.religion}%` });
    }

    if (filters.region) {
      query.andWhere('user.region LIKE :region', { region: `%${filters.region}%` });
    }

    // 1. If I don't accept past issues, don't show me people who have them
    if (!currentUser.acceptsPastIssues) {
      query.andWhere('user.hasPastIssues = :noIssues', { noIssues: false });
    }
    // 2. If I HAVE past issues, only show me people who ACCEPT them
    if (currentUser.hasPastIssues) {
      query.andWhere('user.acceptsPastIssues = :acceptsOthersIssues', { acceptsOthersIssues: true });
    }

    const users = await query.getMany();

    if (currentUser.id) {
      for (const user of users) {
        await this.populateUserExtraFields(user, currentUser);
      }
    }

    return users;
  }

  async discover(currentUser: User): Promise<User[]> {
    const users = await this.usersRepository.find({
      where: { status: UserStatus.ACTIVE },
    });

    for (const user of users) {
      await this.populateUserExtraFields(user, currentUser);
    }

    return users;
  }

  async findOne(id: string, currentUserId?: string): Promise<User> {
    const user = await this.usersRepository.findOne({ where: { id } });
    if (!user) {
      throw new NotFoundException('Profile not found');
    }

    if (currentUserId) {
      // Check if blocked
      const isBlocked = await this.blockingService.isBlocked(currentUserId, id);
      if (isBlocked) {
        throw new NotFoundException('Profile not found');
      }

      const currentUser = await this.usersRepository.findOne({ where: { id: currentUserId } });
      if (currentUser) {
        await this.populateUserExtraFields(user, currentUser);
      } else {
        user.connectionStatus = 'NONE';
        user.isFavorited = false;
      }
    } else {
      user.connectionStatus = 'NONE';
      user.isFavorited = false;
    }

    return user;
  }

  async update(id: string, updateProfileDto: UpdateProfileDto, profileImagePath?: string): Promise<User> {
    const user = await this.findOne(id, id);

    // Check email uniqueness if it's being updated
    if (updateProfileDto.email && updateProfileDto.email !== user.email) {
      const existingUser = await this.usersRepository.findOne({
        where: { email: updateProfileDto.email },
      });
      if (existingUser && existingUser.id !== id) {
        throw new ConflictException('Email already exists');
      }
    }

    // Check phone number uniqueness if it's being updated
    if (updateProfileDto.phoneNumber && updateProfileDto.phoneNumber !== user.phoneNumber) {
      const existingUser = await this.usersRepository.findOne({
        where: { phoneNumber: updateProfileDto.phoneNumber },
      });
      if (existingUser && existingUser.id !== id) {
        throw new ConflictException('Phone number already exists');
      }
    }
    
    const updateData: any = { ...updateProfileDto };

    // Explicitly delete profileImageUrl from DTO to handle it separately
    delete updateData.profileImageUrl;

    if (profileImagePath) {
      // Delete old image if it exists
      if (user.profileImageUrl) {
        const relativeOldPath = user.profileImageUrl.startsWith('/') ? user.profileImageUrl.substring(1) : user.profileImageUrl;
        const oldImagePath = path.join(process.cwd(), relativeOldPath);
        if (fs.existsSync(oldImagePath)) {
          try {
            fs.unlinkSync(oldImagePath);
          } catch (err) {
            console.error(`Failed to delete old profile image: ${oldImagePath}`, err);
          }
        }
      }
      user.profileImageUrl = `/${profileImagePath.replace(/\\/g, '/')}`;
    }

    if (updateData.dob) {
      updateData.dob = new Date(updateData.dob);
    }

    // Remove null/undefined/empty string fields from updateData
    Object.keys(updateData).forEach(key => {
      if (updateData[key] === null || updateData[key] === undefined || updateData[key] === '') {
        delete updateData[key];
      }
    });

    Object.assign(user, updateData);
    user.updatedAt = new Date();
    return this.usersRepository.save(user);
  }

  async skipUser(userId: string, targetId: string): Promise<void> {
    if (userId === targetId) return;
    const existing = await this.skippedUserRepository.findOne({
      where: { userId, skippedUserId: targetId },
    });
    if (!existing) {
      const skip = this.skippedUserRepository.create({ userId, skippedUserId: targetId });
      await this.skippedUserRepository.save(skip);
    }
  }

  async unskipUser(userId: string, targetId: string): Promise<void> {
    await this.skippedUserRepository.delete({ userId, skippedUserId: targetId });
  }

  async getSkippedUsers(currentUser: User): Promise<User[]> {
    const skips = await this.skippedUserRepository.find({
      where: { userId: currentUser.id },
    });
    const skippedIds = skips.map((s) => s.skippedUserId);
    if (skippedIds.length === 0) return [];

    const users = await this.usersRepository.find({
      where: { id: In(skippedIds) },
    });

    for (const user of users) {
      await this.populateUserExtraFields(user, currentUser);
    }
    return users;
  }

  async getHomeProfiles(currentUser: User): Promise<User[]> {
    // 1. Get all active users except current user
    const query = this.usersRepository.createQueryBuilder('user')
      .where('user.status = :status', { status: UserStatus.ACTIVE })
      .andWhere('user.id != :currentUserId', { currentUserId: currentUser.id });

    // 2. Gender filter
    let targetGender = currentUser.gender?.toLowerCase() === 'male' ? 'female' : 'male';
    query.andWhere('LOWER(user.gender) = :gender', { gender: targetGender });

    // 3. Filter out connected users (only ACCEPTED status)
    const connectedQuery = this.connectionsRepository.createQueryBuilder('conn')
      .select('CASE WHEN conn.requesterId = :myId THEN conn.recipientId ELSE conn.requesterId END', 'userId')
      .where('(conn.requesterId = :myId OR conn.recipientId = :myId)')
      .andWhere('conn.status = :accepted', { accepted: ConnectionStatus.ACCEPTED })
      .setParameters({ myId: currentUser.id });

    const connectedUsers = await connectedQuery.getRawMany();
    const connectedIds = connectedUsers.map(u => u.userId);

    if (connectedIds.length > 0) {
      query.andWhere('user.id NOT IN (:...connectedIds)', { connectedIds });
    }

    // 4. Filter out skipped users
    const skippedQuery = this.skippedUserRepository.createQueryBuilder('skip')
      .select('skip.skippedUserId', 'userId')
      .where('skip.userId = :myId', { myId: currentUser.id });

    const skippedUsers = await skippedQuery.getRawMany();
    const skippedIds = skippedUsers.map(u => u.userId);

    if (skippedIds.length > 0) {
      query.andWhere('user.id NOT IN (:...skippedIds)', { skippedIds });
    }

    // Blocking filter
    const blockedIds = await this.blockingService.getBlockedUserIds(currentUser.id);
    if (blockedIds.length > 0) {
      query.andWhere('user.id NOT IN (:...blockedIds)', { blockedIds });
    }

    // Past Issues Filtering
    if (!currentUser.acceptsPastIssues) {
      query.andWhere('user.hasPastIssues = :noIssues', { noIssues: false });
    }
    if (currentUser.hasPastIssues) {
      query.andWhere('user.acceptsPastIssues = :acceptsOthersIssues', { acceptsOthersIssues: true });
    }

    const users = await query.getMany();
    for (const user of users) {
      await this.populateUserExtraFields(user, currentUser);
    }
    return users;
  }

  async getExploreProfiles(currentUser: User, includeConnected: boolean, includeSkipped: boolean): Promise<User[]> {
    const query = this.usersRepository.createQueryBuilder('user')
      .where('user.status = :status', { status: UserStatus.ACTIVE })
      .andWhere('user.id != :currentUserId', { currentUserId: currentUser.id }).orderBy('RANDOM()');

    // Gender filter
    let targetGender = currentUser.gender?.toLowerCase() === 'male' ? 'female' : 'male';
    query.andWhere('LOWER(user.gender) = :gender', { gender: targetGender });

    // Filter connected
    if (!includeConnected) {
      const connectedQuery = this.connectionsRepository.createQueryBuilder('conn')
        .select('CASE WHEN conn.requesterId = :myId THEN conn.recipientId ELSE conn.requesterId END', 'userId')
        .where('(conn.requesterId = :myId OR conn.recipientId = :myId)')
        .andWhere('conn.status = :accepted', { accepted: ConnectionStatus.ACCEPTED })
        .setParameters({ myId: currentUser.id });

      const connectedUsers = await connectedQuery.getRawMany();
      const connectedIds = connectedUsers.map(u => u.userId);

      if (connectedIds.length > 0) {
        query.andWhere('user.id NOT IN (:...connectedIds)', { connectedIds });
      }
    }

    // Filter skipped
    if (!includeSkipped) {
      const skippedQuery = this.skippedUserRepository.createQueryBuilder('skip')
        .select('skip.skippedUserId', 'userId')
        .where('skip.userId = :myId', { myId: currentUser.id });

      const skippedUsers = await skippedQuery.getRawMany();
      const skippedIds = skippedUsers.map(u => u.userId);

      if (skippedIds.length > 0) {
        query.andWhere('user.id NOT IN (:...skippedIds)', { skippedIds });
      }
    }

    // Blocking filter
    const blockedIds = await this.blockingService.getBlockedUserIds(currentUser.id);
    if (blockedIds.length > 0) {
      query.andWhere('user.id NOT IN (:...blockedIds)', { blockedIds });
    }

    // Past Issues Filtering
    if (!currentUser.acceptsPastIssues) {
      query.andWhere('user.hasPastIssues = :noIssues', { noIssues: false });
    }
    if (currentUser.hasPastIssues) {
      query.andWhere('user.acceptsPastIssues = :acceptsOthersIssues', { acceptsOthersIssues: true });
    }

    const users = await query.getMany();
    for (const user of users) {
      await this.populateUserExtraFields(user, currentUser);
    }
    return users;
  }
}

import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { User } from '../users/user.entity';
import { Connection, ConnectionStatus } from '../connections/connection.entity';
import { UpdateProfileDto } from './dto/update-profile.dto';
import { FavoritesService } from '../favorites/favorites.service';

@Injectable()
export class ProfilesService {
  constructor(
    @InjectRepository(User)
    private usersRepository: Repository<User>,
    @InjectRepository(Connection)
    private connectionsRepository: Repository<Connection>,
    private favoritesService: FavoritesService,
  ) {}

  async populateUserExtraFields(user: User, currentUser: User): Promise<User> {
    user.isFavorited = await this.favoritesService.isFavorite(currentUser.id, user.id);
    
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

    const users = await query.getMany();

    if (currentUser.id) {
      for (const user of users) {
        await this.populateUserExtraFields(user, currentUser);
      }
    }

    return users;
  }

  async findOne(id: string, currentUserId?: string): Promise<User> {
    const user = await this.usersRepository.findOne({ where: { id } });
    if (!user) {
      throw new NotFoundException('Profile not found');
    }

    if (currentUserId) {
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
    const user = await this.findOne(id);
    
    const updateData: any = { ...updateProfileDto };

    if (profileImagePath) {
      updateData.profileImageUrl = `/${profileImagePath.replace(/\\/g, '/')}`;
    }

    if (updateData.dob) {
      updateData.dob = new Date(updateData.dob);
    }

    Object.assign(user, updateData);
    return this.usersRepository.save(user);
  }
}

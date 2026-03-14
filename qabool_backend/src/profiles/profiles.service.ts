import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { User } from '../users/user.entity';
import { UpdateProfileDto } from './dto/update-profile.dto';
import { FavoritesService } from '../favorites/favorites.service';

@Injectable()
export class ProfilesService {
  constructor(
    @InjectRepository(User)
    private usersRepository: Repository<User>,
    private favoritesService: FavoritesService,
  ) {}

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
        user.isFavorited = await this.favoritesService.isFavorite(currentUser.id, user.id);
        
        // Calculate isOnline (last 5 minutes)
        if (user.lastSeen) {
          const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
          user.isOnline = user.lastSeen > fiveMinutesAgo;
        } else {
          user.isOnline = false;
        }

        if (user.isFavorited) {
          console.log(`User ${currentUser.id} has favorited ${user.id} (Found in list)`);
        }
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
      user.isFavorited = await this.favoritesService.isFavorite(currentUserId, user.id);
      console.log(`Checking if ${currentUserId} favorited ${user.id}: ${user.isFavorited}`);
    }

    // Calculate isOnline
    if (user.lastSeen) {
      const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
      user.isOnline = user.lastSeen > fiveMinutesAgo;
    } else {
      user.isOnline = false;
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

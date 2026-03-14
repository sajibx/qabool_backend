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
    currentUserId?: string,
  ) {
    const query = this.usersRepository.createQueryBuilder('user')
      .where('user.status = :status', { status: 'ACTIVE' });

    if (filters.religion) {
      query.andWhere('user.religion LIKE :religion', { religion: `%${filters.religion}%` });
    }
 
    if (filters.region) {
      query.andWhere('user.region LIKE :region', { region: `%${filters.region}%` });
    }

    if (filters.gender) {
      query.andWhere('user.gender = :gender', { gender: filters.gender });
    }

    const users = await query.getMany();

    if (currentUserId) {
      for (const user of users) {
        user.isFavorited = await this.favoritesService.isFavorite(currentUserId, user.id);
        if (user.isFavorited) {
          console.log(`User ${currentUserId} has favorited ${user.id} (Found in list)`);
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

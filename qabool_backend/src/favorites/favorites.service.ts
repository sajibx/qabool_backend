import { Injectable, ConflictException, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Favorite } from './entities/favorite.entity';
import { User } from '../users/user.entity';
import { ProfilesService } from '../profiles/profiles.service';
import { Inject, forwardRef } from '@nestjs/common';

import { MessagingGateway } from '../messaging/messaging.gateway';

@Injectable()
export class FavoritesService {
  constructor(
    @InjectRepository(Favorite)
    private favoritesRepository: Repository<Favorite>,
    @InjectRepository(User)
    private usersRepository: Repository<User>,
    @Inject(forwardRef(() => ProfilesService))
    private profilesService: ProfilesService,
    private messagingGateway: MessagingGateway,
  ) {}

  async addFavorite(userId: string, targetId: string) {
    if (userId === targetId) {
      throw new ConflictException('You cannot favorite yourself');
    }

    const existing = await this.favoritesRepository.findOne({
      where: { userId, targetId },
    });

    if (existing) {
      return existing;
    }

    const favorite = this.favoritesRepository.create({ userId, targetId });
    const saved = await this.favoritesRepository.save(favorite);

    // Notify target user
    const fromUser = await this.usersRepository.findOne({ where: { id: userId } });
    if (fromUser) {
      this.messagingGateway.notifyNewFavorite(targetId, fromUser);
    }

    return saved;
  }

  async removeFavorite(userId: string, targetId: string) {
    const result = await this.favoritesRepository.delete({ userId, targetId });
    if (result.affected === 0) {
      throw new NotFoundException('Favorite not found');
    }
  }

  async getMyFavorites(userId: string): Promise<User[]> {
    const favorites = await this.favoritesRepository.find({
      where: { userId },
      relations: ['target'],
    });
    
    const users = favorites.map(f => f.target);
    const currentUser = await this.usersRepository.findOne({ where: { id: userId } });
    
    if (currentUser) {
      for (const user of users) {
        await this.profilesService.populateUserExtraFields(user, currentUser);
      }
    }
    
    return users;
  }

  async getUsersWhoFavoritedMe(userId: string): Promise<User[]> {
    const favorites = await this.favoritesRepository.find({
      where: { targetId: userId },
      relations: ['user'],
    });
    
    const users = favorites.map(f => f.user);
    const currentUser = await this.usersRepository.findOne({ where: { id: userId } });
    
    if (currentUser) {
      for (const user of users) {
        await this.profilesService.populateUserExtraFields(user, currentUser);
      }
    }
    
    return users;
  }

  async isFavorite(userId: string, targetId: string): Promise<boolean> {
    const count = await this.favoritesRepository.count({
      where: { userId, targetId },
    });
    return count > 0;
  }
}

import { Injectable, ConflictException, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Favorite } from './entities/favorite.entity';
import { User } from '../users/user.entity';

@Injectable()
export class FavoritesService {
  constructor(
    @InjectRepository(Favorite)
    private favoritesRepository: Repository<Favorite>,
    @InjectRepository(User)
    private usersRepository: Repository<User>,
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
    return this.favoritesRepository.save(favorite);
  }

  async removeFavorite(userId: string, targetId: string) {
    const result = await this.favoritesRepository.delete({ userId, targetId });
    if (result.affected === 0) {
      throw new NotFoundException('Favorite not found');
    }
  }

  async getMyFavorites(userId: string) {
    const favorites = await this.favoritesRepository.find({
      where: { userId },
      relations: ['target'],
    });
    return favorites.map((f) => f.target);
  }

  async getUsersWhoFavoritedMe(userId: string) {
    const favorites = await this.favoritesRepository.find({
      where: { targetId: userId },
      relations: ['user'],
    });
    return favorites.map((f) => f.user);
  }

  async isFavorite(userId: string, targetId: string): Promise<boolean> {
    const count = await this.favoritesRepository.count({
      where: { userId, targetId },
    });
    return count > 0;
  }
}

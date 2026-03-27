import { Injectable, ConflictException, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { BlockedUser } from './blocked-user.entity';
import { User } from './user.entity';

@Injectable()
export class BlockingService {
  constructor(
    @InjectRepository(BlockedUser)
    private blockedUserRepository: Repository<BlockedUser>,
    @InjectRepository(User)
    private userRepository: Repository<User>,
  ) {}

  async blockUser(blockerId: string, blockedUserId: string): Promise<BlockedUser> {
    if (blockerId === blockedUserId) {
      throw new ConflictException('You cannot block yourself');
    }

    const blockedUser = await this.userRepository.findOne({ where: { id: blockedUserId } });
    if (!blockedUser) {
      throw new NotFoundException('User to block not found');
    }

    const existing = await this.blockedUserRepository.findOne({
      where: { blockerId, blockedUserId },
    });

    if (existing) {
      throw new ConflictException('User is already blocked');
    }

    const block = this.blockedUserRepository.create({ blockerId, blockedUserId });
    return this.blockedUserRepository.save(block);
  }

  async unblockUser(blockerId: string, blockedUserId: string): Promise<void> {
    const result = await this.blockedUserRepository.delete({ blockerId, blockedUserId });
    if (result.affected === 0) {
      throw new NotFoundException('Block record not found');
    }
  }

  async getBlockedUsers(blockerId: string): Promise<User[]> {
    const blocks = await this.blockedUserRepository.find({
      where: { blockerId },
      relations: ['blockedUser'],
    });
    return blocks.map(b => b.blockedUser);
  }

  async isBlocked(userAId: string, userBId: string): Promise<boolean> {
    const count = await this.blockedUserRepository.count({
      where: [
        { blockerId: userAId, blockedUserId: userBId },
        { blockerId: userBId, blockedUserId: userAId },
      ],
    });
    return count > 0;
  }

  async getBlockedUserIds(userId: string): Promise<string[]> {
    const blocks = await this.blockedUserRepository.find({
      where: [
        { blockerId: userId },
        { blockedUserId: userId },
      ],
    });
    
    const ids = new Set<string>();
    blocks.forEach(b => {
      ids.add(b.blockerId);
      ids.add(b.blockedUserId);
    });
    ids.delete(userId);
    return Array.from(ids);
  }
}

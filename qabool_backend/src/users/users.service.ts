import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, ILike } from 'typeorm';
import { User, UserStatus } from './user.entity';

@Injectable()
export class UsersService {
  constructor(
    @InjectRepository(User)
    private usersRepository: Repository<User>,
  ) {}

  async create(userData: Partial<User>): Promise<User> {
    const user = this.usersRepository.create(userData);
    return this.usersRepository.save(user);
  }

  async countByGender(gender: string, status?: UserStatus): Promise<number> {
    const where: any = { gender: ILike(gender) };
    if (status) {
      where.status = status;
    }
    return this.usersRepository.count({ where });
  }

  async findByEmail(email: string): Promise<User | null> {
    return this.usersRepository.findOne({ where: { email } });
  }

  async findByPhoneNumber(phoneNumber: string): Promise<User | null> {
    return this.usersRepository.findOne({ where: { phoneNumber } });
  }

  async findById(id: string): Promise<User | null> {
    return this.usersRepository.findOne({ where: { id } });
  }

  async update(id: string, userData: Partial<User>): Promise<User> {
    await this.usersRepository.update(id, userData);
    const updatedUser = await this.findById(id);
    if (!updatedUser) throw new Error('User not found');
    return updatedUser;
  }

  async updateLastSeen(id: string): Promise<void> {
    await this.usersRepository.update(id, { lastSeen: new Date() });
  }
}

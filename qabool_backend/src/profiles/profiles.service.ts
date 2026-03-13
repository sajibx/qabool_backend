import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { User } from '../users/user.entity';
import { UpdateProfileDto } from './dto/update-profile.dto';

@Injectable()
export class ProfilesService {
  constructor(
    @InjectRepository(User)
    private usersRepository: Repository<User>,
  ) {}

  async findAll(filters: { religion?: string; region?: string; ageMin?: number; ageMax?: number }) {
    const query = this.usersRepository.createQueryBuilder('user');

    if (filters.religion) {
      query.andWhere('user.religion = :religion', { religion: filters.religion });
    }

    if (filters.region) {
      query.andWhere('user.region = :region', { region: filters.region });
    }

    // Basic age filtering assuming we had an age field or calculated from dob
    // For now, just returning all with basic filters
    return query.getMany();
  }

  async findOne(id: string): Promise<User> {
    const user = await this.usersRepository.findOne({ where: { id } });
    if (!user) {
      throw new NotFoundException('Profile not found');
    }
    return user;
  }

  async update(id: string, updateProfileDto: UpdateProfileDto): Promise<User> {
    const user = await this.findOne(id);
    
    // Convert dob string to Date object if present
    const updateData = { ...updateProfileDto };
    if (updateProfileDto.dob) {
      updateData.dob = new Date(updateProfileDto.dob) as any;
    }

    Object.assign(user, updateData);
    return this.usersRepository.save(user);
  }
}

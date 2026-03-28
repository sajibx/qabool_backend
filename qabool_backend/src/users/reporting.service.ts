import { Injectable, ConflictException, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { ReportedUser } from './reported-user.entity';
import { User } from './user.entity';
import { ReportUserDto } from './dto/report-user.dto';

@Injectable()
export class ReportingService {
  constructor(
    @InjectRepository(ReportedUser)
    private reportedUserRepository: Repository<ReportedUser>,
    @InjectRepository(User)
    private userRepository: Repository<User>,
  ) {}

  async reportUser(reporterId: string, reportedUserId: string, dto: ReportUserDto): Promise<ReportedUser> {
    if (reporterId === reportedUserId) {
      throw new ConflictException('You cannot report yourself');
    }

    const reportedUser = await this.userRepository.findOne({ where: { id: reportedUserId } });
    if (!reportedUser) {
      throw new NotFoundException('User to report not found');
    }

    const existing = await this.reportedUserRepository.findOne({
      where: { reporterId, reportedUserId },
    });

    if (existing) {
      throw new ConflictException('You have already reported this user once');
    }

    const report = this.reportedUserRepository.create({
      reporterId,
      reportedUserId,
      reason: dto.reason,
    });

    return this.reportedUserRepository.save(report);
  }

  async getReports(reporterId: string): Promise<ReportedUser[]> {
    return this.reportedUserRepository.find({
      where: { reporterId },
      relations: ['reportedUser'],
    });
  }
}

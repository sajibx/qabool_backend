import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { UsersService } from './users.service';
import { UsersController } from './users.controller';
import { User } from './user.entity';

import { BlockedUser } from './blocked-user.entity';
import { BlockingService } from './blocking.service';
import { BlockingController } from './blocking.controller';

import { ReportedUser } from './reported-user.entity';
import { ReportingService } from './reporting.service';
import { ReportingController } from './reporting.controller';

@Module({
  imports: [TypeOrmModule.forFeature([User, BlockedUser, ReportedUser])],
  providers: [UsersService, BlockingService, ReportingService],
  controllers: [UsersController, BlockingController, ReportingController],
  exports: [UsersService, BlockingService, ReportingService],
})
export class UsersModule {}

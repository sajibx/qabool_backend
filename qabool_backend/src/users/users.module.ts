import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { UsersService } from './users.service';
import { UsersController } from './users.controller';
import { User } from './user.entity';

import { BlockedUser } from './blocked-user.entity';
import { BlockingService } from './blocking.service';
import { BlockingController } from './blocking.controller';

@Module({
  imports: [TypeOrmModule.forFeature([User, BlockedUser])],
  providers: [UsersService, BlockingService],
  controllers: [UsersController, BlockingController],
  exports: [UsersService, BlockingService],
})
export class UsersModule {}

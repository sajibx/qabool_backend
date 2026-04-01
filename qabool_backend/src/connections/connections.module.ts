import { Module, forwardRef } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { ConnectionsService } from './connections.service';
import { ConnectionsController } from './connections.controller';
import { Connection } from './connection.entity';
import { User } from '../users/user.entity';
import { MessagingModule } from '../messaging/messaging.module';
import { ProfilesModule } from '../profiles/profiles.module';
import { NotificationsModule } from '../notifications/notifications.module';

@Module({
  imports: [
    TypeOrmModule.forFeature([Connection, User]),
    MessagingModule,
    NotificationsModule,
    forwardRef(() => ProfilesModule),
  ],
  providers: [ConnectionsService],
  controllers: [ConnectionsController],
  exports: [ConnectionsService],
})
export class ConnectionsModule {}

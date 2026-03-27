import { Module, forwardRef } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { ProfilesService } from './profiles.service';
import { ProfilesController } from './profiles.controller';
import { User } from '../users/user.entity';
import { Connection } from '../connections/connection.entity';
import { FavoritesModule } from '../favorites/favorites.module';
import { ConnectionsModule } from '../connections/connections.module';
import { UsersModule } from '../users/users.module';

import { SkippedUser } from './entities/skipped-user.entity';

@Module({
  imports: [
    TypeOrmModule.forFeature([User, Connection, SkippedUser]),
    forwardRef(() => FavoritesModule),
    forwardRef(() => ConnectionsModule),
    UsersModule,
  ],
  providers: [ProfilesService],
  controllers: [ProfilesController],
  exports: [ProfilesService],
})
export class ProfilesModule {}

import { Module, forwardRef } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { ProfilesService } from './profiles.service';
import { ProfilesController } from './profiles.controller';
import { User } from '../users/user.entity';
import { Connection } from '../connections/connection.entity';
import { FavoritesModule } from '../favorites/favorites.module';
import { ConnectionsModule } from '../connections/connections.module';

@Module({
  imports: [
    TypeOrmModule.forFeature([User, Connection]),
    forwardRef(() => FavoritesModule),
    forwardRef(() => ConnectionsModule),
  ],
  providers: [ProfilesService],
  controllers: [ProfilesController],
  exports: [ProfilesService],
})
export class ProfilesModule {}

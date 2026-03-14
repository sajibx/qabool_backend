import { Controller, Post, Delete, Get, Param, UseGuards, UnauthorizedException } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { FavoritesService } from './favorites.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { GetUser } from '../common/decorators/get-user.decorator';
import { User } from '../users/user.entity';
import { MessagingGateway } from '../messaging/messaging.gateway';

@ApiTags('favorites')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('favorites')
export class FavoritesController {
  constructor(
    private readonly favoritesService: FavoritesService,
    private readonly messagingGateway: MessagingGateway,
  ) {}

  @Post(':id')
  @ApiOperation({ summary: 'Add a user to favorites' })
  async addFavorite(@GetUser() user: User, @Param('id') targetId: string) {
    const favorite = await this.favoritesService.addFavorite(user.id, targetId);
    
    // Notify target user in realtime
    this.messagingGateway.server.to(`user_${targetId}`).emit('new_favorite', {
      from: {
        id: user.id,
        firstName: user.firstName,
        lastName: user.lastName,
        profileImageUrl: user.profileImageUrl,
      },
    });

    return favorite;
  }

  @Delete(':id')
  @ApiOperation({ summary: 'Remove a user from favorites' })
  async removeFavorite(@GetUser() user: User, @Param('id') targetId: string) {
    await this.favoritesService.removeFavorite(user.id, targetId);
    return { success: true };
  }

  @Get('my')
  @ApiOperation({ summary: 'List users I have favorited' })
  async getMyFavorites(@GetUser() user: User) {
    return this.favoritesService.getMyFavorites(user.id);
  }

  @Get('by-whom')
  @ApiOperation({ summary: 'List users who have favorited me' })
  async getUsersWhoFavoritedMe(@GetUser() user: User) {
    return this.favoritesService.getUsersWhoFavoritedMe(user.id);
  }
}

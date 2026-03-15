import { Controller, Get, Post, Body, Param, UseGuards, Query } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { MessagingService } from './messaging.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { MessagingGateway } from './messaging.gateway';
import { GetUser } from '../common/decorators/get-user.decorator';
import { User } from '../users/user.entity';

@ApiTags('chats')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('chats')
export class MessagingController {
  constructor(
    private readonly messagingService: MessagingService,
    private readonly messagingGateway: MessagingGateway,
  ) {}

  @Get()
  @ApiOperation({ summary: 'List recent conversations' })
  async findMyChats(@GetUser() user: User) {
    return this.messagingService.findUserChats(user.id);
  }

  @Get(':id/messages')
  @ApiOperation({ summary: 'Fetch message history with pagination' })
  async findChatMessages(
    @Param('id') id: string,
    @Query('page') page: number = 1,
    @Query('limit') limit: number = 20,
  ) {
    return this.messagingService.findChatMessages(id, +page, +limit);
  }

  @Post(':id/read')
  @ApiOperation({ summary: 'Mark messages in a chat as read' })
  async markAsRead(@GetUser() user: User, @Param('id') id: string) {
    await this.messagingService.markAsRead(id, user.id);
    
    // Find other participant to notify
    const chats = await this.messagingService.findUserChats(user.id);
    const chat = chats.find(c => c.id === id);
    if (chat && chat.participants) {
      const otherUser = chat.participants.find(p => p.id !== user.id);
      if (otherUser) {
        this.messagingGateway.notifyMessagesRead(id, user.id, otherUser.id);
      }
    }
    
    return { success: true };
  }

  @Post()
  @ApiOperation({ summary: 'Create or find a chat' })
  async createChat(@GetUser() user: User, @Body('recipientId') recipientId: string) {
    return this.messagingService.findOrCreateChat([user.id, recipientId]);
  }
}

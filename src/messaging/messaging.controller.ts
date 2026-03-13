import { Controller, Get, Post, Body, Param, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { MessagingService } from './messaging.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { GetUser } from '../common/decorators/get-user.decorator';
import { User } from '../users/user.entity';

@ApiTags('chats')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('chats')
export class MessagingController {
  constructor(private readonly messagingService: MessagingService) {}

  @Get()
  @ApiOperation({ summary: 'List recent conversations' })
  async findMyChats(@GetUser() user: User) {
    return this.messagingService.findUserChats(user.id);
  }

  @Get(':id/messages')
  @ApiOperation({ summary: 'Fetch message history' })
  async findChatMessages(@Param('id') id: string) {
    return this.messagingService.findChatMessages(id);
  }

  @Post()
  @ApiOperation({ summary: 'Create or find a chat' })
  async createChat(@GetUser() user: User, @Body('recipientId') recipientId: string) {
    return this.messagingService.findOrCreateChat([user.id, recipientId]);
  }
}

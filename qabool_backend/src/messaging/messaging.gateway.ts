import {
  WebSocketGateway,
  SubscribeMessage,
  MessageBody,
  WebSocketServer,
  ConnectedSocket,
  OnGatewayConnection,
  OnGatewayDisconnect,
} from '@nestjs/websockets';
import { Server, Socket } from 'socket.io';
import { UseGuards, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { MessagingService } from './messaging.service';
import { MessageType } from './entities/message.entity';
import { UsersService } from '../users/users.service';

@WebSocketGateway({
  cors: {
    origin: '*',
  },
})
export class MessagingGateway implements OnGatewayConnection, OnGatewayDisconnect {
  @WebSocketServer()
  server: Server;

  constructor(
    private readonly jwtService: JwtService,
    private readonly messagingService: MessagingService,
    private readonly usersService: UsersService,
  ) {}

  async handleConnection(client: Socket) {
    try {
      const token = client.handshake.auth.token || client.handshake.headers.authorization?.split(' ')[1];
      if (!token) {
        throw new UnauthorizedException();
      }
      const payload = await this.jwtService.verifyAsync(token);
      client.data.user = payload;
      const userId = payload.sub.toString();
      client.join(`user_${userId}`);
      await this.usersService.updateLastSeen(userId);
      console.log(`Socket ${client.id} joined room: user_${userId}`);
    } catch (e) {
      client.disconnect();
    }
  }

  handleDisconnect(client: Socket) {
    const user = client.data.user;
    if (user) {
      const userId = user.sub.toString();
      client.leave(`user_${userId}`);
      console.log(`Socket ${client.id} left room: user_${userId}`);
    }
  }

  @SubscribeMessage('send_message')
  async handleMessage(
    @MessageBody() data: { chatId: string; recipientId: string; content: string; type: MessageType },
    @ConnectedSocket() client: Socket,
  ) {
    const senderId = client.data.user.sub.toString();
    const recipientId = data.recipientId?.toString();
    
    console.log(`[send_message] senderId: ${senderId}, recipientId: ${recipientId}, chatId: ${data.chatId}`);

    if (!recipientId) {
      console.error(`[send_message] Missing recipientId for sender ${senderId}`);
      return;
    }
    
    await this.usersService.updateLastSeen(senderId);
    const message = await this.messagingService.saveMessage(data.chatId, senderId, data.content, data.type);
    
    console.log(`[send_message] Message saved. Emitting to user_${senderId} and user_${recipientId}`);
    
    // Emit to both parties
    this.server.to(`user_${senderId}`).emit('new_message', message);
    this.server.to(`user_${recipientId}`).emit('new_message', message);
    
    return message;
  }

  @SubscribeMessage('send_image_p2p')
  async handleImageP2P(
    @MessageBody() data: { chatId: string; recipientId: string; content: string },
    @ConnectedSocket() client: Socket,
  ) {
    const senderId = client.data.user.sub.toString();
    const recipientId = data.recipientId?.toString();
    
    if (!recipientId) return;
    
    await this.usersService.updateLastSeen(senderId);
    
    // Relay image without saving
    const ephemeralMessage = {
      id: `temp-${Date.now()}`,
      chatId: data.chatId,
      senderId: senderId,
      content: data.content,
      type: MessageType.IMAGE_P2P,
      status: 'SENT',
      createdAt: new Date().toISOString(),
    };
    
    this.server.to(`user_${senderId}`).emit('new_message', ephemeralMessage);
    this.server.to(`user_${recipientId}`).emit('new_message', ephemeralMessage);
  }

  @SubscribeMessage('typing_status')
  async handleTyping(
    @MessageBody() data: { chatId: string; recipientId: string; isTyping: boolean },
    @ConnectedSocket() client: Socket,
  ) {
    const senderId = client.data.user.sub.toString();
    const recipientId = data.recipientId.toString();
    
    await this.usersService.updateLastSeen(senderId);
    this.server.to(`user_${recipientId}`).emit('typing_status', {
      chatId: data.chatId,
      senderId,
      isTyping: data.isTyping,
    });
  }

  @SubscribeMessage('heartbeat')
  async handleHeartbeat(@ConnectedSocket() client: Socket) {
    if (client.data.user) {
      const userId = client.data.user.sub.toString();
      await this.usersService.updateLastSeen(userId);
    }
  }
  
  notifyMessagesRead(chatId: string, readerId: string, recipientId: string) {
    this.server.to(`user_${recipientId}`).emit('messages_read', {
      chatId,
      readerId,
    });
  }

  notifyConnectionRequest(recipientId: string, requester: any) {
    this.server.to(`user_${recipientId}`).emit('new_connection_request', {
      requester,
      message: `${requester.firstName} sent you a connection request`,
    });
  }

  notifyNewFavorite(targetId: string, fromUser: any) {
    this.server.to(`user_${targetId}`).emit('new_favorite', {
      from: {
        id: fromUser.id,
        firstName: fromUser.firstName,
        lastName: fromUser.lastName,
        profileImageUrl: fromUser.profileImageUrl,
      },
    });
  }

  notifyNotification(recipientId: string, notification: any) {
    this.server.to(`user_${recipientId}`).emit('new_notification', notification);
  }
}

import {
  WebSocketGateway,
  SubscribeMessage,
  MessageBody,
  WebSocketServer,
  ConnectedSocket,
  OnGatewayConnection,
} from '@nestjs/websockets';
import { Server, Socket } from 'socket.io';
import { UseGuards, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { MessagingService } from './messaging.service';
import { MessageType } from './entities/message.entity';

@WebSocketGateway({
  cors: {
    origin: '*',
  },
})
export class MessagingGateway implements OnGatewayConnection {
  @WebSocketServer()
  server: Server;

  constructor(
    private readonly jwtService: JwtService,
    private readonly messagingService: MessagingService,
  ) {}

  async handleConnection(client: Socket) {
    try {
      const token = client.handshake.auth.token || client.handshake.headers.authorization?.split(' ')[1];
      if (!token) {
        throw new UnauthorizedException();
      }
      const payload = await this.jwtService.verifyAsync(token);
      client.data.user = payload;
      client.join(`user_${payload.sub}`);
    } catch (e) {
      client.disconnect();
    }
  }

  @SubscribeMessage('send_message')
  async handleMessage(
    @MessageBody() data: { chatId: string; recipientId: string; content: string; type: MessageType },
    @ConnectedSocket() client: Socket,
  ) {
    const senderId = client.data.user.sub;
    const message = await this.messagingService.saveMessage(data.chatId, senderId, data.content, data.type);
    
    // Emit to both parties
    this.server.to(`user_${senderId}`).emit('new_message', message);
    this.server.to(`user_${data.recipientId}`).emit('new_message', message);
    
    return message;
  }

  @SubscribeMessage('typing_status')
  handleTyping(
    @MessageBody() data: { chatId: string; recipientId: string; isTyping: boolean },
    @ConnectedSocket() client: Socket,
  ) {
    const senderId = client.data.user.sub;
    this.server.to(`user_${data.recipientId}`).emit('typing_status', {
      chatId: data.chatId,
      senderId,
      isTyping: data.isTyping,
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
}

import { Injectable, NotFoundException, BadRequestException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, Not } from 'typeorm';
import { Connection, ConnectionStatus } from './connection.entity';
import { MessagingGateway } from '../messaging/messaging.gateway';
import { User } from '../users/user.entity';
import { ProfilesService } from '../profiles/profiles.service';
import { Inject, forwardRef } from '@nestjs/common';
import { NotificationsService } from '../notifications/notifications.service';
import { NotificationType } from '../notifications/entities/notification.entity';

@Injectable()
export class ConnectionsService {
  constructor(
    @InjectRepository(Connection)
    private connectionsRepository: Repository<Connection>,
    @InjectRepository(User)
    private usersRepository: Repository<User>,
    private messagingGateway: MessagingGateway,
    @Inject(forwardRef(() => ProfilesService))
    private profilesService: ProfilesService,
    private notificationsService: NotificationsService,
  ) {}

  async createRequest(requesterId: string, recipientId: string): Promise<Connection> {
    if (requesterId === recipientId) {
      throw new BadRequestException('You cannot connect with yourself');
    }

    const existing = await this.connectionsRepository.findOne({
      where: [
        { requesterId, recipientId },
        { requesterId: recipientId, recipientId: requesterId },
      ],
    });

    if (existing) {
      if (existing.status === ConnectionStatus.REJECTED) {
        // Allow retry if previously rejected
        existing.status = ConnectionStatus.PENDING;
        existing.requesterId = requesterId;
        existing.recipientId = recipientId;
        const saved = await this.connectionsRepository.save(existing);
        
        // Notify
        const requester = await this.usersRepository.findOne({ where: { id: requesterId } });
        if (requester) {
          this.messagingGateway.notifyConnectionRequest(recipientId, requester);
          
          // Create persistent notification
          await this.notificationsService.create(
            recipientId,
            requesterId,
            NotificationType.CONNECTION_REQUEST,
            `${requester.firstName} sent you a connection request`,
          );
        }
        
        return saved;
      }
      throw new BadRequestException('Connection request already exists');
    }

    const connection = this.connectionsRepository.create({
      requesterId,
      recipientId,
      status: ConnectionStatus.PENDING,
    });

    const saved = await this.connectionsRepository.save(connection);

    // Notify
    const reqIdStr = requesterId.toString();
    const recIdStr = recipientId.toString();
    
    console.log(`Notifying connection request: req=${reqIdStr}, rec=${recIdStr}`);
    
    const requester = await this.usersRepository.findOne({ where: { id: reqIdStr } });
    if (requester) {
      this.messagingGateway.notifyConnectionRequest(recIdStr, requester);
      
      // Create persistent notification
      await this.notificationsService.create(
        recIdStr,
        reqIdStr,
        NotificationType.CONNECTION_REQUEST,
        `${requester.firstName} sent you a connection request`,
      );
    }

    return saved;
  }

  async findAll(userId: string) {
    const connections = await this.connectionsRepository.find({
      where: [
        { requesterId: userId, status: Not(ConnectionStatus.REJECTED) },
        { recipientId: userId, status: Not(ConnectionStatus.REJECTED) },
      ],
      relations: ['requester', 'recipient'],
    });

    const currentUser = await this.usersRepository.findOne({ where: { id: userId } });
    if (currentUser) {
      for (const connection of connections) {
        if (connection.requester) {
          await this.profilesService.populateUserExtraFields(connection.requester, currentUser);
        }
        if (connection.recipient) {
          await this.profilesService.populateUserExtraFields(connection.recipient, currentUser);
        }
      }
    }

    return connections;
  }

  async updateStatus(id: string, userId: string, status: ConnectionStatus) {
    const connection = await this.connectionsRepository.findOne({ where: { id } });
    
    if (!connection) {
      throw new NotFoundException('Connection not found');
    }

    // Recipient can respond to PENDING
    // Requester can withdraw PENDING
    const isRecipient = connection.recipientId.toString() === userId.toString();
    const isRequester = connection.requesterId.toString() === userId.toString();

    if (!isRecipient && !isRequester) {
      throw new BadRequestException('You are not part of this connection');
    }

    if (status === ConnectionStatus.REJECTED) {
      // If rejected or withdrawn, just delete the record to "reset"
      await this.connectionsRepository.remove(connection);
      return { id, status: 'DELETED' };
    }

    if (!isRecipient) {
      throw new BadRequestException('Only the recipient can accept the request');
    }

    connection.status = status;
    const saved = await this.connectionsRepository.save(connection);

    if (status === ConnectionStatus.ACCEPTED) {
      const accepter = await this.usersRepository.findOne({ where: { id: userId } });
      if (accepter) {
        // Notify the person who SENT the request
        await this.notificationsService.create(
          connection.requesterId,
          userId,
          NotificationType.CONNECTION_ACCEPTED,
          `${accepter.firstName} accepted your connection request`,
        );
      }
    }

    return saved;
  }
}

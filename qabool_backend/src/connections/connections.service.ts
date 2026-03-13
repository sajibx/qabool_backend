import { Injectable, NotFoundException, BadRequestException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Connection, ConnectionStatus } from './connection.entity';

@Injectable()
export class ConnectionsService {
  constructor(
    @InjectRepository(Connection)
    private connectionsRepository: Repository<Connection>,
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
      throw new BadRequestException('Connection request already exists');
    }

    const connection = this.connectionsRepository.create({
      requesterId,
      recipientId,
      status: ConnectionStatus.PENDING,
    });

    return this.connectionsRepository.save(connection);
  }

  async findAll(userId: string) {
    return this.connectionsRepository.find({
      where: [
        { requesterId: userId },
        { recipientId: userId },
      ],
      relations: ['requester', 'recipient'],
    });
  }

  async updateStatus(id: string, userId: string, status: ConnectionStatus) {
    const connection = await this.connectionsRepository.findOne({ where: { id } });
    
    if (!connection) {
      throw new NotFoundException('Connection not found');
    }

    if (connection.recipientId !== userId) {
      throw new BadRequestException('Only the recipient can accept/reject the request');
    }

    connection.status = status;
    return this.connectionsRepository.save(connection);
  }
}

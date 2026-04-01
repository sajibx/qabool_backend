import { Injectable, NotFoundException, Inject, forwardRef } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Notification, NotificationType } from './entities/notification.entity';
import { MessagingGateway } from '../messaging/messaging.gateway';

@Injectable()
export class NotificationsService {
  constructor(
    @InjectRepository(Notification)
    private notificationsRepository: Repository<Notification>,
    @Inject(forwardRef(() => MessagingGateway))
    private messagingGateway: MessagingGateway,
  ) {}

  async create(userId: string, senderId: string, type: NotificationType, message: string) {
    // 1. Create and save the new notification
    const notification = this.notificationsRepository.create({
      userId,
      senderId,
      type,
      message,
    });
    const saved = await this.notificationsRepository.save(notification);

    // 2. Fetch full notification with sender info for real-time delivery
    const fullNotification = await this.notificationsRepository.findOne({
      where: { id: saved.id },
      relations: ['sender'],
    });

    // 3. Emit real-time notification via WebSocket
    if (fullNotification) {
      this.messagingGateway.notifyNotification(userId, fullNotification);
    }

    // 4. Enforce the 20-notification limit
    const count = await this.notificationsRepository.count({ where: { userId } });
    if (count > 20) {
      const oldestNotifications = await this.notificationsRepository.find({
        where: { userId },
        order: { createdAt: 'DESC' },
        skip: 20,
      });

      if (oldestNotifications.length > 0) {
        await this.notificationsRepository.remove(oldestNotifications);
      }
    }

    return fullNotification;
  }

  async findAll(userId: string) {
    return this.notificationsRepository.find({
      where: { userId },
      order: { createdAt: 'DESC' },
      relations: ['sender'],
      take: 20,
    });
  }

  async markAsRead(id: string, userId: string) {
    const notification = await this.notificationsRepository.findOne({
      where: { id, userId },
    });

    if (!notification) {
      throw new NotFoundException('Notification not found');
    }

    notification.isRead = true;
    return this.notificationsRepository.save(notification);
  }

  async markAllAsRead(userId: string) {
    await this.notificationsRepository.update({ userId, isRead: false }, { isRead: true });
    return { success: true };
  }

  async getUnreadCount(userId: string) {
    const count = await this.notificationsRepository.count({
      where: { userId, isRead: false },
    });
    return { count };
  }
}

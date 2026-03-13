import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, In } from 'typeorm';
import { Chat } from './entities/chat.entity';
import { Message, MessageType, MessageStatus } from './entities/message.entity';
import { User } from '../users/user.entity';
import { Not } from 'typeorm';

@Injectable()
export class MessagingService {
  constructor(
    @InjectRepository(Chat)
    private chatsRepository: Repository<Chat>,
    @InjectRepository(Message)
    private messagesRepository: Repository<Message>,
    @InjectRepository(User)
    private usersRepository: Repository<User>,
  ) {}

  async findUserChats(userId: string) {
    const chats = await this.chatsRepository
      .createQueryBuilder('chat')
      .innerJoin('chat.participants', 'participant', 'participant.id = :userId', { userId })
      .leftJoinAndSelect('chat.participants', 'allParticipants')
      .innerJoinAndSelect('chat.messages', 'messages')
      .orderBy('messages.createdAt', 'DESC')
      .getMany();

    // Deduplicate chats (QueryBuilder with innerJoin on messages might return same chat multiple times)
    const uniqueChats = Array.from(new Map(chats.map(chat => [chat.id, chat])).values());

    return Promise.all(uniqueChats.map(async (chat) => {
      // Sort messages by creation date to ensure lastMessage is correct
      chat.messages.sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime());
      
      const unreadCount = await this.messagesRepository.count({
        where: {
          chatId: chat.id,
          senderId: Not(userId),
          status: Not(MessageStatus.READ),
        },
      });
      return { 
        ...chat, 
        unreadCount,
        lastMessage: chat.messages[0] // Useful for frontend
      };
    }));
  }

  async markAsRead(chatId: string, userId: string) {
    await this.messagesRepository.update(
      { chatId, senderId: Not(userId), status: Not(MessageStatus.READ) },
      { status: MessageStatus.READ }
    );
  }

  async findChatMessages(chatId: string) {
    return this.messagesRepository.find({
      where: { chatId },
      order: { createdAt: 'ASC' },
      relations: ['sender'],
    });
  }

  async saveMessage(chatId: string, senderId: string, content: string, type: MessageType) {
    const message = this.messagesRepository.create({
      chatId,
      senderId,
      content,
      type,
    });
    return this.messagesRepository.save(message);
  }

  async findOrCreateChat(participantIds: string[]) {
    // For 1-on-1 chats, check if one already exists
    if (participantIds.length === 2) {
      const chats = await this.chatsRepository.find({
        relations: ['participants'],
      });

      const existingChat = chats.find(chat => {
        const ids = chat.participants.map(p => p.id);
        return ids.length === 2 && 
               ids.includes(participantIds[0]) && 
               ids.includes(participantIds[1]);
      });

      if (existingChat) return existingChat;
    }

    const participants = await this.usersRepository.find({
      where: { id: In(participantIds) },
    });
    
    const chat = this.chatsRepository.create({ participants });
    return this.chatsRepository.save(chat);
  }

  async createChat(participantIds: string[]) {
    return this.findOrCreateChat(participantIds);
  }
}

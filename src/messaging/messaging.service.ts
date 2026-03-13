import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, In } from 'typeorm';
import { Chat } from './entities/chat.entity';
import { Message, MessageType } from './entities/message.entity';
import { User } from '../users/user.entity';

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
    return this.chatsRepository.find({
      where: { participants: { id: userId } },
      relations: ['participants', 'messages'],
      order: { createdAt: 'DESC' },
    });
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

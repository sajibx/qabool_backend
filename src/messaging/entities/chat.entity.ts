import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, OneToMany, ManyToMany, JoinTable } from 'typeorm';
import { ApiProperty } from '@nestjs/swagger';
import { Message } from './message.entity';
import { User } from '../../users/user.entity';

@Entity('chats')
export class Chat {
  @ApiProperty()
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @ApiProperty({ type: () => [User] })
  @ManyToMany(() => User)
  @JoinTable()
  participants: User[];

  @OneToMany(() => Message, (message: Message) => message.chat)
  messages: Message[];

  @ApiProperty()
  @CreateDateColumn()
  createdAt: Date;
}

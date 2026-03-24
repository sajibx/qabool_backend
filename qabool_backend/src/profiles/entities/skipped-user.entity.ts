import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, ManyToOne, Unique, JoinColumn } from 'typeorm';
import { User } from '../../users/user.entity';
import { ApiProperty } from '@nestjs/swagger';

@Entity('skipped_users')
@Unique(['user', 'skippedUser'])
export class SkippedUser {
  @ApiProperty()
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @ManyToOne(() => User)
  @JoinColumn({ name: 'userId' })
  user: User;

  @ApiProperty()
  @Column()
  userId: string;

  @ManyToOne(() => User)
  @JoinColumn({ name: 'skippedUserId' })
  skippedUser: User;

  @ApiProperty()
  @Column()
  skippedUserId: string;

  @ApiProperty()
  @CreateDateColumn()
  createdAt: Date;
}

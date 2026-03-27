import { Entity, PrimaryGeneratedColumn, Column, ManyToOne, CreateDateColumn, Unique } from 'typeorm';
import { User } from './user.entity';

@Entity('blocked_users')
@Unique(['blockerId', 'blockedUserId'])
export class BlockedUser {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  blockerId: string;

  @ManyToOne(() => User)
  blocker: User;

  @Column()
  blockedUserId: string;

  @ManyToOne(() => User)
  blockedUser: User;

  @CreateDateColumn()
  createdAt: Date;
}

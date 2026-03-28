import { Entity, PrimaryGeneratedColumn, Column, ManyToOne, CreateDateColumn, Unique } from 'typeorm';
import { User } from './user.entity';

@Entity('reported_users')
@Unique(['reporterId', 'reportedUserId'])
export class ReportedUser {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  reporterId: string;

  @ManyToOne(() => User)
  reporter: User;

  @Column()
  reportedUserId: string;

  @ManyToOne(() => User)
  reportedUser: User;

  @Column({ type: 'text' })
  reason: string;

  @CreateDateColumn()
  createdAt: Date;
}

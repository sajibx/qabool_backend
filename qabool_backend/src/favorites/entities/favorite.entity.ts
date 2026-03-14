import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, ManyToOne, Unique } from 'typeorm';
import { User } from '../../users/user.entity';

@Entity('favorites')
@Unique(['user', 'target'])
export class Favorite {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @ManyToOne(() => User)
  user: User;

  @Column()
  userId: string;

  @ManyToOne(() => User)
  target: User;

  @Column()
  targetId: string;

  @CreateDateColumn()
  createdAt: Date;
}

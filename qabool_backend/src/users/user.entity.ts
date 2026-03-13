import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, UpdateDateColumn } from 'typeorm';
import { ApiProperty } from '@nestjs/swagger';

export enum Gender {
  MALE = 'MALE',
  FEMALE = 'FEMALE',
  OTHER = 'OTHER',
}

@Entity('users')
export class User {
  @ApiProperty()
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @ApiProperty()
  @Column({ unique: true })
  email: string;

  @Column()
  passwordHash: string;

  @ApiProperty()
  @Column({ nullable: true })
  firstName: string;

  @ApiProperty()
  @Column({ nullable: true })
  lastName: string;

  @ApiProperty()
  @Column({ nullable: true })
  profileImageUrl: string;

  @ApiProperty()
  @Column({ type: 'text', nullable: true })
  bio: string;

  @ApiProperty()
  @Column({ nullable: true })
  dob: Date;

  @ApiProperty()
  @Column({ nullable: true })
  gender: string;

  @ApiProperty()
  @Column({ nullable: true })
  region: string;

  @ApiProperty()
  @Column({ nullable: true })
  religion: string;

  @ApiProperty()
  @Column({ nullable: true })
  ethnicity: string;

  @ApiProperty()
  @Column({ nullable: true })
  height: number;

  @ApiProperty()
  @Column({ nullable: true })
  weight: number;

  @ApiProperty()
  @Column({ nullable: true })
  profession: string;

  @ApiProperty()
  @Column({ nullable: true })
  education: string;

  @ApiProperty()
  @Column({ type: 'text', nullable: true })
  specialConsiderations: string;

  @ApiProperty()
  @Column({ default: false })
  isVerified: boolean;

  @ApiProperty()
  @Column({ nullable: true })
  lastSeen: Date;

  @ApiProperty()
  @CreateDateColumn()
  createdAt: Date;

  @ApiProperty()
  @UpdateDateColumn()
  updatedAt: Date;
}

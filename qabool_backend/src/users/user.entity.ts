import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, UpdateDateColumn } from 'typeorm';
import { ApiProperty } from '@nestjs/swagger';

export enum Gender {
  MALE = 'MALE',
  FEMALE = 'FEMALE',
  OTHER = 'OTHER',
}

export enum UserStatus {
  ACTIVE = 'ACTIVE',
  INACTIVE = 'INACTIVE',
}

export enum VerifiedStatus {
  ACTIVE = 'active',
  INACTIVE = 'inactive',
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
  @Column({ enum: UserStatus, default: UserStatus.INACTIVE })
  status: UserStatus;

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
  @Column({ type: 'float', nullable: true })
  height: number;

  @ApiProperty()
  @Column({ type: 'float', nullable: true })
  weight: number;

  @ApiProperty()
  @Column({ nullable: true })
  maritalStatus: string;

  @ApiProperty()
  @Column({ nullable: true })
  currentCity: string;

  @ApiProperty()
  @Column({ type: 'float', nullable: true })
  monthlyIncome: number;

  @ApiProperty()
  @Column({ type: 'int', nullable: true })
  siblings: number;

  @ApiProperty()
  @Column({ type: 'int', nullable: true })
  familyMembers: number;

  @ApiProperty()
  @Column({ nullable: true })
  lookingForAge: string;

  @ApiProperty()
  @Column({ nullable: true })
  lookingForType: string;

  @ApiProperty()
  @Column({ nullable: true })
  lookingForProfession: string;

  @ApiProperty()
  @Column({ enum: VerifiedStatus, default: VerifiedStatus.INACTIVE })
  verifiedStatus: VerifiedStatus;

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
  @Column({ default: false })
  hasPastIssues: boolean;

  @ApiProperty()
  @Column({ default: false })
  acceptsPastIssues: boolean;

  @ApiProperty()
  @Column({ nullable: true })
  phoneNumber: string;

  @ApiProperty()
  @Column({ nullable: true })
  lastSeen: Date;

  @ApiProperty()
  isFavorited?: boolean;

  @ApiProperty()
  public isOnline?: boolean;

  @ApiProperty()
  public get age(): number | null {
    if (!this.dob) return null;
    const today = new Date();
    const birthDate = new Date(this.dob);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    return age;
  }

  @ApiProperty()
  public connectionStatus?: 'PENDING_SENT' | 'PENDING_RECEIVED' | 'ACCEPTED' | 'REJECTED' | 'NONE';

  @ApiProperty()
  public connectionId?: string;

  @ApiProperty()
  public isSkipped?: boolean;

  @ApiProperty()
  @CreateDateColumn()
  createdAt: Date;

  @ApiProperty()
  @UpdateDateColumn()
  updatedAt: Date;
}

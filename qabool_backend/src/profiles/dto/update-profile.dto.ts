import { IsString, IsOptional, IsEnum, IsNumber, IsDateString, MaxLength, IsEmail, IsBoolean, IsArray } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';
import { Gender } from '../../users/user.entity';
import { Transform, Type } from 'class-transformer';

export class UpdateProfileDto {
  @ApiProperty({ required: false })
  @IsOptional()
  @IsEmail()
  email?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  firstName?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lastName?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  profileImageUrl?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  bio?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsDateString()
  dob?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  gender?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  region?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  height?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  weight?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  specialConsiderations?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @Transform(({ value }) => value === 'true' || value === true)
  @IsBoolean()
  hasPastIssues?: boolean;

  @ApiProperty({ required: false })
  @IsOptional()
  @Transform(({ value }) => value === 'true' || value === true)
  @IsBoolean()
  acceptsPastIssues?: boolean;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  maritalStatus?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  currentCity?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  monthlyIncome?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  siblings?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  familyMembers?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lookingForAge?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lookingForType?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  phoneNumber?: string;

  @ApiProperty({ example: ['Coding', 'Reading'], required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  interests?: string[];

  @ApiProperty({ example: ['Kind', 'Humorous'], required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  personalityTraits?: string[];

  @ApiProperty({ example: ['Non-smoker', 'Praying five times'], required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  lifeStyle?: string[];

  @ApiProperty({ example: ['Cooking', 'Photography'], required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  hobbies?: string[];

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  hasChildren?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  grewUpIn?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  otherRequirements?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @Transform(({ value }) => value === 'true' || value === true)
  @IsBoolean()
  managedBySomeoneElse?: boolean;

  @ApiProperty({ required: false })
  @IsOptional()
  @Transform(({ value }) => value === 'true' || value === true)
  @IsBoolean()
  facingChallenges?: boolean;

  @ApiProperty({ required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  facingChallengesList?: string[];

  @ApiProperty({ required: false })
  @IsOptional()
  @Transform(({ value }) => value === 'true' || value === true)
  @IsBoolean()
  readyToQaboolChallenges?: boolean;

  @ApiProperty({ required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  readyToQaboolChallengesList?: string[];

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  education?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  religion?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  religionSect?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  religionCast?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  language?: string;
}

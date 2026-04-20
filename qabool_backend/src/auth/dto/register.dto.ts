import { IsEmail, IsString, MinLength, MaxLength, IsOptional, IsArray, IsBoolean, IsNumber } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';
import { Transform, Type } from 'class-transformer';

export class RegisterDto {
  @ApiProperty({ example: 'test@example.com' })
  @IsEmail()
  email: string;

  @ApiProperty({ example: 'password123' })
  @IsString()
  @MinLength(6)
  password: string;

  @ApiProperty({ example: 'John', required: false })
  @IsString()
  @MaxLength(50)
  firstName?: string;

  @ApiProperty({ example: 'Doe', required: false })
  @IsString()
  @MaxLength(50)
  lastName?: string;

  @ApiProperty({ example: 'Male', enum: ['Male', 'Female'], required: false })
  @IsOptional()
  @IsString()
  gender?: string;

  @ApiProperty({ example: '1990-01-01', required: false })
  @IsOptional()
  @IsString()
  dob?: string;

  @ApiProperty({ example: 175, required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  height?: number;

  @ApiProperty({ example: 70, required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  weight?: number;

  @ApiProperty({ example: 'I love coding...', required: false })
  @IsOptional()
  @IsString()
  bio?: string;


  @ApiProperty({ example: 'Berlin, Germany', required: false })
  @IsOptional()
  @IsString()
  region?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @Transform(({ value }) => value === 'true' || value === true)
  @IsBoolean()
  hasPastIssues?: boolean;

  @ApiProperty({ example: false, required: false })
  @IsOptional()
  @Transform(({ value }) => value === 'true' || value === true)
  @IsBoolean()
  acceptsPastIssues?: boolean;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  pastIssuesDetails?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  acceptedPastIssuesDetails?: string;

  @ApiProperty({ example: '+1234567890', required: false })
  @IsOptional()
  @IsString()
  phoneNumber?: string;

  @ApiProperty({ example: 'Single', required: false })
  @IsOptional()
  @IsString()
  maritalStatus?: string;

  @ApiProperty({ example: 'New York', required: false })
  @IsOptional()
  @IsString()
  currentCity?: string;

  @ApiProperty({ example: 5000, required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  monthlyIncome?: number;

  @ApiProperty({ example: 2, required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  siblings?: number;

  @ApiProperty({ example: 4, required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  familyMembers?: number;

  @ApiProperty({ example: '25-30', required: false })
  @IsOptional()
  @IsString()
  lookingForAge?: string;

  @ApiProperty({ example: 'Religious', required: false })
  @IsOptional()
  @IsString()
  lookingForType?: string;

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

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  ethnicity?: string;

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
  @Type(() => Number)
  @IsNumber()
  lookingForMinAge?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  lookingForMaxAge?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  lookingForMinHeight?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  lookingForMinWeight?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  lookingForMaxWeight?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lookingForReligion?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lookingForReligionSect?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lookingForReligionCast?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @Type(() => Number)
  @IsNumber()
  lookingForMonthlyIncome?: number;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lookingForEducation?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  lookingForMaritalStatus?: string;
}

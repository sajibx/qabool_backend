import { IsEmail, IsString, MinLength, MaxLength, IsOptional, IsArray } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

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

  @ApiProperty({ example: 'South Asian', required: false })
  @IsOptional()
  @IsString()
  ethnicity?: string;

  @ApiProperty({ example: 'Islam', required: false })
  @IsOptional()
  @IsString()
  religion?: string;

  @ApiProperty({ example: 175, required: false })
  @IsOptional()
  height?: number;

  @ApiProperty({ example: 70, required: false })
  @IsOptional()
  weight?: number;

  @ApiProperty({ example: 'Software Engineer', required: false })
  @IsOptional()
  @IsString()
  profession?: string;

  @ApiProperty({ example: 'Bachelor of Science', required: false })
  @IsOptional()
  @IsString()
  education?: string;

  @ApiProperty({ example: 'I love coding...', required: false })
  @IsOptional()
  @IsString()
  bio?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  specialConsiderations?: string;

  @ApiProperty({ example: 'Berlin, Germany', required: false })
  @IsOptional()
  @IsString()
  region?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  hasPastIssues?: boolean;

  @ApiProperty({ example: false, required: false })
  @IsOptional()
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
  monthlyIncome?: number;

  @ApiProperty({ example: 2, required: false })
  @IsOptional()
  siblings?: number;

  @ApiProperty({ example: 4, required: false })
  @IsOptional()
  familyMembers?: number;

  @ApiProperty({ example: '25-30', required: false })
  @IsOptional()
  @IsString()
  lookingForAge?: string;

  @ApiProperty({ example: 'Religious', required: false })
  @IsOptional()
  @IsString()
  lookingForType?: string;

  @ApiProperty({ example: 'Doctor', required: false })
  @IsOptional()
  @IsString()
  lookingForProfession?: string;

  @ApiProperty({ example: ['Coding', 'Reading'], required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  interests?: string[];

  @ApiProperty({ example: ['English', 'Bengali'], required: false, type: [String] })
  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  languages?: string[];

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
  marriageIntentions?: string;

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
  sect?: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  caste?: string;
}

import { IsEmail, IsString, MinLength, MaxLength, IsOptional } from 'class-validator';
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

  @ApiProperty({ example: '+1234567890', required: false })
  @IsOptional()
  @IsString()
  phoneNumber?: string;
}

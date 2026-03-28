import { IsString, IsNotEmpty, MaxLength } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class ReportUserDto {
  @ApiProperty({ description: 'The reason for reporting the user' })
  @IsString()
  @IsNotEmpty()
  @MaxLength(1000)
  reason: string;
}

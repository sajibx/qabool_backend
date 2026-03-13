import { IsUUID } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreateConnectionDto {
  @ApiProperty()
  @IsUUID()
  recipientId: string;
}

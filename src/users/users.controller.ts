import { Controller, Patch, Body, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { UsersService } from './users.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { GetUser } from '../common/decorators/get-user.decorator';
import { User } from './user.entity';

@ApiTags('users')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('users')
export class UsersController {
  constructor(private readonly usersService: UsersService) {}

  @Patch('profile')
  @ApiOperation({ summary: 'Update user profile' })
  async updateProfile(@GetUser() user: User, @Body() userData: Partial<User>) {
    // Prevent sensitive fields from being updated directly via this endpoint if necessary
    const updateData = { ...userData };
    delete (updateData as any).password;
    delete (updateData as any).email;
    delete (updateData as any).id;
    
    return this.usersService.update(user.id, updateData as any);
  }
}

import { Controller, Post, Delete, Get, Param, UseGuards, Request } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { BlockingService } from './blocking.service';

@ApiTags('blocks')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('blocks')
export class BlockingController {
  constructor(private readonly blockingService: BlockingService) {}

  @Post(':id')
  @ApiOperation({ summary: 'Block a user' })
  async block(@Request() req: any, @Param('id') id: string) {
    return this.blockingService.blockUser(req.user.id, id);
  }

  @Delete(':id')
  @ApiOperation({ summary: 'Unblock a user' })
  async unblock(@Request() req: any, @Param('id') id: string) {
    await this.blockingService.unblockUser(req.user.id, id);
    return { message: 'User unblocked successfully' };
  }

  @Get()
  @ApiOperation({ summary: 'Get list of blocked users' })
  async getBlockedUsers(@Request() req: any) {
    return this.blockingService.getBlockedUsers(req.user.id);
  }
}

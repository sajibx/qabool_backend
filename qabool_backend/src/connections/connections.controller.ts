import { Controller, Get, Post, Put, Body, Param, UseGuards } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { ConnectionsService } from './connections.service';
import { CreateConnectionDto } from './dto/create-connection.dto';
import { UpdateConnectionDto } from './dto/update-connection.dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { GetUser } from '../common/decorators/get-user.decorator';
import { User } from '../users/user.entity';

@ApiTags('connections')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('connections')
export class ConnectionsController {
  constructor(private readonly connectionsService: ConnectionsService) {}

  @Post()
  @ApiOperation({ summary: 'Send a connection request' })
  async create(@GetUser() user: User, @Body() createConnectionDto: CreateConnectionDto) {
    console.log(`Connection request: ${user.id} -> ${createConnectionDto.recipientId}`);
    return this.connectionsService.createRequest(user.id, createConnectionDto.recipientId);
  }

  @Get()
  @ApiOperation({ summary: 'List pending/active connections' })
  async findAll(@GetUser() user: User) {
    return this.connectionsService.findAll(user.id);
  }

  @Put(':id')
  @ApiOperation({ summary: 'Accept or reject a request' })
  async update(
    @Param('id') id: string,
    @GetUser() user: User,
    @Body() updateConnectionDto: UpdateConnectionDto,
  ) {
    return this.connectionsService.updateStatus(id, user.id, updateConnectionDto.status);
  }
}

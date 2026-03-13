import { Controller, Get, Put, Body, Param, UseGuards, Query } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth, ApiQuery } from '@nestjs/swagger';
import { ProfilesService } from './profiles.service';
import { UpdateProfileDto } from './dto/update-profile.dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { GetUser } from '../common/decorators/get-user.decorator';
import { User } from '../users/user.entity';

@ApiTags('profiles')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('profiles')
export class ProfilesController {
  constructor(private readonly profilesService: ProfilesService) {}

  @Get()
  @ApiOperation({ summary: 'Get discovery list' })
  @ApiQuery({ name: 'religion', required: false })
  @ApiQuery({ name: 'region', required: false })
  async findAll(
    @Query('religion') religion?: string,
    @Query('region') region?: string,
  ) {
    return this.profilesService.findAll({ religion, region });
  }

  @Get('me')
  @ApiOperation({ summary: 'Get current user profile' })
  async findMe(@GetUser() user: User) {
    return this.profilesService.findOne(user.id);
  }

  @Put('me')
  @ApiOperation({ summary: 'Update current user profile' })
  async updateMe(
    @GetUser() user: User,
    @Body() updateProfileDto: UpdateProfileDto,
  ) {
    return this.profilesService.update(user.id, updateProfileDto);
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get profile by ID' })
  async findOne(@Param('id') id: string) {
    return this.profilesService.findOne(id);
  }
}

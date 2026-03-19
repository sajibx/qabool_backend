import { Controller, Get, Put, Body, Param, UseGuards, Query, UseInterceptors, UploadedFile } from '@nestjs/common';
import { FileInterceptor } from '@nestjs/platform-express';
import { diskStorage } from 'multer';
import { extname } from 'path';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth, ApiQuery } from '@nestjs/swagger';
import { ProfilesService } from './profiles.service';
import { UpdateProfileDto } from './dto/update-profile.dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { GetUser } from '../common/decorators/get-user.decorator';
import { User } from '../users/user.entity';
import { ImageService } from '../common/services/image.service';

@ApiTags('profiles')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('profiles')
export class ProfilesController {
  constructor(
    private readonly profilesService: ProfilesService,
    private readonly imageService: ImageService,
  ) {}

  @Get()
  @ApiOperation({ summary: 'Get discovery list' })
  @ApiQuery({ name: 'religion', required: false })
  @ApiQuery({ name: 'region', required: false })
  @ApiQuery({ name: 'gender', required: false })
  async findAll(
    @GetUser() user: User,
    @Query('religion') religion?: string,
    @Query('region') region?: string,
    @Query('gender') gender?: string,
  ) {
    return this.profilesService.findAll({ religion, region, gender }, user);
  }

  @Get('me')
  @ApiOperation({ summary: 'Get current user profile' })
  async findMe(@GetUser() user: User) {
    return this.profilesService.findOne(user.id, user.id);
  }

  @Put('me')
  @UseInterceptors(FileInterceptor('profileImage', {
    storage: diskStorage({
      destination: './uploads',
      filename: (req: any, file: any, cb: any) => {
        const randomName = Array(32).fill(null).map(() => (Math.round(Math.random() * 16)).toString(16)).join('');
        return cb(null, `${randomName}${extname(file.originalname)}`);
      },
    }),
  }))
  @ApiOperation({ summary: 'Update current user profile' })
  async updateMe(
    @GetUser() user: User,
    @Body() updateProfileDto: UpdateProfileDto,
    @UploadedFile() file?: any
  ) {
    let profileImagePath = file?.path;
    if (file) {
      profileImagePath = await this.imageService.convertToJpeg(file.path);
    }
    return this.profilesService.update(user.id, updateProfileDto, profileImagePath);
  }

  @Get('discover')
  @ApiOperation({ summary: 'Discover new users' })
  async discover(@GetUser() user: User) {
    return this.profilesService.discover(user);
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get profile by ID' })
  async findOne(@GetUser() user: User, @Param('id') id: string) {
    return this.profilesService.findOne(id, user.id);
  }
}

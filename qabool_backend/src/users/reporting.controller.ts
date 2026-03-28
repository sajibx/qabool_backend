import { Controller, Post, Get, Param, Body, UseGuards, Request } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth, ApiResponse } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { ReportingService } from './reporting.service';
import { ReportUserDto } from './dto/report-user.dto';

@ApiTags('reports')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('reports')
export class ReportingController {
  constructor(private readonly reportingService: ReportingService) {}

  @Post(':id')
  @ApiOperation({ summary: 'Report a user' })
  @ApiResponse({ status: 201, description: 'The user has been successfully reported.' })
  @ApiResponse({ status: 409, description: 'Duplicate report or self-reporting.' })
  @ApiResponse({ status: 404, description: 'User not found.' })
  async report(@Request() req: any, @Param('id') id: string, @Body() dto: ReportUserDto) {
    return this.reportingService.reportUser(req.user.id, id, dto);
  }

  @Get()
  @ApiOperation({ summary: 'Get list of users you have reported' })
  async getReports(@Request() req: any) {
    return this.reportingService.getReports(req.user.id);
  }
}

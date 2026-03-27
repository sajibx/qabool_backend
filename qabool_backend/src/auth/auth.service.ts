import { Injectable, UnauthorizedException, ConflictException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { UsersService } from '../users/users.service';
import { UserStatus } from '../users/user.entity';
import * as bcrypt from 'bcrypt';
import { RegisterDto } from './dto/register.dto';
import { LoginDto } from './dto/login.dto';

@Injectable()
export class AuthService {
  constructor(
    private usersService: UsersService,
    private jwtService: JwtService,
  ) {}

  async register(registerDto: RegisterDto, profileImagePath?: string) {
    console.log('Registering user:', registerDto.email);
    console.log('Registration data:', registerDto);
    
    const existingUser = await this.usersService.findByEmail(registerDto.email);
    if (existingUser) {
      console.log('User already exists:', registerDto.email);
      throw new ConflictException('Email already exists');
    }

    const hashedPassword = await bcrypt.hash(registerDto.password, 10);
    
    // Normalize path for web access
    const profileImageUrl = profileImagePath ? `/${profileImagePath.replace(/\\/g, '/')}` : null;

    try {
      const user = await this.usersService.create({
        email: registerDto.email,
        passwordHash: hashedPassword,
        firstName: registerDto.firstName,
        lastName: registerDto.lastName,
        gender: registerDto.gender,
        region: registerDto.region,
        religion: registerDto.religion,
        ethnicity: registerDto.ethnicity,
        height: registerDto.height,
        weight: registerDto.weight,
        profession: registerDto.profession,
        education: registerDto.education,
        specialConsiderations: registerDto.specialConsiderations,
        hasPastIssues: registerDto.hasPastIssues,
        acceptsPastIssues: registerDto.acceptsPastIssues,
        phoneNumber: registerDto.phoneNumber,
        maritalStatus: registerDto.maritalStatus,
        currentCity: registerDto.currentCity,
        monthlyIncome: registerDto.monthlyIncome,
        siblings: registerDto.siblings,
        familyMembers: registerDto.familyMembers,
        lookingForAge: registerDto.lookingForAge,
        lookingForType: registerDto.lookingForType,
        lookingForProfession: registerDto.lookingForProfession,
        profileImageUrl: profileImageUrl as string,
        dob: registerDto.dob ? new Date(registerDto.dob) : undefined,
      });

      console.log('User created successfully:', user.id);

      const payload = { sub: user.id, email: user.email };
      return {
        access_token: this.jwtService.sign(payload),
        user: user,
      };
    } catch (error) {
      console.error('Error creating user in database:', error);
      throw error;
    }
  }

  async login(loginDto: LoginDto) {
    console.log(`Login attempt for email: ${loginDto.email}`);
    const user = await this.usersService.findByEmail(loginDto.email);
    if (!user) {
      console.log(`User NOT found for email: ${loginDto.email}`);
      throw new UnauthorizedException('Invalid credentials');
    }

    console.log(`User found: ${user.email}, status: ${user.status}`);
    const isPasswordValid = await bcrypt.compare(loginDto.password, user.passwordHash);
    console.log(`Is password valid: ${isPasswordValid}`);
    
    if (!isPasswordValid) {
      console.log(`Invalid password for user: ${loginDto.email}`);
      throw new UnauthorizedException('Invalid credentials');
    }

    if (user.status !== UserStatus.ACTIVE) {
      console.log(`User account NOT active: ${user.status}`);
      throw new UnauthorizedException('Your account is pending admin approval');
    }

    console.log(`Login successful for user: ${loginDto.email}`);
    const payload = { sub: user.id, email: user.email };
    return {
      access_token: this.jwtService.sign(payload),
      user: user,
    };
  }
}

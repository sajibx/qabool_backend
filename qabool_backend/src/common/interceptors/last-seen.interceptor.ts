import {
  Injectable,
  NestInterceptor,
  ExecutionContext,
  CallHandler,
} from '@nestjs/common';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { UsersService } from '../../users/users.service';

@Injectable()
export class LastSeenInterceptor implements NestInterceptor {
  constructor(private readonly usersService: UsersService) {}

  intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
    const request = context.switchToHttp().getRequest();
    const user = request.user;

    // If user is authenticated (attached to request by JwtStrategy)
    if (user && user.id) {
      // Async update: we don't necessarily want to block the response for this update
      // But for small scale, we can just trigger it.
      this.usersService.updateLastSeen(user.id).catch((err) => {
        console.error(`Failed to update lastSeen for user ${user.id}:`, err);
      });
    }

    return next.handle();
  }
}

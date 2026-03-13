import { Test, TestingModule } from '@nestjs/testing';
import { MessagingGatewayGateway } from './messaging.gateway.gateway';

describe('MessagingGatewayGateway', () => {
  let gateway: MessagingGatewayGateway;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [MessagingGatewayGateway],
    }).compile();

    gateway = module.get<MessagingGatewayGateway>(MessagingGatewayGateway);
  });

  it('should be defined', () => {
    expect(gateway).toBeDefined();
  });
});

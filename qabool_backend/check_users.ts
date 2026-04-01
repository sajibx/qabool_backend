
import { createConnection } from 'typeorm';
import { User } from './src/users/user.entity';
import { Connection } from './src/connections/connection.entity';

async function check() {
  const connection = await createConnection({
    type: 'postgres',
    host: 'localhost',
    port: 5432,
    username: 'postgres',
    password: '0000',
    database: 'postgres',
    entities: [User, Connection],
    synchronize: false,
  });

  const userRepo = connection.getRepository(User);
  const allUsers = await userRepo.find();
  console.log('--- ALL USERS ---');
    allUsers.forEach(u => {
      console.log(`User ID: ${u.id}, Name: ${u.firstName} ${u.lastName}, Gender: ${u.gender}, Status: ${u.status}`);
    });

  await connection.close();
}

check().catch(console.error);

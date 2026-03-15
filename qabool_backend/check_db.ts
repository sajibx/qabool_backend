
import { createConnection } from 'typeorm';
import { User } from './src/users/user.entity';
import { Connection } from './src/connections/connection.entity';

async function check() {
  const connection = await createConnection({
    type: 'sqlite',
    database: 'database.sqlite',
    entities: [User, Connection],
    synchronize: false,
  });

  const connRepo = connection.getRepository(Connection);
  const allConns = await connRepo.find();
  console.log('--- ALL CONNECTIONS ---');
  console.log(JSON.stringify(allConns, null, 2));

  await connection.close();
}

check().catch(console.error);

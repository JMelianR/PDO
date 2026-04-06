const { createClient } = require('@libsql/client');
const path = require('path');

let url = process.env.DATABASE_URL;
let authToken = process.env.DATABASE_AUTH_TOKEN;

if (!url) {
  // En entorno de desarrollo local (netlify dev)
  const dbPath = path.resolve(process.cwd(), 'database.sqlite');
  url = `file:${dbPath}`;
}

const client = createClient({
  url,
  authToken
});

module.exports = client;

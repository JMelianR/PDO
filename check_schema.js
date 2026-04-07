const { createClient } = require('@libsql/client');
const path = require('path');
const url = 'file:' + path.resolve('.', 'database.sqlite');
const db = createClient({ url });

db.execute('SELECT name, sql FROM sqlite_master WHERE type=\'table\' ORDER BY name')
  .then(r => {
    r.rows.forEach(row => {
      console.log('=== TABLE:', row.name, '===');
      console.log(row.sql);
      console.log('');
    });
  })
  .catch(e => console.error(e.message));

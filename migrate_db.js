const { createClient } = require('@libsql/client');
const path = require('path');
const url = 'file:' + path.resolve('.', 'database.sqlite');
const db = createClient({ url });

async function migrate() {
  console.log('Iniciando migración de esquema...');

  // 1. Add subject_id to grades (if not exists)
  try {
    await db.execute('ALTER TABLE grades ADD COLUMN subject_id INTEGER REFERENCES subjects(id)');
    console.log('✅ Columna subject_id agregada a grades');
  } catch (e) {
    if (e.message.includes('duplicate column')) {
      console.log('ℹ️  subject_id ya existe en grades');
    } else {
      console.error('Error agregando subject_id:', e.message);
    }
  }

  // 2. Add professor_id to grades (if not exists)
  try {
    await db.execute('ALTER TABLE grades ADD COLUMN professor_id INTEGER REFERENCES users(id)');
    console.log('✅ Columna professor_id agregada a grades');
  } catch (e) {
    if (e.message.includes('duplicate column')) {
      console.log('ℹ️  professor_id ya existe en grades');
    } else {
      console.error('Error agregando professor_id:', e.message);
    }
  }

  // 3. Verify final schema
  const result = await db.execute("SELECT sql FROM sqlite_master WHERE name='grades'");
  console.log('\nEsquema final de grades:');
  console.log(result.rows[0].sql);
  console.log('\n✅ Migración completada.');
}

migrate().catch(e => {
  console.error('Error fatal:', e.message);
  process.exit(1);
});

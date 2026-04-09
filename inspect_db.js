const db = require('./netlify/functions/db');

async function inspect() {
  const tables = await db.execute("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
  for (const t of tables.rows) {
    console.log(`\n--- TABLE: ${t.name} ---`);
    const schema = await db.execute(`PRAGMA table_info(${t.name})`);
    console.table(schema.rows.map(r => ({ cid: r.cid, name: r.name, type: r.type, pk: r.pk })));
  }
}

inspect().catch(err => {
  console.error(err);
  process.exit(1);
});

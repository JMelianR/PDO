const db = require('./netlify/functions/db');
async function run() {
  // Check course_professor schema
  const r = await db.execute("PRAGMA table_info(course_professor)");
  console.log('course_professor columns:', JSON.stringify(r.rows));
  
  // Check existing data
  const r2 = await db.execute(`
    SELECT cp.professor_id, cp.course_id, 
           u.nombre || ' ' || u.apellido AS professor_name,
           c.nombre AS course_name
    FROM course_professor cp
    JOIN users u ON cp.professor_id = u.id
    JOIN courses c ON cp.course_id = c.id
  `);
  console.log('Existing enrollments:', JSON.stringify(r2.rows));
}
run().catch(console.error);

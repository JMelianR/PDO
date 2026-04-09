const db = require('./netlify/functions/db');

async function check() {
  try {
    const courses = await db.execute('SELECT count(*) as c FROM courses');
    const subjects = await db.execute('SELECT count(*) as s FROM subjects');
    const professors = await db.execute("SELECT count(*) as p FROM users WHERE role = 'profesor'");
    const students = await db.execute("SELECT count(*) as st FROM users WHERE role = 'alumno'");
    
    console.log('Courses:', courses.rows[0].c);
    console.log('Subjects:', subjects.rows[0].s);
    console.log('Professors:', professors.rows[0].p);
    console.log('Students:', students.rows[0].st);

    if (professors.rows[0].p > 0) {
        const pList = await db.execute("SELECT id, nombre, apellido FROM users WHERE role = 'profesor' LIMIT 5");
        console.log('Sample Professors:', JSON.stringify(pList.rows, null, 2));
    }
  } catch (err) {
    console.error('Error:', err.message);
  }
}

check().then(() => process.exit(0));

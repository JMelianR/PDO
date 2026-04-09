const db = require('./netlify/functions/db');

async function seed() {
  try {
    console.log('Seeding basics...');
    // Courses
    await db.execute("INSERT OR IGNORE INTO courses (nombre, anio, division) VALUES ('1ero Básico A', '1', 'A')");
    await db.execute("INSERT OR IGNORE INTO courses (nombre, anio, division) VALUES ('2do Básico B', '2', 'B')");
    
    // Subjects
    await db.execute("INSERT OR IGNORE INTO subjects (nombre) VALUES ('Matemáticas')");
    await db.execute("INSERT OR IGNORE INTO subjects (nombre) VALUES ('Lenguaje')");
    await db.execute("INSERT OR IGNORE INTO subjects (nombre) VALUES ('Historia')");
    
    // Assign Carlos to Matemáticas
    const carlos = await db.execute("SELECT id FROM users WHERE username = 'prof-carlos' LIMIT 1");
    const mat = await db.execute("SELECT id FROM subjects WHERE nombre = 'Matemáticas' LIMIT 1");
    if (carlos.rows.length > 0 && mat.rows.length > 0) {
        await db.execute("INSERT OR IGNORE INTO professor_subjects (professor_id, subject_id) VALUES (?, ?)", [carlos.rows[0].id, mat.rows[0].id]);
    }
    
    // Assign alumno1 to 1ero Básico A
    const alumno1 = await db.execute("SELECT id FROM users WHERE username = 'alumno1' LIMIT 1");
    const curso1 = await db.execute("SELECT id FROM courses WHERE nombre = '1ero Básico A' LIMIT 1");
    if (alumno1.rows.length > 0 && curso1.rows.length > 0) {
        await db.execute("INSERT OR IGNORE INTO students (user_id, course_id) VALUES (?, ?)", [alumno1.rows[0].id, curso1.rows[0].id]);
    }

    console.log('Basics seeded successfully.');
  } catch (err) {
    console.error('Error:', err.message);
  }
}

seed().then(() => process.exit(0));

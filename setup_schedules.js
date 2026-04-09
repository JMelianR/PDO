const db = require('./netlify/functions/db');

async function setup() {
  console.log('Creando tabla schedules...');
  try {
    await db.execute(`
      CREATE TABLE IF NOT EXISTS schedules (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        course_id INTEGER NOT NULL,
        subject_id INTEGER NOT NULL,
        professor_id INTEGER NOT NULL,
        day_of_week TEXT NOT NULL,
        start_time TEXT NOT NULL,
        end_time TEXT NOT NULL,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE CASCADE
      )
    `);
    console.log('Tabla schedules creada exitosamente.');
  } catch (err) {
    console.error('Error al crear la tabla:', err.message);
    process.exit(1);
  }
}

setup().then(() => process.exit(0));

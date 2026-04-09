const db = require('./db');
const { verifyAuth } = require('./utils/auth');

exports.handler = async (event) => {
  const auth = verifyAuth(event, ['alumno']);
  if (auth.error) {
    return { statusCode: auth.statusCode, body: JSON.stringify({ error: auth.error }) };
  }

  const studentId = auth.user.id;
  const method = event.httpMethod;

  if (method === 'GET') {
    try {
      // Cursos en los que está inscripto el alumno
      const coursesResult = await db.execute({
        sql: `SELECT c.id, c.nombre, c.anio, c.division FROM courses c
              JOIN students s ON s.course_id = c.id
              WHERE s.user_id = ?
              ORDER BY c.anio ASC, c.division ASC`,
        args: [studentId]
      });

      // Todas las notas del alumno
      const gradesResult = await db.execute({
        sql: `SELECT g.id, g.course_id, g.subject_id, g.period, g.type, g.value,
                     c.nombre AS course_name,
                     sub.nombre AS subject_name,
                     prof.nombre || ' ' || prof.apellido AS professor_name
              FROM grades g
              JOIN courses c ON g.course_id = c.id
              LEFT JOIN subjects sub ON g.subject_id = sub.id
              LEFT JOIN users prof ON g.professor_id = prof.id
              WHERE g.student_id = ?
              ORDER BY c.nombre ASC, sub.nombre ASC, g.period ASC`,
        args: [studentId]
      });

      // Horario del alumno (basado en sus cursos inscriptos)
      const schedulesResult = await db.execute({
        sql: `SELECT sc.day_of_week, sc.start_time, sc.end_time, s.nombre as subject_name, 
                     prof.nombre || ' ' || prof.apellido as professor_name
              FROM schedules sc
              JOIN subjects s ON sc.subject_id = s.id
              JOIN users prof ON sc.professor_id = prof.id
              JOIN students st ON sc.course_id = st.course_id
              WHERE st.user_id = ?
              ORDER BY sc.start_time ASC`,
        args: [studentId]
      });

      return {
        statusCode: 200,
        body: JSON.stringify({
          courses: coursesResult.rows,
          grades: gradesResult.rows,
          schedules: schedulesResult.rows,
        })
      };
    } catch (error) {
      console.error('Student GET error:', error);
      return { statusCode: 500, body: JSON.stringify({ error: error.message }) };
    }
  }

  return { statusCode: 405, body: JSON.stringify({ error: 'Método no permitido.' }) };
};

const db = require('./db');
const { verifyAuth } = require('./utils/auth');

exports.handler = async (event) => {
  const auth = verifyAuth(event, ['profesor']);
  if (auth.error) {
    return { statusCode: auth.statusCode, body: JSON.stringify({ error: auth.error }) };
  }

  const professorId = auth.user.id;
  const method = event.httpMethod;

  // GET /api/professor — fetch professor's subjects, courses, students, and all grades
  if (method === 'GET') {
    try {
      // Materias asignadas al profesor
      const subjectsResult = await db.execute({
        sql: `SELECT s.id, s.nombre FROM subjects s
              JOIN professor_subjects ps ON s.id = ps.subject_id
              WHERE ps.professor_id = ?
              ORDER BY s.nombre ASC`,
        args: [professorId]
      });

      // Cursos que tienen alumnos inscriptos (para dar notas)
      const coursesResult = await db.execute(
        `SELECT DISTINCT c.id, c.nombre, c.anio, c.division FROM courses c
         JOIN students st ON st.course_id = c.id
         ORDER BY c.anio ASC, c.division ASC`
      );

      // Todos los alumnos por curso
      const studentsResult = await db.execute(`
        SELECT st.user_id AS id, st.course_id, u.nombre, u.apellido, u.username
        FROM students st
        JOIN users u ON st.user_id = u.id
        ORDER BY u.apellido ASC, u.nombre ASC
      `);

      // Todas las notas cargadas por este profesor
      const gradesResult = await db.execute({
        sql: `SELECT g.id, g.student_id, g.course_id, g.subject_id, g.period, g.type, g.value,
                     u.nombre || ' ' || u.apellido AS student_name,
                     c.nombre AS course_name,
                     s.nombre AS subject_name
              FROM grades g
              JOIN users u ON g.student_id = u.id
              JOIN courses c ON g.course_id = c.id
              LEFT JOIN subjects s ON g.subject_id = s.id
              ORDER BY c.nombre ASC, u.apellido ASC, g.period ASC`,
        args: []
      });

      // Horario del profesor
      const schedulesResult = await db.execute({
        sql: `SELECT sc.day_of_week, sc.start_time, sc.end_time, s.nombre as subject_name, 
                     c.nombre || ' (' || c.anio || ' ' || c.division || ')' as course_name
              FROM schedules sc
              JOIN subjects s ON sc.subject_id = s.id
              JOIN courses c ON sc.course_id = c.id
              WHERE sc.professor_id = ?
              ORDER BY sc.start_time ASC`,
        args: [professorId]
      });

      return {
        statusCode: 200,
        body: JSON.stringify({
          subjects: subjectsResult.rows,
          courses: coursesResult.rows,
          students: studentsResult.rows,
          grades: gradesResult.rows,
          schedules: schedulesResult.rows,
        })
      };
    } catch (error) {
      console.error('Professor GET error:', error);
      return { statusCode: 500, body: JSON.stringify({ error: error.message }) };
    }
  }

  // POST /api/professor — save a grade
  if (method === 'POST') {
    try {
      const body = JSON.parse(event.body);
      const { action } = body;

      if (action === 'save_grade') {
        const { student_id, course_id, subject_id, period, type, value } = body;

        if (!student_id || !course_id || !period || !type || value === undefined) {
          return { statusCode: 400, body: JSON.stringify({ error: 'Faltan campos requeridos.' }) };
        }

        const numValue = parseFloat(value);
        if (isNaN(numValue) || numValue < 0 || numValue > 10) {
          return { statusCode: 400, body: JSON.stringify({ error: 'La nota debe ser un número entre 0 y 10.' }) };
        }

        const sid = subject_id || null;

        const existing = await db.execute({
          sql: `SELECT id FROM grades WHERE student_id = ? AND course_id = ? AND period = ? AND type = ?
                AND ((?  IS NULL AND subject_id IS NULL) OR subject_id = ?) LIMIT 1`,
          args: [student_id, course_id, period, type, sid, sid]
        });

        if (existing.rows.length > 0) {
          await db.execute({
            sql: `UPDATE grades SET value = ?, professor_id = ?, subject_id = ? WHERE id = ?`,
            args: [numValue, professorId, sid, existing.rows[0].id]
          });
        } else {
          await db.execute({
            sql: `INSERT INTO grades (student_id, course_id, subject_id, professor_id, period, type, value) VALUES (?, ?, ?, ?, ?, ?, ?)`,
            args: [student_id, course_id, sid, professorId, period, type, numValue]
          });
        }

        return { statusCode: 200, body: JSON.stringify({ message: 'Nota guardada correctamente.' }) };
      }

      if (action === 'delete_grade') {
        const { grade_id } = body;
        if (!grade_id) return { statusCode: 400, body: JSON.stringify({ error: 'ID de nota requerido.' }) };
        await db.execute({ sql: 'DELETE FROM grades WHERE id = ?', args: [grade_id] });
        return { statusCode: 200, body: JSON.stringify({ message: 'Nota eliminada.' }) };
      }

      return { statusCode: 400, body: JSON.stringify({ error: 'Acción no soportada.' }) };
    } catch (error) {
      console.error('Professor POST error:', error);
      return { statusCode: 500, body: JSON.stringify({ error: error.message }) };
    }
  }

  return { statusCode: 405, body: JSON.stringify({ error: 'Método no permitido.' }) };
};

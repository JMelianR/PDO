const db = require('./db');
const { verifyAuth } = require('./utils/auth');

exports.handler = async (event) => {
  const auth = verifyAuth(event, ['admin']);
  if (auth.error) {
    return { statusCode: auth.statusCode, body: JSON.stringify({ error: auth.error }) };
  }

  const method = event.httpMethod;

  if (method === 'GET') {
    try {
      const usersResult = await db.execute('SELECT id, username, role, nombre, apellido FROM users ORDER BY nombre ASC, apellido ASC');
      const coursesResult = await db.execute('SELECT * FROM courses ORDER BY anio ASC, division ASC');
      const subjectsResult = await db.execute('SELECT * FROM subjects ORDER BY nombre ASC');
      const professorsResult = await db.execute("SELECT id, username, nombre, apellido FROM users WHERE role = 'profesor' ORDER BY nombre ASC");
      const studentsResult = await db.execute("SELECT id, username, nombre, apellido FROM users WHERE role = 'alumno' ORDER BY nombre ASC");
      const assignmentsResult = await db.execute(`
        SELECT ps.professor_id, ps.subject_id, u.nombre || ' ' || u.apellido AS professor_name, s.nombre AS subject_name
        FROM professor_subjects ps
        JOIN users u ON ps.professor_id = u.id
        JOIN subjects s ON ps.subject_id = s.id
        ORDER BY u.nombre ASC
      `);
      const enrollmentResult = await db.execute(`
        SELECT s.user_id, s.course_id, u.nombre || ' ' || u.apellido AS student_name, c.nombre AS course_name
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN courses c ON s.course_id = c.id
        ORDER BY u.nombre ASC
      `);
      const gradesResult = await db.execute(`
        SELECT g.id, g.student_id, g.course_id, g.period, g.type, g.value,
               u.nombre || ' ' || u.apellido AS student_name, c.nombre AS course_name
        FROM grades g
        JOIN users u ON g.student_id = u.id
        JOIN courses c ON g.course_id = c.id
        ORDER BY c.nombre ASC, u.nombre ASC
      `);

      return {
        statusCode: 200,
        body: JSON.stringify({
          users: usersResult.rows,
          courses: coursesResult.rows,
          subjects: subjectsResult.rows,
          professors: professorsResult.rows,
          students: studentsResult.rows,
          assignments: assignmentsResult.rows,
          enrollment: enrollmentResult.rows,
          grades: gradesResult.rows,
        })
      };
    } catch (error) {
      return { statusCode: 500, body: JSON.stringify({ error: error.message }) };
    }
  }

  if (method === 'POST') {
    try {
      const body = JSON.parse(event.body);
      const action = body.action;

      if (action === 'create_user') {
        const { username, password, role, nombre, apellido } = body;
        const bcrypt = require('bcryptjs');
        const hash = await bcrypt.hash(password, 10);

        await db.execute({
          sql: 'INSERT INTO users (username, password_hash, role, nombre, apellido) VALUES (?, ?, ?, ?, ?)',
          args: [username, hash, role, nombre, apellido]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Usuario creado exitosamente' }) };
      }

      if (action === 'update_password') {
        const { user_id, new_password } = body;
        if (!user_id || !new_password) {
          return { statusCode: 400, body: JSON.stringify({ error: 'Faltan datos' }) };
        }
        const bcrypt = require('bcryptjs');
        const hash = await bcrypt.hash(new_password, 10);

        await db.execute({
          sql: 'UPDATE users SET password_hash = ? WHERE id = ?',
          args: [hash, user_id]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Contraseña actualizada correctamente' }) };
      }

      if (action === 'create_course') {
        const { nombre, anio, division } = body;
        await db.execute({
          sql: 'INSERT INTO courses (nombre, anio, division) VALUES (?, ?, ?)',
          args: [nombre, anio, division]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Curso creado exitosamente' }) };
      }

      if (action === 'delete_user') {
        const { user_id } = body;
        if (!user_id) {
          return { statusCode: 400, body: JSON.stringify({ error: 'ID de usuario requerido' }) };
        }
        await db.execute({
          sql: "DELETE FROM users WHERE id = ? AND username != 'admin'",
          args: [user_id]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Usuario eliminado exitosamente' }) };
      }

      if (action === 'create_subject') {
        const { nombre } = body;
        if (!nombre || !nombre.trim()) {
          return { statusCode: 400, body: JSON.stringify({ error: 'Nombre de materia requerido' }) };
        }
        await db.execute({
          sql: 'INSERT INTO subjects (nombre) VALUES (?)',
          args: [nombre.trim()]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Materia creada exitosamente' }) };
      }

      if (action === 'delete_subject') {
        const { subject_id } = body;
        if (!subject_id) {
          return { statusCode: 400, body: JSON.stringify({ error: 'ID de materia requerido' }) };
        }
        await db.execute({ sql: 'DELETE FROM professor_subjects WHERE subject_id = ?', args: [subject_id] });
        await db.execute({ sql: 'DELETE FROM subjects WHERE id = ?', args: [subject_id] });
        return { statusCode: 200, body: JSON.stringify({ message: 'Materia eliminada exitosamente' }) };
      }

      if (action === 'assign_subject') {
        const { professor_id, subject_id } = body;
        if (!professor_id || !subject_id) {
          return { statusCode: 400, body: JSON.stringify({ error: 'Faltan datos de asignación' }) };
        }
        await db.execute({
          sql: 'INSERT OR IGNORE INTO professor_subjects (professor_id, subject_id) VALUES (?, ?)',
          args: [professor_id, subject_id]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Materia asignada al profesor' }) };
      }

      if (action === 'unassign_subject') {
        const { professor_id, subject_id } = body;
        if (!professor_id || !subject_id) {
          return { statusCode: 400, body: JSON.stringify({ error: 'Faltan datos para desasignar' }) };
        }
        await db.execute({
          sql: 'DELETE FROM professor_subjects WHERE professor_id = ? AND subject_id = ?',
          args: [professor_id, subject_id]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Materia desasignada del profesor' }) };
      }

      if (action === 'delete_course') {
        const { course_id } = body;
        if (!course_id) {
          return { statusCode: 400, body: JSON.stringify({ error: 'ID de curso requerido' }) };
        }
        await db.execute({ sql: 'DELETE FROM students WHERE course_id = ?', args: [course_id] });
        await db.execute({ sql: 'DELETE FROM grades WHERE course_id = ?', args: [course_id] });
        await db.execute({ sql: 'DELETE FROM courses WHERE id = ?', args: [course_id] });
        return { statusCode: 200, body: JSON.stringify({ message: 'Curso eliminado exitosamente' }) };
      }

      if (action === 'enroll_student') {
        const { student_id, course_id } = body;
        if (!student_id || !course_id) {
          return { statusCode: 400, body: JSON.stringify({ error: 'Faltan datos de inscripción' }) };
        }
        await db.execute({
          sql: 'INSERT OR IGNORE INTO students (user_id, course_id) VALUES (?, ?)',
          args: [student_id, course_id]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Alumno inscrito en el curso' }) };
      }

      if (action === 'unenroll_student') {
        const { student_id, course_id } = body;
        if (!student_id || !course_id) {
          return { statusCode: 400, body: JSON.stringify({ error: 'Faltan datos para desinscribir' }) };
        }
        await db.execute({
          sql: 'DELETE FROM students WHERE user_id = ? AND course_id = ?',
          args: [student_id, course_id]
        });
        return { statusCode: 200, body: JSON.stringify({ message: 'Alumno retirado del curso' }) };
      }

      return { statusCode: 400, body: JSON.stringify({ error: 'Acción no soportada' }) };
    } catch (error) {
      return { statusCode: 500, body: JSON.stringify({ error: error.message }) };
    }
  }

  return { statusCode: 405, body: 'Metodo no permitido o accion no implementada' };
};

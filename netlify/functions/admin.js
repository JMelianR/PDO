const db = require('../db');
const { verifyAuth } = require('./utils/auth');

exports.handler = async (event) => {
  const auth = verifyAuth(event, ['admin']);
  if (auth.error) {
    return { statusCode: auth.statusCode, body: JSON.stringify({ error: auth.error }) };
  }

  const method = event.httpMethod;

  if (method === 'GET') {
    try {
      const usersResult = await db.execute('SELECT id, username, role, nombre, apellido FROM users ORDER BY apellido ASC');
      const coursesResult = await db.execute('SELECT * FROM courses');
      
      return {
        statusCode: 200,
        body: JSON.stringify({
          users: usersResult.rows,
          courses: coursesResult.rows,
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
      
      return { statusCode: 400, body: JSON.stringify({ error: 'Acción no soportada' }) };
    } catch (error) {
      return { statusCode: 500, body: JSON.stringify({ error: error.message }) };
    }
  }

  return { statusCode: 405, body: 'Metodo no permitido o accion no implementada' };
};

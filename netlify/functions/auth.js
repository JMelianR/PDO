const db = require('./db');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

const JWT_SECRET = process.env.JWT_SECRET || 'super-secret-key-temporal';

exports.handler = async (event, context) => {
  if (event.httpMethod !== 'POST') {
    return { statusCode: 405, body: JSON.stringify({ error: 'Method Not Allowed' }) };
  }

  try {
    const { username, password } = JSON.parse(event.body);

    if (!username || !password) {
      return {
        statusCode: 400,
        body: JSON.stringify({ error: 'Faltan credenciales.' })
      };
    }

    const result = await db.execute({
      sql: 'SELECT id, username, password_hash, role, nombre, apellido FROM users WHERE username = ? LIMIT 1',
      args: [username]
    });

    if (result.rows.length === 0) {
      return {
        statusCode: 401,
        body: JSON.stringify({ error: 'Usuario o contraseña incorrectos.' })
      };
    }

    const user = result.rows[0];

    // PHP generaba $2y$, bcryptjs lo procesa correctamente
    const isValid = await bcrypt.compare(password, user.password_hash);
    
    if (!isValid) {
      return {
        statusCode: 401,
        body: JSON.stringify({ error: 'Usuario o contraseña incorrectos.' })
      };
    }

    const payload = {
      id: user.id,
      role: user.role,
      name: `${user.nombre} ${user.apellido}`,
      username: user.username
    };

    const token = jwt.sign(payload, JWT_SECRET, { expiresIn: '1d' });

    return {
      statusCode: 200,
      body: JSON.stringify({
        token,
        user: payload
      })
    };
  } catch (error) {
    console.error("Login Error: ", error);
    return {
      statusCode: 500,
      body: JSON.stringify({ error: 'Error del servidor: ' + error.message })
    };
  }
};

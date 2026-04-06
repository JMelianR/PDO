const db = require('../db');
const { verifyAuth } = require('./utils/auth');

exports.handler = async (event) => {
  const auth = verifyAuth(event, ['alumno']);
  if (auth.error) {
    return { statusCode: auth.statusCode, body: JSON.stringify({ error: auth.error }) };
  }

  // To-Do: implementar endpoints de Alumno
  return { statusCode: 200, body: JSON.stringify({ message: "Endpoint de alumno en construcción" }) };
};

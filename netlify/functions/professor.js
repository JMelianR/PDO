const db = require('../db');
const { verifyAuth } = require('./utils/auth');

exports.handler = async (event) => {
  const auth = verifyAuth(event, ['profesor']);
  if (auth.error) {
    return { statusCode: auth.statusCode, body: JSON.stringify({ error: auth.error }) };
  }

  // To-Do: implementar endpoints de Profesor
  return { statusCode: 200, body: JSON.stringify({ message: "Endpoint de profesor en construcción" }) };
};

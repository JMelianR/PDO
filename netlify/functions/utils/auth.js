const jwt = require('jsonwebtoken');

const JWT_SECRET = process.env.JWT_SECRET || 'super-secret-key-temporal';

exports.verifyAuth = (event, roles = []) => {
    try {
        const authHeader = event.headers.authorization || event.headers.Authorization;
        if (!authHeader || !authHeader.startsWith('Bearer ')) {
            return { error: 'No token provided', statusCode: 401 };
        }
        
        const token = authHeader.split(' ')[1];
        const decoded = jwt.verify(token, JWT_SECRET);
        
        if (roles.length > 0 && !roles.includes(decoded.role)) {
            return { error: 'Unauthorized role', statusCode: 403 };
        }
        
        return { user: decoded };
    } catch (e) {
        return { error: 'Invalid or expired token', statusCode: 401 };
    }
};

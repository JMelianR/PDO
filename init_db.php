<?php
// init_db.php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Hubo un error al conectar a la base de datos.");
}

echo "Inicializando base de datos SQLite...<br>";

// Borrar tablas si existen
$db->exec("DROP TABLE IF EXISTS grades");
$db->exec("DROP TABLE IF EXISTS course_professor");
$db->exec("DROP TABLE IF EXISTS students");
$db->exec("DROP TABLE IF EXISTS courses");
$db->exec("DROP TABLE IF EXISTS users");

// Crear tabla users
$query = "CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('admin', 'profesor', 'alumno')),
    nombre TEXT NOT NULL,
    apellido TEXT NOT NULL
);";
$db->exec($query);

// Crear tabla courses
$query = "CREATE TABLE courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    anio TEXT NOT NULL,
    division TEXT NOT NULL
);";
$db->exec($query);

$query = "CREATE TABLE course_evaluations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id INTEGER,
    period TEXT NOT NULL,
    type TEXT NOT NULL,
    UNIQUE(course_id, period, type),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);";
$db->exec($query);

// Crear tabla course_professor (asociación N:M)
$query = "CREATE TABLE course_professor (
    course_id INTEGER,
    professor_id INTEGER,
    PRIMARY KEY (course_id, professor_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE CASCADE
);";
$db->exec($query);

$query = "CREATE TABLE students (
    user_id INTEGER,
    course_id INTEGER,
    PRIMARY KEY (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);";
$db->exec($query);

// Crear tabla grades
$query = "CREATE TABLE grades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER,
    course_id INTEGER,
    period TEXT NOT NULL,
    type TEXT NOT NULL,
    value REAL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);";
$db->exec($query);

echo "Tablas creadas exitosamente.<br>";

// ==========================================
// SEED DUMMY DATA
// ==========================================
echo "Insertando datos base (Seed)...<br>";

// Insertar Usuarios
// admin / admin123
// prof1 / prof123
// alumno1 / alum123

$adminHash = password_hash('admin123', PASSWORD_BCRYPT);
$profHash = password_hash('prof123', PASSWORD_BCRYPT);
$alumHash = password_hash('alum123', PASSWORD_BCRYPT);

$db->exec("INSERT INTO users (username, password_hash, role, nombre, apellido) VALUES ('admin', '$adminHash', 'admin', 'Administrador', 'Principal')");
$adminId = $db->lastInsertId();

$db->exec("INSERT INTO users (username, password_hash, role, nombre, apellido) VALUES ('prof1', '$profHash', 'profesor', 'Carlos', 'Gómez')");
$profId = $db->lastInsertId();

$db->exec("INSERT INTO users (username, password_hash, role, nombre, apellido) VALUES ('alumno1', '$alumHash', 'alumno', 'Juan', 'Pérez')");
$alumId = $db->lastInsertId();

// Insertar Cursos
$db->exec("INSERT INTO courses (nombre, anio, division) VALUES ('Matemáticas', '1ero', 'A')");
$course1 = $db->lastInsertId();

$db->exec("INSERT INTO courses (nombre, anio, division) VALUES ('Física', '1ero', 'A')");
$course2 = $db->lastInsertId();

// Asignar profesor a los cursos
$db->exec("INSERT INTO course_professor (course_id, professor_id) VALUES ($course1, $profId)");
$db->exec("INSERT INTO course_professor (course_id, professor_id) VALUES ($course2, $profId)");

// Asignar alumno al curso Matematica (como representacion de su inscripcion)
// Nota: RNF-023 "Un alumno pertenece a un solo curso". 
// Si un alumno pertenece a "1ero A", deberíamos tener un concepto de Grado (Año y División) y las Materias están asociadas.
// Simplificaremos asumiendo que los "courses" son las "Materias" de ese grado.
// Para cumplir RNF-023 (un alumno pertenece a un solo curso), rediseñamos semánticamente 'course' como 'Materia/Clase'. En el colegio, el alumno pertenece a "1ero A".
// Asumamos que course_id en students es el "Grupo" principal o la Materia a la que está inscripto.
// Insertamos alumno en estatus general:
$db->exec("INSERT INTO students (user_id, course_id) VALUES ($alumId, $course1)");

// Insertar Notas
// RNF-020: valor de 1 a 7 con un decimal
$db->exec("INSERT INTO grades (student_id, course_id, period, type, value) VALUES ($alumId, $course1, 'Trimestre 1', 'Examen Parcial', 5.5)");
$db->exec("INSERT INTO grades (student_id, course_id, period, type, value) VALUES ($alumId, $course1, 'Trimestre 1', 'Trabajo Práctico', 6.0)");
$db->exec("INSERT INTO grades (student_id, course_id, period, type, value) VALUES ($alumId, $course2, 'Trimestre 1', 'Examen', 4.5)");

echo "Base de datos inicializada correctamente.<br>";
echo "<a href='index.php'>Volver al inicio</a>";
?>

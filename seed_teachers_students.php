<?php
// seed_teachers_students.php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "Iniciando importación progresiva de Profesores y Alumnos...<br>";

$professors = [
    ['Claudia', 'Muñoz', 'Matemáticas'],
    ['Ricardo', 'Torres', 'Lenguaje'],
    ['Patricia', 'Reyes', 'Ciencias'],
    ['Jorge', 'Ramírez', 'Historia y Geografía'],
    ['Verónica', 'Flores', 'Artes'],
    ['Marcelo', 'Castro', 'Educación Física'],
    ['Daniela', 'González', 'Matemáticas'],
    ['Felipe', 'López', 'Lenguaje'],
    ['Camila', 'Fernández', 'Ciencias'],
    ['Andrés', 'Martínez', 'Historia y Geografía'],
    ['Paula', 'Sánchez', 'Artes'],
    ['Cristián', 'Rojas', 'Educación Física'],
    ['Elena', 'Díaz', 'Matemáticas']
];

$coursesCreated = [];

// Precargar cursos si existen
$stmt = $db->query("SELECT id, nombre FROM courses");
$existingCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($existingCourses as $c) {
    $coursesCreated[$c['nombre']] = $c['id'];
}

function getCourseId($db, $nombre, &$coursesCreated)
{
    if (isset($coursesCreated[$nombre]))
        return $coursesCreated[$nombre];
    $db->exec("INSERT INTO courses (nombre, anio, division) VALUES ('$nombre', '1ero', 'A')");
    $id = $db->lastInsertId();
    $coursesCreated[$nombre] = $id;
    return $id;
}

// 1. Insertar Profesores
foreach ($professors as $index => $p) {
    $nombre = $p[0];
    $apellido = $p[1];
    $materia = $p[2];

    $courseId = getCourseId($db, $materia, $coursesCreated);

    $username = strtolower(substr($nombre, 0, 1) . $apellido);
    $password = password_hash($username . '123', PASSWORD_BCRYPT);

    // Check si existe user:
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        $db->exec("INSERT INTO users (username, password_hash, role, nombre, apellido) VALUES ('$username', '$password', 'profesor', '$nombre', '$apellido')");
        $profId = $db->lastInsertId();

        $db->exec("INSERT INTO course_professor (course_id, professor_id) VALUES ($courseId, $profId)");
    }
}
echo "✓ " . count($professors) . " profesores y materias base importados correctamente.<br>";

// 2. Generar Alumnos
$studentNames = ['Juan', 'Mateo', 'Sofia', 'Diego', 'Valentina', 'Maria', 'Pedro', 'Jose', 'Ana', 'Luis'];
$studentLastnames = ['Perez', 'Martinez', 'Garcia', 'Fernandez', 'Lopez', 'Gomez', 'Rodriguez'];

$insertedStudents = 0;
// Generar 10 alumnos random, borrar grades y recrearlos para no superpoblar
for ($i = 1; $i <= 10; $i++) {
    $nombre = $studentNames[array_rand($studentNames)];
    $apellido = $studentLastnames[array_rand($studentLastnames)];

    $uid = uniqid();
    $username = 'estudiante_' . $uid;
    $password = password_hash('alum123', PASSWORD_BCRYPT);

    $db->exec("INSERT INTO users (username, password_hash, role, nombre, apellido) VALUES ('$username', '$password', 'alumno', '$nombre', '$apellido')");
    $studentId = $db->lastInsertId();
    $insertedStudents++;

    $baseCourseId = $coursesCreated['Matemáticas'];
    $db->exec("INSERT INTO students (user_id, course_id) VALUES ($studentId, $baseCourseId)");

    foreach ($coursesCreated as $materia => $cId) {
        $g1 = round(rand(40, 70) / 10, 1);
        $db->exec("INSERT INTO grades (student_id, course_id, period, type, value) VALUES ($studentId, $cId, 'Trimestre 1', 'Parcial', $g1)");
        $g2 = round(rand(30, 70) / 10, 1);
        $db->exec("INSERT INTO grades (student_id, course_id, period, type, value) VALUES ($studentId, $cId, 'Trimestre 1', 'Trabajo Práctico', $g2)");
    }
}

echo "✓ $insertedStudents alumnos creados simulando 1 Básico.<br>";
echo "<a href='index.php'>Volver al Panel</a>";

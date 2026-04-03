<?php
require 'config/database.php';
$db = (new Database())->getConnection();
$courses = $db->query("SELECT id, nombre, anio, division FROM courses")->fetchAll(PDO::FETCH_ASSOC);
foreach($courses as $c) {
    echo "Course: " . $c['nombre'] . " " . $c['anio'] . " " . $c['division'] . "\n";
    $stm = $db->prepare("SELECT u.nombre, u.apellido FROM students s JOIN users u ON s.user_id = u.id WHERE s.course_id = ?");
    $stm->execute([$c['id']]);
    $st = $stm->fetchAll(PDO::FETCH_ASSOC);
    echo "  Students: " . count($st) . "\n";
}
?>

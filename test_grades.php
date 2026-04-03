<?php
require_once 'config/database.php';
require_once 'models/Grade.php';

$db = (new Database())->getConnection();
$gradeModel = new Grade($db);

// We know course_id 1 has Juan Perez
$course_id = 1;
$period = 'Semestre 1';
$type = 'Prueba TEST';
$student_ids = [3]; // Juan Perez

echo "Calling createActivityForStudents...\n";
try {
    $res = $gradeModel->createActivityForStudents($course_id, $period, $type, $student_ids);
    if($res) {
        echo "Success!\n";
    } else {
        echo "Failed: " . print_r($db->errorInfo(), true) . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

$grades = $db->query("SELECT * FROM grades WHERE course_id=1")->fetchAll(PDO::FETCH_ASSOC);
print_r($grades);
?>

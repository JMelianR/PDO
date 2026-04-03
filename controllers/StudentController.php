<?php
require_once 'models/Grade.php';

require_once 'models/Course.php';

class StudentController {
    public function dashboard() {
        $database = new Database();
        $db = $database->getConnection();
        $gradeModel = new Grade($db);
        $courseModel = new Course($db);

        $student_id = $_SESSION['user_id'];

        // Get assigned courses
        $coursesStmt = $courseModel->getCoursesByStudent($student_id);
        $assignedCourses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch grades for logged in student
        $stmt = $gradeModel->getGradesByStudent($student_id);
        $all_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $courseGrades = [];
        $totalItems = 0;
        $totalSum = 0;
        
        // Group grades by course_id
        foreach($all_grades as $g) {
            $courseGrades[$g['materia']][] = $g;
            if ($g['value'] !== null) {
                $totalSum += $g['value'];
                $totalItems++;
            }
        }
        
        $avg = $totalItems > 0 ? ($totalSum / $totalItems) : 0;

        require_once 'views/student/dashboard.php';
    }
}
?>

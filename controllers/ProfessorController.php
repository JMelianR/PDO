<?php
require_once 'models/Course.php';
require_once 'models/Grade.php';

class ProfessorController {
    public function dashboard() {
        $database = new Database();
        $db = $database->getConnection();
        
        $courseModel = new Course($db);
        $gradeModel = new Grade($db);
        
        $prof_id = $_SESSION['user_id'];
        
        // Manejar subida de notas
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'update_grade') {
                $grade_id = $_POST['grade_id'];
                $value = trim($_POST['value']);
                if ($value === '') {
                    $gradeModel->updateGrade($grade_id, null);
                } else {
                    $valF = (float)$value;
                    if($valF >= 1.0 && $valF <= 7.0) {
                        $gradeModel->updateGrade($grade_id, $valF);
                    }
                }
                header("Location: index.php?action=dashboard");
                exit;
            }
            elseif ($_POST['action'] === 'create_evaluation') {
                $c_id = $_POST['course_id'];
                $period = $_POST['period'];
                $type = $_POST['type'];
                
                $gradeModel->createActivityForCourse($c_id, $period, $type);
                
                header("Location: index.php?action=dashboard");
                exit;
            }
            elseif ($_POST['action'] === 'delete_evaluation') {
                $gradeModel->deleteActivityForCourse($_POST['course_id'], $_POST['period'], $_POST['type']);
                header("Location: index.php?action=dashboard");
                exit;
            }
        }

        // Obtener cursos asignados
        $coursesStmt = $courseModel->getCoursesByProfessor($prof_id);
        $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Estructura de datos para la vista: 
        // [ course_id => [ 'course' => {...}, 'students' => [ student => [grades...] ] ] ]
        
        $courseData = [];
        foreach($courses as $c) {
            $gradeModel->syncCourseEvaluations($c['id']);
            
            // Get all official course evaluations
            $evalsStmt = $db->prepare("SELECT period, type FROM course_evaluations WHERE course_id = ?");
            $evalsStmt->execute([$c['id']]);
            $evaluations = $evalsStmt->fetchAll(PDO::FETCH_ASSOC);

            $courseData[$c['id']] = ['info' => $c, 'students' => [], 'evaluations' => $evaluations];
            
            $studentsStmt = $courseModel->getStudentsByCourse($c['id']);
            $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($students as $s) {
                $gradesStmt = $gradeModel->getStudentGradesInCourse($s['id'], $c['id']);
                $grades = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);
                $courseData[$c['id']]['students'][] = [
                    'info' => $s,
                    'grades' => $grades
                ];
            }
        }

        require_once 'views/professor/dashboard.php';
    }
}
?>

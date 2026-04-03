<?php
require_once 'models/User.php';
require_once 'models/Course.php';
require_once 'models/Grade.php';

class AdminController {
    public function dashboard() {
        $database = new Database();
        $db = $database->getConnection();
        
        $userModel = new User($db);
        $courseModel = new Course($db);
        
        $tab = $_GET['tab'] ?? 'users';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionPost = $_POST['admin_action'] ?? '';
            
            if ($actionPost === 'create_user') {
                $userModel->createUser($_POST['username'], $_POST['password'], $_POST['role'], $_POST['nombre'], $_POST['apellido']);
                header("Location: index.php?action=dashboard&tab=users&msg=Usuario+Creado");
            }
            elseif ($actionPost === 'create_course') {
                $q = "INSERT INTO courses (nombre, anio, division) VALUES (:n, :a, :d)";
                $stmt = $db->prepare($q);
                $stmt->execute([':n'=>$_POST['nombre'], ':a'=>$_POST['anio'], ':d'=>$_POST['division']]);
                header("Location: index.php?action=dashboard&tab=courses&msg=Curso+Creado");
            }
            elseif ($actionPost === 'delete_user') {
                $id = (int) $_POST['user_id'];
                try {
                    $db->prepare("DELETE FROM users WHERE id = :id")->execute([':id'=>$id]);
                    header("Location: index.php?action=dashboard&tab=users&msg=Usuario+eliminado");
                } catch (Exception $e) {
                    header("Location: index.php?action=dashboard&tab=users&msg=Error+al+eliminar+usuario");
                }
                exit;
            }
            elseif ($actionPost === 'assign_prof') {
                $c = $_POST['course_id'];
                $p = $_POST['prof_id'] ?? '';
                $a = $_POST['anio'] ?? '';
                try {
                    if ($a !== '') {
                        $db->prepare("UPDATE courses SET anio = :a WHERE id = :c")->execute([':a'=>$a, ':c'=>$c]);
                    }
                    if ($p !== '') {
                        $db->prepare("INSERT INTO course_professor (course_id, professor_id) VALUES (:c, :p)")->execute([':c'=>$c, ':p'=>$p]);
                    }
                } catch(Exception $e){}
                header("Location: index.php?action=dashboard&tab=courses&msg=Asignación+guardada");
                exit;
            }
            elseif ($actionPost === 'delete_course') {
                $id = (int) $_POST['course_id'];
                try {
                    $db->prepare("DELETE FROM courses WHERE id = :id")->execute([':id'=>$id]);
                    header("Location: index.php?action=dashboard&tab=courses&msg=Materia+eliminada");
                } catch (Exception $e) {
                    header("Location: index.php?action=dashboard&tab=courses&msg=Error+eliminando");
                }
                exit;
            }
            elseif ($actionPost === 'delete_assignment') {
                $c = (int) $_POST['course_id'];
                $p = (int) $_POST['prof_id'];
                try {
                    $stmt = $db->prepare("DELETE FROM course_professor WHERE course_id = :c AND professor_id = :p");
                    $stmt->bindValue(':c', $c, PDO::PARAM_INT);
                    $stmt->bindValue(':p', $p, PDO::PARAM_INT);
                    $stmt->execute();
                    header("Location: index.php?action=dashboard&tab=assignments&msg=Asignación+eliminada");
                } catch(Exception $e) {
                    header("Location: index.php?action=dashboard&tab=assignments&msg=Error:" . urlencode($e->getMessage()));
                }
                exit;
            }
            elseif ($actionPost === 'assign_student') {
                $c = (int) $_POST['course_id'];
                $s = (int) $_POST['student_id'];
                try {
                    $db->prepare("INSERT INTO students (user_id, course_id) VALUES (:s, :c)")->execute([':s'=>$s, ':c'=>$c]);
                    $gradeModel = new Grade($db);
                    $gradeModel->syncCourseEvaluations($c);
                    header("Location: index.php?action=dashboard&tab=enrollments&msg=Alumno+inscrito");
                } catch(Exception $e) {
                    header("Location: index.php?action=dashboard&tab=enrollments&msg=Error+o+alumno+ya+inscrito");
                }
                exit;
            }
            elseif ($actionPost === 'delete_enrollment') {
                $c = (int) $_POST['course_id'];
                $s = (int) $_POST['student_id'];
                try {
                    $db->prepare("DELETE FROM students WHERE course_id = :c AND user_id = :s")->execute([':c'=>$c, ':s'=>$s]);
                    header("Location: index.php?action=dashboard&tab=enrollments&msg=Inscripción+eliminada");
                } catch(Exception $e) {
                    header("Location: index.php?action=dashboard&tab=enrollments&msg=Error+al+eliminar+inscripción");
                }
                exit;
            }
            exit;
        }

        // Obtener datos para la vista
        $usersStmt = $userModel->getAllUsers();
        $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $coursesStmt = $courseModel->getAllCourses();
        $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        $assignments = [];
        if ($tab === 'assignments') {
            $assignmentsStmt = $courseModel->getAllAssignments();
            $assignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $enrollmentData = [];
        if ($tab === 'enrollments') {
            foreach($courses as $c) {
                $st = $courseModel->getStudentsByCourse($c['id'])->fetchAll(PDO::FETCH_ASSOC);
                $enrollmentData[] = ['course' => $c, 'students' => $st];
            }
        }

        require_once 'views/admin/dashboard.php';
    }
}
?>

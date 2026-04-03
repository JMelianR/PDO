<?php
class Grade {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getGradesByStudent($student_id) {
        $query = "SELECT g.id, c.nombre as materia, g.period, g.type, g.value 
                  FROM grades g
                  JOIN courses c ON g.course_id = c.id
                  WHERE g.student_id = :student_id
                  ORDER BY g.period, c.nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        return $stmt;
    }

    public function updateGrade($grade_id, $value) {
        // value can be null now
        $query = "UPDATE grades SET value = :value WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':id', $grade_id);
        return $stmt->execute();
    }
    
    public function syncCourseEvaluations($course_id) {
        // Fetch all enrolled students
        $studentsStmt = $this->conn->prepare("SELECT user_id FROM students WHERE course_id = ?");
        $studentsStmt->execute([$course_id]);
        $student_ids = $studentsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Fetch all course evaluations
        $evalsStmt = $this->conn->prepare("SELECT period, type FROM course_evaluations WHERE course_id = ?");
        $evalsStmt->execute([$course_id]);
        $evals = $evalsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($student_ids) || empty($evals)) return;

        // Insert missing grades using INSERT IGNORE analogue for SQLite
        $this->conn->beginTransaction();
        try {
            $checkStmt = $this->conn->prepare("SELECT 1 FROM grades WHERE student_id = ? AND course_id = ? AND period = ? AND type = ?");
            $insertStmt = $this->conn->prepare("INSERT INTO grades (student_id, course_id, period, type, value) VALUES (?, ?, ?, ?, NULL)");
            
            foreach($student_ids as $sid) {
                foreach($evals as $ev) {
                    $checkStmt->execute([$sid, $course_id, $ev['period'], $ev['type']]);
                    if(!$checkStmt->fetch()) {
                        $insertStmt->execute([$sid, $course_id, $ev['period'], $ev['type']]);
                    }
                }
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Grade sync exception: " . $e->getMessage());
        }
    }

    public function createActivityForCourse($course_id, $period, $type) {
        $this->conn->beginTransaction();
        try {
            $query = "INSERT OR IGNORE INTO course_evaluations (course_id, period, type) VALUES (:cid, :period, :type)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':cid' => $course_id,
                ':period' => $period,
                ':type' => $type
            ]);
            $this->conn->commit();
            
            // Sync casilleros for current students
            $this->syncCourseEvaluations($course_id);
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Grade model exception: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteActivityForCourse($course_id, $period, $type) {
        $this->conn->beginTransaction();
        try {
            $delGrades = "DELETE FROM grades WHERE course_id = :cid AND period = :period AND type = :type";
            $stmt1 = $this->conn->prepare($delGrades);
            $stmt1->execute([':cid'=>$course_id, ':period'=>$period, ':type'=>$type]);
            
            $delEval = "DELETE FROM course_evaluations WHERE course_id = :cid AND period = :period AND type = :type";
            $stmt2 = $this->conn->prepare($delEval);
            $stmt2->execute([':cid'=>$course_id, ':period'=>$period, ':type'=>$type]);
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function getStudentGradesInCourse($student_id, $course_id) {
        $query = "SELECT * FROM grades WHERE student_id = :sid AND course_id = :cid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sid', $student_id);
        $stmt->bindParam(':cid', $course_id);
        $stmt->execute();
        return $stmt;
    }
}
?>

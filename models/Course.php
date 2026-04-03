<?php
class Course {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCoursesByProfessor($prof_id) {
        $query = "SELECT c.id, c.nombre, c.anio, c.division 
                  FROM courses c
                  JOIN course_professor cp ON c.id = cp.course_id
                  WHERE cp.professor_id = :prof_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':prof_id', $prof_id);
        $stmt->execute();
        return $stmt;
    }

    public function getAllCourses() {
        $query = "SELECT * FROM courses";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAllAssignments() {
        $query = "SELECT c.id as course_id, c.nombre as curso_nombre, c.anio, c.division, u.id as prof_id, u.nombre as prof_nombre, u.apellido as prof_apellido
                  FROM courses c
                  JOIN course_professor cp ON c.id = cp.course_id
                  JOIN users u ON cp.professor_id = u.id
                  ORDER BY c.nombre ASC, u.apellido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getStudentsByCourse($course_id) {
        $query = "SELECT u.id, u.nombre, u.apellido 
                  FROM users u
                  JOIN students s ON u.id = s.user_id
                  WHERE s.course_id = :course_id
                  ORDER BY u.apellido ASC, u.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        return $stmt;
    }

    public function getCoursesByStudent($student_id) {
        $query = "SELECT c.* 
                  FROM courses c
                  JOIN students s ON c.id = s.course_id
                  WHERE s.user_id = :student_id
                  ORDER BY c.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        return $stmt;
    }
}
?>

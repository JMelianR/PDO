<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, role, nombre, apellido FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            if(password_verify($password, $row['password_hash'])) {
                return $row;
            }
        }
        return false;
    }

    public function getAllUsers() {
        $query = "SELECT id, username, role, nombre, apellido FROM " . $this->table_name . " ORDER BY apellido ASC, nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function createUser($username, $password, $role, $nombre, $apellido) {
        $query = "INSERT INTO " . $this->table_name . " (username, password_hash, role, nombre, apellido) VALUES (:u, :p, :r, :n, :a)";
        $stmt = $this->conn->prepare($query);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(':u', $username);
        $stmt->bindParam(':p', $hash);
        $stmt->bindParam(':r', $role);
        $stmt->bindParam(':n', $nombre);
        $stmt->bindParam(':a', $apellido);
        return $stmt->execute();
    }
}
?>

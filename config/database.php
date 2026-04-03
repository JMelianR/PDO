<?php
// config/database.php

class Database {
    private $dbPath = __DIR__ . '/../database.sqlite';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Configurar conexión PDO con SQLite
            $this->conn = new PDO('sqlite:' . $this->dbPath);
            // Configurar para que arroje excepciones en caso de error
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Habilitar claves foráneas
            $this->conn->exec('PRAGMA foreign_keys = ON;');
        } catch(PDOException $exception) {
            echo 'Error de conexión: ' . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>

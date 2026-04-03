<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

try {
    $db->exec('PRAGMA foreign_keys=off;');
    $db->beginTransaction();
    
    $query = "CREATE TABLE grades_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER,
        course_id INTEGER,
        period TEXT NOT NULL,
        type TEXT NOT NULL,
        value REAL,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    );";
    $db->exec($query);
    
    $db->exec("INSERT INTO grades_new SELECT * FROM grades;");
    $db->exec("DROP TABLE grades;");
    $db->exec("ALTER TABLE grades_new RENAME TO grades;");
    
    $db->commit();
    $db->exec('PRAGMA foreign_keys=on;');
    echo "Schema updated successfully!";
} catch(Exception $e) {
    if($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error updating schema: " . $e->getMessage();
}
?>

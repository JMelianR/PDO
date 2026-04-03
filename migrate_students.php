<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

try {
    $db->exec('PRAGMA foreign_keys=off;');
    $db->beginTransaction();
    
    // Create new table
    $query = "CREATE TABLE students_new (
        user_id INTEGER,
        course_id INTEGER,
        PRIMARY KEY (user_id, course_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    );";
    $db->exec($query);
    
    // Copy data
    $db->exec("INSERT INTO students_new SELECT user_id, course_id FROM students;");
    
    // Drop old and rename new
    $db->exec("DROP TABLE students;");
    $db->exec("ALTER TABLE students_new RENAME TO students;");
    
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

<?php
require_once 'config/database.php';

try {
    $db = (new Database())->getConnection();
    
    // Get assignments
    $stmt = $db->query("SELECT * FROM course_professor");
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Current assignments:\n";
    print_r($assignments);

    if (empty($assignments)) {
        echo "No assignments to delete.\n";
        exit;
    }

    $c = $assignments[0]['course_id'];
    $p = $assignments[0]['professor_id'];

    echo "\nTrying to delete course_id=$c, professor_id=$p\n";
    
    $stmt = $db->prepare("DELETE FROM course_professor WHERE course_id = :c AND professor_id = :p");
    $stmt->bindValue(':c', $c, PDO::PARAM_INT);
    $stmt->bindValue(':p', $p, PDO::PARAM_INT);
    $res = $stmt->execute();
    
    echo "Result: " . ($res ? 'Success' : 'Failed') . "\n";
    if (!$res) {
        print_r($stmt->errorInfo());
    }
    
    // Check again
    $stmt = $db->query("SELECT * FROM course_professor");
    $remaining = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nRemaining assignments:\n";
    print_r($remaining);
    
} catch(Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>

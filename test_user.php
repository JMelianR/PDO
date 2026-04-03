<?php
require_once 'config/database.php';

try {
    $db = (new Database())->getConnection();
    
    // Get users
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Current users:\n";
    print_r($users);

    // find testuser1
    $target_id = null;
    foreach($users as $u) {
        if ($u['username'] === 'testuser1') {
            $target_id = $u['id'];
            break;
        }
    }

    if (!$target_id) {
        echo "testuser1 not found\n";
        exit;
    }

    echo "\nTrying to delete testuser1 (id=$target_id)\n";
    
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindValue(':id', $target_id, PDO::PARAM_INT);
    $res = $stmt->execute();
    
    echo "Result: " . ($res ? 'Success' : 'Failed') . "\n";
    if (!$res) {
        print_r($stmt->errorInfo());
    }
    
} catch(Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>

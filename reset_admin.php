<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$adminHash = password_hash('admin123', PASSWORD_BCRYPT);
$db->exec("UPDATE users SET password_hash = '$adminHash' WHERE username = 'admin'");
echo "Admin password resetado a admin123";
?>

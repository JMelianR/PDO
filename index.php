<?php
session_start();
// index.php - Router Principal

require_once 'config/database.php';
require_once 'controllers/AuthController.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Módulo de Autenticación
$authController = new AuthController();

if ($action === 'login') {
    $authController->login();
    exit;
}
if ($action === 'logout') {
    $authController->logout();
    exit;
}

// Proteger el resto de las rutas
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}

$role = $_SESSION['user_role'];

// Router simple según rol y acción
if ($role === 'alumno') {
    require_once 'controllers/StudentController.php';
    $controller = new StudentController();
    $controller->dashboard();
} elseif ($role === 'profesor') {
    require_once 'controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->dashboard();
} elseif ($role === 'admin') {
    require_once 'controllers/AdminController.php';
    $controller = new AdminController();
    $controller->dashboard();
} else {
    echo "Rol no válido.";
    session_destroy();
}
?>

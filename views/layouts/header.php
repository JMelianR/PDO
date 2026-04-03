<?php
// views/layouts/header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Notas - PDO</title>
    <!-- Google Fonts para tipografías modernas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php if(isset($_SESSION['user_id'])): ?>
    <nav class="navbar slide-in">
        <div class="navbar-brand">🏫 Colegio Generico</div>
        <div class="navbar-nav">
            <span class="user-badge">
                <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= ucfirst($_SESSION['user_role']) ?>)
            </span>
            <a href="index.php?action=logout" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.85rem;">Cerrar Sesión</a>
        </div>
    </nav>
    <main class="container fade-in">
    <?php else: ?>
    <!-- Modal Login Layout -->
    <?php endif; ?>

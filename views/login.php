<?php require_once 'views/layouts/header.php'; ?>

<div class="auth-wrapper fade-in">
    <div class="card" style="max-width: 400px; width: 100%;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2>Portal de Acceso</h2>
            <p>Sistema de Notas PDO</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=login" method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Ingrese su usuario" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Ingresar</button>
        </form>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>

<?php require_once 'views/layouts/header.php'; ?>

<div class="fade-in" style="margin-top: 2rem;">
    <h2>Panel de Administrador</h2>
    
    <div style="margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center; width: 100%;">
        <a href="index.php?action=dashboard&tab=users" class="btn <?= $tab==='users'?'btn-primary':'' ?>" style="background: <?= $tab==='users'?'':'rgba(255,255,255,0.1)' ?>">Gestionar Usuarios</a>
        <a href="index.php?action=dashboard&tab=courses" class="btn <?= $tab==='courses'?'btn-primary':'' ?>" style="background: <?= $tab==='courses'?'':'rgba(255,255,255,0.1)' ?>">Gestionar Cursos</a>
        <a href="index.php?action=dashboard&tab=assignments" class="btn <?= $tab==='assignments'?'btn-primary':'' ?>" style="background: <?= $tab==='assignments'?'':'rgba(255,255,255,0.1)' ?>">Asignaciones</a>
        <a href="index.php?action=dashboard&tab=enrollments" class="btn <?= $tab==='enrollments'?'btn-primary':'' ?>" style="background: <?= $tab==='enrollments'?'':'rgba(255,255,255,0.1)' ?>">Inscribir Alumnos</a>
        
        <?php if($tab === 'users'): ?>
        <button id="fabBtn" onclick="openUserModal()" title="Agregar nuevo usuario" style="margin-left: auto; height: 44px; border-radius: 22px; padding: 0 20px; background: var(--accent-primary); color: white; font-size: 15px; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(59,130,246,0.4); display: flex; align-items: center; gap: 8px; justify-content: center; transition: transform 0.2s, background 0.2s; z-index: 10;">
            <span style="font-size: 22px; margin-top: -2px;">+</span>
            <span>Agregar Nuevo Usuario</span>
        </button>
        <?php endif; ?>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if($tab === 'users'): ?>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 10px;">
            <h3 style="margin: 0;">Usuarios del Sistema</h3>
            <input type="text" id="userFilter" onkeyup="filterUsers()" class="form-control" placeholder="Buscar usuario..." style="max-width: 300px;">
        </div>
        <div class="table-wrapper">
            <table id="userTable">
                <thead>
                    <tr><th>Usuario</th><th>Rol</th><th>Nombre</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td><?= htmlspecialchars($u['apellido'].', '.$u['nombre']) ?></td>
                        <td>
                            <?php if($u['id'] !== $_SESSION['user_id']): ?>
                            <button type="button" class="btn" style="background:rgba(239, 68, 68, 0.2); color:var(--danger); padding:4px 8px;" onclick="confirmUserDelete(<?= $u['id'] ?>)">Borrar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        


    </div> <!-- closes .card -->

    <!-- Modal Crear Usuario -->
    <div id="userModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.9); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width:90%; max-width:440px; position:relative; background: var(--bg-secondary); border: 1px solid var(--accent-primary); box-shadow: 0 20px 50px rgba(0,0,0,0.5); backdrop-filter: none; -webkit-backdrop-filter: none;">
            <button onclick="closeUserModal()" style="position:absolute; top:20px; right:20px; background:transparent; border:none; font-size:28px; color:var(--text-secondary); cursor:pointer; line-height:1;">&times;</button>
            <h3 style="margin-top:0; margin-bottom:1.5rem; color: var(--accent-primary);">Nuevo Usuario</h3>
            <form method="POST" action="index.php?action=dashboard&tab=users">
                <input type="hidden" name="admin_action" value="create_user">
                <div class="form-group">
                    <input class="form-control" type="text" name="username" placeholder="Usuario" required>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" name="password" placeholder="Contraseña" required>
                </div>
                <div class="form-group">
                    <select class="form-control" name="role" required>
                        <option value="alumno">Alumno</option>
                        <option value="profesor">Profesor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <input class="form-control" type="text" name="nombre" placeholder="Nombre" required>
                </div>
                <div class="form-group">
                    <input class="form-control" type="text" name="apellido" placeholder="Apellido" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 1rem;">Guardar Usuario</button>
            </form>
        </div>
    </div>

    <!-- Modal Confirmación Borrar Usuario -->
    <div id="deleteConfirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.9); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width: 100%; max-width: 400px; padding: 2rem;">
            <h3 style="margin-top:0; color:var(--danger);">Confirmar Eliminación</h3>
            <p style="font-size: 1.1rem; margin-top: 1rem;">¿Estás seguro que quieres borrar este usuario?</p>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 2rem;">Esta acción no se puede deshacer.</p>
            <div style="display:flex; justify-content:flex-end; gap:1rem;">
                <button type="button" class="btn" onclick="document.getElementById('deleteConfirmModal').style.display='none'">Cancelar</button>
                <form method="POST" action="index.php?action=dashboard&tab=users" style="margin:0;">
                    <input type="hidden" name="admin_action" value="delete_user">
                    <input type="hidden" id="deleteUserIdInput" name="user_id" value="">
                    <button type="submit" class="btn btn-primary" style="background:var(--danger);">Borrar Permanentemente</button>
                </form>
            </div>
        </div>
    </div>

        <script>
            function openUserModal() {
                document.getElementById('userModal').style.display = 'flex';
            }
            function closeUserModal() {
                document.getElementById('userModal').style.display = 'none';
            }
            function confirmUserDelete(id) {
                document.getElementById('deleteUserIdInput').value = id;
                document.getElementById('deleteConfirmModal').style.display = 'flex';
                // Move modal to body to prevent stacking context issues
                document.body.appendChild(document.getElementById('deleteConfirmModal'));
            }

            // Move modal to body on load to avoid backdrop issues
            document.addEventListener("DOMContentLoaded", () => {
                document.body.appendChild(document.getElementById('userModal'));
            });

            // Cerrar modal al hacer clic fuera
            document.getElementById('userModal').addEventListener('click', function(e) {
                if (e.target === this) closeUserModal();
            });
            function filterUsers() {
                let input = document.getElementById("userFilter");
                let filter = input.value.toLowerCase();
                let table = document.getElementById("userTable");
                let tr = table.getElementsByTagName("tr");
                for (let i = 1; i < tr.length; i++) {
                    let tdUsuario = tr[i].getElementsByTagName("td")[0];
                    let tdRol = tr[i].getElementsByTagName("td")[1];
                    let tdNombre = tr[i].getElementsByTagName("td")[2];
                    if (tdUsuario && tdRol && tdNombre) {
                        let txtValue = tdUsuario.textContent + " " + tdRol.textContent + " " + tdNombre.textContent;
                        tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? "" : "none";
                    }
                }
            }
        </script>
    
    <?php elseif($tab === 'courses'): ?>
    <div class="card">
        <h3>Cursos y Materias</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Materia</th><th>Año</th><th>División</th><th>Asignar Profesor</th></tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre']) ?></td>
                        <td>
                            <select name="anio" form="assign_form_<?= $c['id'] ?>" class="form-control" style="width: auto; padding: 4px;">
                                <?php if($c['anio']): ?>
                                <option value="<?= htmlspecialchars($c['anio']) ?>" selected><?= htmlspecialchars($c['anio']) ?></option>
                                <?php else: ?>
                                <option value="" selected>Asignar Curso...</option>
                                <?php endif; ?>
                                <optgroup label="Educación Básica">
                                    <option value="1ero básico">1ero básico</option>
                                    <option value="2do básico">2do básico</option>
                                    <option value="3ero básico">3ero básico</option>
                                    <option value="4to básico">4to básico</option>
                                    <option value="5to básico">5to básico</option>
                                    <option value="6to básico">6to básico</option>
                                    <option value="7mo básico">7mo básico</option>
                                    <option value="8vo básico">8vo básico</option>
                                </optgroup>
                                <optgroup label="Educación Media">
                                    <option value="1ero medio">1ero medio</option>
                                    <option value="2do medio">2do medio</option>
                                    <option value="3ero medio">3ero medio</option>
                                    <option value="4to medio">4to medio</option>
                                </optgroup>
                            </select>
                        </td>
                        <td><?= htmlspecialchars($c['division']) ?></td>
                        <td>
                            <div style="display:flex; gap:0.5rem; align-items:center;">
                                <form id="assign_form_<?= $c['id'] ?>" method="POST" style="display:flex; gap:0.5rem; margin:0;">
                                    <input type="hidden" name="admin_action" value="assign_prof">
                                    <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                    <select name="prof_id" class="form-control" style="width: auto; padding: 4px;">
                                        <option value="">Seleccionar Profesor</option>
                                        <?php foreach($users as $p): if($p['role']==='profesor'): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['apellido'].' '.$p['nombre']) ?></option>
                                        <?php endif; endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary" style="padding:4px 8px;">Guardar</button>
                                </form>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="admin_action" value="delete_course">
                                    <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn" style="background:rgba(239, 68, 68, 0.2); color:var(--danger); padding:4px 8px;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 style="margin: 0;">Crear Nuevo Curso o Materia</h3>
        </div>
        <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end; background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 12px; border: 1px dashed var(--border-color);">
            <input type="hidden" name="admin_action" value="create_course">
            <div class="form-group" style="margin:0;">
                <label>Asignatura</label>
                <input class="form-control" type="text" name="nombre" placeholder="Ej: Química" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label>Curso</label>
                <select class="form-control" name="anio" required>
                    <option value="">Seleccionar Año</option>
                    <optgroup label="Educación Básica">
                        <option value="1ero básico">1ero básico</option>
                        <option value="2do básico">2do básico</option>
                        <option value="3ero básico">3ero básico</option>
                        <option value="4to básico">4to básico</option>
                        <option value="5to básico">5to básico</option>
                        <option value="6to básico">6to básico</option>
                        <option value="7mo básico">7mo básico</option>
                        <option value="8vo básico">8vo básico</option>
                    </optgroup>
                    <optgroup label="Educación Media">
                        <option value="1ero medio">1ero medio</option>
                        <option value="2do medio">2do medio</option>
                        <option value="3ero medio">3ero medio</option>
                        <option value="4to medio">4to medio</option>
                    </optgroup>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label>División</label>
                <input class="form-control" type="text" name="division" placeholder="Ej: B" required>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px;">Registrar Materia</button>
        </form>
    </div>
    
    <?php elseif($tab === 'assignments'): ?>
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 1rem;">Profesores Asignados a Cursos</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Año y División</th>
                        <th>Profesor Asignado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($assignments)): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 2rem;">No hay asignaciones registradas.</td></tr>
                    <?php else: ?>
                    <?php foreach($assignments as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['curso_nombre']) ?></td>
                        <td><?= htmlspecialchars($a['anio'] . ' - ' . $a['division']) ?></td>
                        <td><?= htmlspecialchars($a['prof_apellido'] . ', ' . $a['prof_nombre']) ?></td>
                        <td>
                            <form method="POST" action="index.php?action=dashboard&tab=assignments" style="display:inline;">
                                <input type="hidden" name="admin_action" value="delete_assignment">
                                <input type="hidden" name="course_id" value="<?= $a['course_id'] ?>">
                                <input type="hidden" name="prof_id" value="<?= $a['prof_id'] ?>">
                                <button type="submit" class="btn" style="background:rgba(239, 68, 68, 0.2); color:var(--danger); padding:4px 8px; font-size: 0.85rem;">Quitar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php elseif($tab === 'enrollments'): ?>
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 1rem;">Inscripción de Alumnos por Materia</h3>
        <p style="color:var(--text-secondary); margin-bottom: 2rem;">Selecciona una materia e inscribe a los alumnos en ella.</p>
        
        <?php foreach($enrollmentData as $edata): $c = $edata['course']; $st = $edata['students']; ?>
        <div style="background:rgba(255,255,255,0.02); border:1px solid var(--border-color); border-radius:8px; padding:1.5rem; margin-bottom:1rem;">
            <h4 style="margin-top:0; color:var(--accent-primary);"><?= htmlspecialchars($c['nombre'].' ('.$c['anio'].' - '.$c['division'].')') ?></h4>
            
            <form method="POST" action="index.php?action=dashboard&tab=enrollments" style="display:flex; gap:1rem; align-items:center; margin-bottom:1rem;">
                <input type="hidden" name="admin_action" value="assign_student">
                <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                <select name="student_id" class="form-control" style="width:300px; padding:6px;" required>
                    <option value="">-- Seleccionar Alumno para inscribir --</option>
                    <?php foreach($users as $u): if($u['role']==='alumno'): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></option>
                    <?php endif; endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary" style="padding:6px 12px;">+ Inscribir</button>
            </form>
            
            <?php if(empty($st)): ?>
                <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">No hay alumnos inscritos en esta materia.</p>
            <?php else: ?>
                <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                <?php foreach($st as $s): ?>
                    <div style="background:rgba(255,255,255,0.05); border-radius:16px; padding:4px 12px; display:flex; align-items:center; gap:0.5rem; font-size:0.85rem;">
                        <span><?= htmlspecialchars($s['apellido'].', '.$s['nombre']) ?></span>
                        <form method="POST" action="index.php?action=dashboard&tab=enrollments" style="margin:0;">
                            <input type="hidden" name="admin_action" value="delete_enrollment">
                            <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                            <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                            <button type="submit" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:1.1rem; padding:0; line-height:1;" title="Quitar alumno">&times;</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'views/layouts/footer.php'; ?>

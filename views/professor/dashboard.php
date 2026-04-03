<?php require_once 'views/layouts/header.php'; ?>

<div class="fade-in" style="margin-top: 2rem;">
    <h2>Panel de Control - Profesor</h2>
    <p>Gestión de notas para tus cursos asignados.</p>

    <?php if(empty($courseData)): ?>
        <div class="card" style="margin-top: 2rem;">
            <p>No tienes cursos asignados.</p>
        </div>
    <?php else: ?>
        <?php foreach($courseData as $cId => $data): ?>
            <div class="card" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem;"><?= htmlspecialchars($data['info']['nombre']) ?> (<?= htmlspecialchars($data['info']['anio']) ?> - Div <?= htmlspecialchars($data['info']['division']) ?>)</h3>
                
                <?php 
                $evaluaciones = $data['evaluations'];
                ?>

                <div style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); display:flex; gap:1rem;">
                    <button type="button" class="btn" style="background:transparent; border-bottom:2px solid var(--accent-primary); border-radius:0; padding:0.5rem 1rem;" onclick="switchProfTab(this, 'notas-<?= $cId ?>', <?= $cId ?>)">Cargar Notas</button>
                    <button type="button" class="btn" style="background:transparent; border-bottom:2px solid transparent; border-radius:0; color:var(--text-secondary); padding:0.5rem 1rem;" onclick="switchProfTab(this, 'eval-<?= $cId ?>', <?= $cId ?>)">Gestionar Evaluaciones</button>
                </div>

                <div id="notas-<?= $cId ?>" class="prof-tab-content-<?= $cId ?>">
                    <form action="index.php?action=dashboard" method="POST" style="display:flex; gap:1rem; margin-bottom: 1.5rem; align-items:flex-end; background:rgba(255,255,255,0.02); padding:1rem; border-radius:8px; border:1px solid var(--border-color);">
                        <input type="hidden" name="action" value="create_evaluation">
                        <input type="hidden" name="course_id" value="<?= $cId ?>">
                        
                        <div class="form-group" style="margin:0;">
                            <label style="font-size: 0.8rem; margin-bottom: 4px;">Periodo</label>
                            <select name="period" class="form-control" style="width:auto; padding: 6px;" required>
                                <option value="Semestre 1">Semestre 1</option>
                                <option value="Semestre 2">Semestre 2</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin:0;">
                            <label style="font-size: 0.8rem; margin-bottom: 4px;">Evaluación / Actividad</label>
                            <input type="text" name="type" class="form-control" style="width:200px; padding: 6px;" placeholder="Ej: Control de Lectura" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="padding: 6px 16px;">+ Crear Casilleros</button>
                    </form>

                    <?php if(empty($data['students'])): ?>
                        <p style="margin-top:1rem;">No hay alumnos en esta materia.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Notas Regulares</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['students'] as $student): ?>
                                    <tr>
                                        <td style="width: 30%;">
                                            <?= htmlspecialchars($student['info']['apellido'] . ', ' . $student['info']['nombre']) ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                            <?php if(empty($student['grades'])): ?>
                                                <span style="color:var(--text-secondary); font-size:0.85rem;">No hay evaluaciones registradas. Crea una arriba.</span>
                                            <?php else: ?>
                                                <?php foreach($student['grades'] as $g): ?>
                                                <form action="index.php?action=dashboard" method="POST" style="display:flex; flex-direction:column; background:rgba(255,255,255,0.03); padding:0.75rem; border-radius:12px; border: 1px solid var(--border-color); min-width: 140px; transition: all 0.3s ease;">
                                                    <input type="hidden" name="action" value="update_grade">
                                                    <input type="hidden" name="grade_id" value="<?= $g['id'] ?>">
                                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                                        <span style="font-size:0.65rem; text-transform: uppercase; color: var(--accent-primary); font-weight: 700;"><?= htmlspecialchars($g['type']) ?></span>
                                                        <span style="font-size:0.65rem; opacity: 0.5;"><?= htmlspecialchars($g['period']) ?></span>
                                                    </div>
                                                    <div style="display:flex; align-items:center; gap:0.5rem;">
                                                        <input type="number" step="0.1" min="1.0" max="7.0" name="value" class="form-control" style="width:65px; height: 32px; padding:4px 8px; font-weight: 600;" value="<?= htmlspecialchars($g['value'] ?? '') ?>" placeholder="-">
                                                        <button type="submit" class="btn btn-primary" style="padding:4px 10px; font-size:0.7rem; height: 32px; min-width: 45px;">Ok</button>
                                                    </div>
                                                </form>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="eval-<?= $cId ?>" class="prof-tab-content-<?= $cId ?>" style="display:none;">
                    <h4>Evaluaciones Activas</h4>
                    <?php if(empty($evaluaciones)): ?>
                        <p style="color:var(--text-secondary);">No hay evaluaciones creadas.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Periodo</th>
                                        <th>Actividad</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($evaluaciones as $ev): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ev['period']) ?></td>
                                        <td><?= htmlspecialchars($ev['type']) ?></td>
                                        <td>
                                            <form action="index.php?action=dashboard" method="POST" onsubmit="return confirm('¿Borrar esta evaluación para todos los alumnos? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="action" value="delete_evaluation">
                                                <input type="hidden" name="course_id" value="<?= $cId ?>">
                                                <input type="hidden" name="period" value="<?= htmlspecialchars($ev['period']) ?>">
                                                <input type="hidden" name="type" value="<?= htmlspecialchars($ev['type']) ?>">
                                                <button type="submit" class="btn" style="background:rgba(239, 68, 68, 0.2); color:var(--danger); padding:4px 8px;">Borrar Evaluación</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function switchProfTab(btn, tabId, courseId) {
    // Hide all tabs for this course
    document.querySelectorAll('.prof-tab-content-' + courseId).forEach(el => el.style.display = 'none');
    // Unstyle all buttons in this header
    const btns = btn.parentElement.querySelectorAll('button');
    btns.forEach(b => {
        b.style.borderBottomColor = 'transparent';
        b.style.color = 'var(--text-secondary)';
    });
    
    // Show target tab
    document.getElementById(tabId).style.display = 'block';
    
    // Style active button
    btn.style.borderBottomColor = 'var(--accent-primary)';
    btn.style.color = 'var(--text-primary)';
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>

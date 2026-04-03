<?php require_once 'views/layouts/header.php'; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid var(--accent-primary);">
            <div style="font-size: 2rem;">📊</div>
            <div>
                <h4 style="margin:0; font-size: 0.9rem; opacity: 0.7;">Promedio General</h4>
                <p style="margin:0; font-size: 1.5rem; font-weight: 700; color: var(--accent-primary);"><?= $avg > 0 ? number_format($avg, 1) : '-' ?></p>
            </div>
        </div>
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid var(--success);">
            <div style="font-size: 2rem;">✅</div>
            <div>
                <h4 style="margin:0; font-size: 0.9rem; opacity: 0.7;">Materias</h4>
                <p style="margin:0; font-size: 1.5rem; font-weight: 700; color: var(--success);"><?= count($assignedCourses) ?></p>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 2rem; margin-top: 2rem; flex-wrap: wrap;">
        <!-- Menú de Materias -->
        <div class="card fade-in" style="flex: 1; min-width: 250px;">
            <h3 style="margin-top: 0;">Mis Materias</h3>
            <?php if(empty($assignedCourses)): ?>
                <p style="color:var(--text-secondary);">No tienes materias asignadas.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php foreach($assignedCourses as $idx => $c): ?>
                        <button class="btn student-course-btn <?= $idx === 0 ? 'active' : '' ?>" 
                                onclick="showStudentCourse('course-<?= $c['id'] ?>', this)" 
                                style="text-align: left; background: <?= $idx === 0 ? 'var(--accent-primary)' : 'rgba(255,255,255,0.05)' ?>; border: none; padding: 1rem; border-radius: 8px; cursor: pointer; color: white; transition: all 0.2s;">
                            <div style="font-weight: 600; font-size: 1rem;"><?= htmlspecialchars($c['nombre']) ?></div>
                            <div style="font-size: 0.8rem; opacity: 0.7;"><?= htmlspecialchars($c['anio'] . ' - Div ' . $c['division']) ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detalle de Evaluaciones -->
        <div class="card fade-in" style="flex: 2; min-width: 300px;">
            <?php if(empty($assignedCourses)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                    Selecciona una materia para ver tus notas.
                </div>
            <?php else: ?>
                <?php foreach($assignedCourses as $idx => $c): ?>
                    <div id="course-<?= $c['id'] ?>" class="student-course-content" style="display: <?= $idx === 0 ? 'block' : 'none' ?>;">
                        <h3 style="margin-top: 0; color: var(--accent-primary);"><?= htmlspecialchars($c['nombre']) ?></h3>
                        
                        <?php 
                        $materiaName = $c['nombre'];
                        $grades = isset($courseGrades[$materiaName]) ? $courseGrades[$materiaName] : []; 
                        ?>

                        <?php if(empty($grades)): ?>
                            <p style="color:var(--text-secondary);">No hay evaluaciones registradas para esta materia aún.</p>
                        <?php else: ?>
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Período</th>
                                            <th>Evaluación</th>
                                            <th>Nota</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($grades as $grade): ?>
                                        <tr class="slide-in">
                                            <td><?= htmlspecialchars($grade['period']) ?></td>
                                            <td style="font-weight: 500;"><?= htmlspecialchars($grade['type']) ?></td>
                                            <td>
                                                <?php if($grade['value'] === null): ?>
                                                    <span style="color:var(--text-secondary); font-size: 0.9rem;">Pdte.</span>
                                                <?php else: ?>
                                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                        <div style="height: 6px; width: 60px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                                                            <div style="height: 100%; width: <?= ($grade['value']/7)*100 ?>%; background: <?= $grade['value'] >= 4.0 ? 'var(--success)' : 'var(--danger)' ?>;"></div>
                                                        </div>
                                                        <span class="badge <?= $grade['value'] >= 4.0 ? 'badge-success' : 'badge-danger' ?>" style="font-size: 1rem; padding: 6px 12px; border-radius: 8px;">
                                                            <?= number_format($grade['value'], 1) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showStudentCourse(courseId, btn) {
    // Esconder todos los contenidos
    document.querySelectorAll('.student-course-content').forEach(el => el.style.display = 'none');
    
    // Quitar background activo de todos los botones
    document.querySelectorAll('.student-course-btn').forEach(b => {
        b.style.background = 'rgba(255,255,255,0.05)';
        b.classList.remove('active');
    });
    
    // Mostrar contenido seleccionado
    document.getElementById(courseId).style.display = 'block';
    
    // Activar boton seleccionado
    btn.style.background = 'var(--accent-primary)';
    btn.classList.add('active');
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>

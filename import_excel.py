import openpyxl
import sqlite3
import bcrypt
import uuid
import re

print("Iniciando parseo del Excel Lista_alumnos.xlsx...")

try:
    wb = openpyxl.load_workbook('Lista_alumnos.xlsx', data_only=True)
    sheet = wb.active
    
    conn = sqlite3.connect('database.sqlite')
    c = conn.cursor()
    
    # Pre-cargar IDs de las materias correctas segun nombre (creadas en el script seed_teachers_students.php o init_db.php)
    c.execute("SELECT id, nombre FROM courses")
    map_courses = {row[1]: row[0] for row in c.fetchall()}
    
    # Garantizar que las materias existan si por casualidad faltaron o tienen acentos
    materias_excel = [
        (4, 'Matemáticas'),
        (5, 'Lenguaje'),
        (6, 'Ciencias'),
        (7, 'Historia y Geografía'),
        (8, 'Artes'),
        (9, 'Educación Física')
    ]
    
    for idx, mat in materias_excel:
        if mat not in map_courses:
            c.execute("INSERT INTO courses (nombre, anio, division) VALUES (?, ?, ?)", (mat, "1ero", "A"))
            map_courses[mat] = c.lastrowid
            
    # Contadores
    alumnos_insertados = 0
    notas_insertadas = 0

    password_hash = bcrypt.hashpw(b"alum123", bcrypt.gensalt()).decode("utf-8")
    
    # Procesar filas a partir de la 2
    for row in sheet.iter_rows(min_row=2, values_only=True):
        if not row[2] or not row[3]: # Si Nombre o Apellido estan vacios, saltar
            continue
            
        nombre = str(row[2]).strip()
        apellido = str(row[3]).strip()
        
        # Generar username base "jlopez" pero con sufijo unico
        uid = str(uuid.uuid4().hex[:6])
        username = re.sub(r'[^a-zA-Z0-9]', '', nombre.lower()[:1] + apellido.lower()) + "_" + uid
        
        # Insertar Usuario
        c.execute("INSERT INTO users (username, password_hash, role, nombre, apellido) VALUES (?, ?, 'alumno', ?, ?)", 
                 (username, password_hash, nombre, apellido))
        user_id = c.lastrowid
        
        # Asignar a un curso base en tabla students 
        curso_base_id = map_courses['Matemáticas']
        c.execute("INSERT INTO students (user_id, course_id) VALUES (?, ?)", (user_id, curso_base_id))
        alumnos_insertados += 1
        
        # Registrar notas para ese alumno
        for idx, materia in materias_excel:
            nota = row[idx]
            if nota is not None:
                try:
                    valor_nota = float(nota)
                    c.execute("INSERT INTO grades (student_id, course_id, period, type, value) VALUES (?, ?, 'Trimestre 1', 'Nota Final', ?)",
                              (user_id, map_courses[materia], valor_nota))
                    notas_insertadas += 1
                except ValueError:
                    pass
                    
    conn.commit()
    conn.close()
    
    print(f"¡Éxito! Se insertaron {alumnos_insertados} alumnos y {notas_insertadas} notas en la base de datos.")
    
except Exception as e:
    print("Error durante la importación:", str(e))

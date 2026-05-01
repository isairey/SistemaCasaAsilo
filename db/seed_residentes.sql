-- Datos de ejemplo para la tabla `residente` (ajustados para usar solo usuarios con id 3 y 4)
-- Revisa los valores de `id_usuario` y `creado_por` antes de importar en tu base de datos.

INSERT INTO `residente` (
  `id_usuario`,`nombre`,`apellidos`,`fecha_nacimiento`,`genero`,
  `enfermedades`,`discapacidades`,`medicacion`,`alergias`,`observaciones`,
  `foto`,`contacto_emergencia_nombre`,`contacto_emergencia_telefono`,`contacto_emergencia_relacion`,`creado_por`
) VALUES
(3, 'María', 'Pérez García', '1945-09-12', 'Femenino',
 'Hipertensión arterial; Diabetes tipo II', 'Movilidad reducida (usa andadera)', 'Metformina 500mg; Lisinopril 10mg', 'Penicilina', 'Requiere control de glucosa diario',
 'residentes/maria_perez.jpg', 'Juan Pérez', '+52 55 1234 5678', 'Hijo', 3),

(4, 'José', 'Ramírez López', '1938-03-05', 'Masculino',
 'Enfermedad pulmonar crónica (EPOC)', '', 'Salbutamol inhalador PRN', 'Ninguna conocida', 'Buena supervisión familiar; alergia a mariscos (confirmada)',
 'residentes/jose_ramirez.jpg', 'Ana López', '+52 55 2345 6789', 'Nieta', 4),

(3, 'Dolores', 'Hernández', '1952-12-20', 'Femenino',
 'Artritis reumatoide', 'Discapacidad visual parcial', 'AINEs según prescripción', 'Aspirina', 'Necesita ayuda para higiene personal',
 'residentes/dolores_hernandez.jpg', 'Miguel Hernández', '+52 55 3456 7890', 'Hermano', 3),

(4, 'Roberto', 'Molina', '1949-07-01', 'Masculino',
 'Hipertensión; Insuficiencia renal crónica', '', 'Amlodipino 5mg; Furosemida 20mg', 'Ninguna', 'Control renal mensual',
 'residentes/roberto_molina.jpg', 'Laura Molina', '+52 55 4567 8901', 'Hija', 4),

(3, 'Carmen', 'Sosa', '1935-11-30', 'Femenino',
 'Demencia leve', 'Dependencia parcial para actividades', 'Donepezilo 5mg', 'Lactosa (leve)', 'Vive con familiar, horario de visitas restringido',
 'residentes/carmen_sosa.jpg', 'Pedro Sosa', '+52 55 5678 9012', 'Esposo', 3),

(4, 'Héctor', 'Vargas', '1950-02-15', 'Masculino',
 'Diabetes tipo II', '', 'Insulina NPH según endocrinología', 'Ninguna', 'Control de heridas en pie izquierdo',
 'residentes/hector_vargas.jpg', 'Rosa Vargas', '+52 55 6789 0123', 'Esposa', 4),

(3, 'Ana', 'Ortega', '1947-05-22', 'Femenino',
 'Hipotiroidismo', '', 'Levotiroxina 50mcg', 'Penicilina (leve)', 'Participa en terapia ocupacional',
 'residentes/ana_ortega.jpg', 'Camila Ortega', '+52 55 7890 1234', 'Hija', 3),

(4, 'Elena', 'Castro', '1939-10-10', 'Femenino',
 'Insuficiencia cardiaca', 'Limitación de marcha', 'Carvedilol 6.25mg; Espironolactona 25mg', 'Alergia a sulfonamidas', 'Requiere seguimiento cardiológico frecuente',
 'residentes/elena_castro.jpg', 'Arturo Castro', '+52 55 8901 2345', 'Hijo', 4),

(3, 'Ricardo', 'Lozano', '1942-04-18', 'Masculino',
 'Parkinson (leve)', 'Temblor en extremidades', 'Levodopa/Carbidopa según pauta', 'Ninguna', 'Asistencia para la marcha y medicamentos',
 'residentes/ricardo_lozano.jpg', 'Sonia Lozano', '+52 55 9012 3456', 'Esposa', 3),

(4, 'Beatriz', 'Alonso', '1954-08-09', 'Femenino',
 'Hipertensión', '', 'Enalapril 10mg', 'Ninguna', 'Participa en grupos de memoria',
 'residentes/beatriz_alonso.jpg', 'Fernando Alonso', '+52 55 0123 4567', 'Hijo', 4);

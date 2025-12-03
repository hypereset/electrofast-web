-- --------------------------------------------------------
-- SCRIPT: KITS DE PROYECTOS "SALVA SEMESTRE"
-- --------------------------------------------------------

-- 1. Crear la nueva categoría "Proyectos y Prácticas"
INSERT INTO categorias (id_categoria, nombre, descripcion, activa) 
VALUES (11, 'Proyectos Escolares', 'Kits con todos los componentes para tu tarea', 1)
ON DUPLICATE KEY UPDATE nombre = 'Proyectos Escolares';

-- 2. Insertar los Productos (Kits de Proyectos)
INSERT INTO productos 
(nombre, descripcion, precio_unitario, precio_mayoreo, stock_actual, stock_minimo, imagen_url, estado, id_categoria) 
VALUES 
(
    'Proyecto: Luces Secuenciales (Auto Fantástico)', 
    'Incluye: CI 555, CI CD4017, 10 LEDs Rojos, Potenciómetro 10k (velocidad), Capacitores, Resistencias y Broche 9V. ¡Solo ensambla!', 
    85.00, 
    75.00, 
    40, 
    5, 
    'proy_secuencial.jpg', 
    'activo', 
    11
),
(
    'Proyecto: Contador Digital 0-9', 
    'Incluye: Display 7 Segmentos (Cátodo Común), CI CD4026 (Contador/Driver), Push Button, Resistencias y Diagrama de conexión.', 
    110.00, 
    95.00, 
    35, 
    5, 
    'proy_contador.jpg', 
    'activo', 
    11
),
(
    'Proyecto: Semáforo Inteligente (Arduino)', 
    'Versión con Microcontrolador. Incluye: Arduino Nano (Compatible), Cable USB, 2 LEDs Rojos, 2 Amarillos, 2 Verdes, Resistencias y Protoboard Mini.', 
    220.00, 
    195.00, 
    20, 
    3, 
    'proy_semaforo.jpg', 
    'activo', 
    11
),
(
    'Proyecto: Control de Motor (Puente H)', 
    'Aprende a invertir el giro. Incluye: Motor DC 3-6V, CI L293D (Puente H), 2 Push Buttons, Portapilas y Cables.', 
    140.00, 
    125.00, 
    25, 
    4, 
    'proy_motor.jpg', 
    'activo', 
    11
),
(
    'Kit Fuente de Poder Casera (LM317)', 
    'Haz tu propia fuente regulable 1.2V a 12V. Incluye: LM317T, Disipador, Potenciómetro, Resistencias, Capacitores y Jack DC.', 
    95.00, 
    85.00, 
    30, 
    5, 
    'proy_fuente.jpg', 
    'activo', 
    11
);

-- Verificación
SELECT id_producto, nombre, precio_unitario FROM productos WHERE id_categoria = 11;
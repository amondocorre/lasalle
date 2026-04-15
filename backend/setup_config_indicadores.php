<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Crear tabla de configuración de indicadores
$sql_table = "CREATE TABLE IF NOT EXISTS config_indicadores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('académico', 'conductual', 'presentación') NOT NULL,
    indicador VARCHAR(255) NOT NULL,
    icono VARCHAR(50) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$conn->query($sql_table);

// 2. Insertar valores académicos
$academicos = [
    "No ingresó a clases.", "Necesita apoyo.", "No participó en clase.", 
    "Trabajo: (Incompleto / No presentó).", "Investigación: (Incompleto / No presentó).", 
    "Cuaderno: (Incompleto / No presentó).", "Copió tarea de compañero.", 
    "No trajo material.", "No rindió evaluación.", "No trabaja en clases."
];
foreach ($academicos as $ind) {
    $conn->query("INSERT INTO config_indicadores (tipo, indicador) VALUES ('académico', '$ind') ON DUPLICATE KEY UPDATE indicador=indicador");
}

// 3. Insertar valores conductuales
$conductuales = [
    "No atiende explicaciones.", "Se distrae frecuentemente.", "Se ausentó sin justificación.", 
    "Faltó a la verdad.", "No respeta las cosas de sus compañeros.", "Se comportó mal en formaciones.", 
    "No participa en actividades del colegio/clase.", "Llega tarde al colegio/clase.", 
    "Muestra miedo excesivo o retraimiento.", "Tiene cambios bruscos de humor.", 
    "Expresa temor hacia ciertos adultos o compañeros.", "Manifiesta tristeza persistente."
];
foreach ($conductuales as $ind) {
    $conn->query("INSERT INTO config_indicadores (tipo, indicador) VALUES ('conductual', '$ind') ON DUPLICATE KEY UPDATE indicador=indicador");
}

// 4. Insertar valores de presentación
$presentacion = [
    ['Cabello largo/fuera de norma', 'Scissors'],
    ['Uniforme incompleto', 'Shirt'],
    ['Uñas largas/Maquillaje', 'Sparkles'],
    ['Sin credencial', 'Contact2']
];
foreach ($presentacion as $p) {
    $ind = $p[0];
    $ico = $p[1];
    $conn->query("INSERT INTO config_indicadores (tipo, indicador, icono) VALUES ('presentación', '$ind', '$ico') ON DUPLICATE KEY UPDATE indicador=indicador");
}

echo "Tabla config_indicadores creada y poblada correctamente.";

$conn->close();
?>

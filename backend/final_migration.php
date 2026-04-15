<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Añadir columna gravedad
$sql1 = "ALTER TABLE novedades ADD COLUMN gravedad ENUM('Leve', 'Grave', 'Muy Grave') DEFAULT 'Leve' AFTER comentario_docente";
if ($conn->query($sql1)) {
    echo "Columna 'gravedad' añadida correctamente.\n";
} else {
    echo "Error añadiendo gravedad: " . $conn->error . "\n";
}

// 2. Actualizar tipos de indicadores
$sql2 = "ALTER TABLE novedad_indicadores MODIFY COLUMN tipo ENUM('académico', 'conductual', 'presentación') NOT NULL";
if ($conn->query($sql2)) {
    echo "Tipos de indicadores actualizados correctamente.\n";
} else {
    echo "Error actualizando tipos: " . $conn->error . "\n";
}

$conn->close();
?>

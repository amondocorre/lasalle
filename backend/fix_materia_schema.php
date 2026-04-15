<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Drop FK
if (!$conn->query("ALTER TABLE novedades DROP FOREIGN KEY novedades_ibfk_3")) {
    echo "Warning: No se pudo eliminar la FK (tal vez no existe): " . $conn->error . "\n";
}

// 2. Modify Column
if ($conn->query("ALTER TABLE novedades MODIFY COLUMN materia_id INT UNSIGNED NULL")) {
    echo "Columna materia_id ahora permite NULL.\n";
} else {
    echo "Error modificando columna: " . $conn->error . "\n";
}

// 3. Re-add FK with ON DELETE SET NULL
$sql_fk = "ALTER TABLE novedades ADD CONSTRAINT novedades_ibfk_3 FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE SET NULL";
if ($conn->query($sql_fk)) {
    echo "Restricción de clave foránea reactivada con SET NULL.\n";
} else {
    echo "Error reactivando FK: " . $conn->error . "\n";
}

$conn->close();
?>

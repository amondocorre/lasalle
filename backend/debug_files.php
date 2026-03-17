<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'colegiolasalle';
$conn = new mysqli($host, $user, $pass, $db);
$res = $conn->query("SELECT l.id FROM licencias l WHERE l.rude = '2024001001'");
while($row = $res->fetch_assoc()) {
    $id = $row['id'];
    echo "Licencia ID: $id\n";
    $res2 = $conn->query("SELECT * FROM archivos_adjuntos WHERE licencia_id = $id");
    while($f = $res2->fetch_assoc()) {
        echo "  - File: {$f['nombre_archivo']} (Original: {$f['nombre_original']})\n";
    }
}
$conn->close();

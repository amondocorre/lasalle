<?php
$mysqli = new mysqli("localhost", "root", "", "colegiolasalle");
$res = $mysqli->query("SELECT id, nombre FROM perfiles");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} - Perfil: {$row['nombre']}\n";
}

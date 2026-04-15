<?php
$mysqli = new mysqli("localhost", "root", "", "colegiolasalle");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 1. Insert new menu item
$label = 'Reporte Mensual';
$path = '/reporte-mensual';
$icon = 'FileText';
$orden = 100; // Large number to put it at the end

$stmt = $mysqli->prepare("INSERT INTO menus (label, path, icon, orden, activo) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("sssi", $label, $path, $icon, $orden);
if ($stmt->execute()) {
    $menu_id = $mysqli->insert_id;
    echo "Menu inserted with ID: $menu_id\n";

    // 2. Assign to admin and regente profiles
    // Find IDs for admin and regente
    $res = $mysqli->query("SELECT id, nombre FROM perfiles WHERE nombre IN ('admin', 'regente')");
    while ($row = $res->fetch_assoc()) {
        $perfil_id = $row['id'];
        $mysqli->query("INSERT INTO perfil_menu (perfil_id, menu_id, acceso_lectura, acceso_escritura) VALUES ($perfil_id, $menu_id, 1, 1)");
        echo "Assigned to profile: " . $row['nombre'] . "\n";
    }
} else {
    echo "Error inserting menu: " . $mysqli->error . "\n";
}

$mysqli->close();
?>

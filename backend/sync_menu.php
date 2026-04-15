<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Insertar el nuevo menú
$sql_menu = "INSERT INTO menus (label, path, icon, orden, activo) VALUES ('Presentación y Normas', '/novedades', 'ShieldCheck', 15, 1)";
if ($conn->query($sql_menu)) {
    $menu_id = $conn->insert_id;
    echo "Menú 'Presentación y Normas' insertado con ID: $menu_id\n";

    // 2. Asignar a Administrador (1) y Regente (2)
    $conn->query("INSERT INTO perfil_menu (perfil_id, menu_id, acceso_lectura, acceso_escritura) VALUES (1, $menu_id, 1, 1)");
    $conn->query("INSERT INTO perfil_menu (perfil_id, menu_id, acceso_lectura, acceso_escritura) VALUES (2, $menu_id, 1, 1)");
    echo "Permisos asignados a Administrador y Regente.\n";
} else {
    echo "Error al insertar menú: " . $conn->error . "\n";
}

$conn->close();
?>

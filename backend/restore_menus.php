<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Re-añadir Roles y Permisos (Si no existe)
$check = $conn->query("SELECT id FROM menus WHERE path = '/roles'");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO menus (label, path, icon) VALUES ('Roles y Permisos', '/roles', 'Shield')");
    $menu_id = $conn->insert_id;
    $conn->query("INSERT INTO perfil_menu (perfil_id, menu_id) VALUES (1, $menu_id)"); // Admin
    echo "Restaurado: Roles y Permisos\n";
}

// 2. Re-añadir Licencias (Si no existe)
$check = $conn->query("SELECT id FROM menus WHERE path = '/licencias'");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO menus (label, path, icon) VALUES ('Licencias', '/licencias', 'FileText')");
    $menu_id = $conn->insert_id;
    $conn->query("INSERT INTO perfil_menu (perfil_id, menu_id) VALUES (1, $menu_id)"); // Admin
    $conn->query("INSERT INTO perfil_menu (perfil_id, menu_id) VALUES (2, $menu_id)"); // Regente
    echo "Restaurado: Licencias\n";
}

$conn->close();
?>

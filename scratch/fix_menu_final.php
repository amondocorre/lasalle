<?php
$mysqli = new mysqli("localhost", "root", "", "colegiolasalle");
if ($mysqli->connect_error) die("Connection failed: " . $mysqli->connect_error);

$menu_label = 'Reporte Mensual';
$res = $mysqli->query("SELECT id FROM menus WHERE label = '$menu_label'");
$menu = $res->fetch_assoc();

if ($menu) {
    $menu_id = $menu['id'];
    echo "Using existing Menu ID: $menu_id\n";
    
    // Profiles: Administrador (1), Regente (2)
    $profiles = [1, 2];
    foreach ($profiles as $p_id) {
        $check = $mysqli->query("SELECT * FROM perfil_menu WHERE perfil_id = $p_id AND menu_id = $menu_id");
        if ($check->num_rows == 0) {
            $mysqli->query("INSERT INTO perfil_menu (perfil_id, menu_id, acceso_lectura, acceso_escritura) VALUES ($p_id, $menu_id, 1, 1)");
            echo "Assigned to profile ID: $p_id\n";
        } else {
            echo "Profile ID: $p_id already has access\n";
        }
    }
} else {
    echo "Menu '$menu_label' not found\n";
}

$mysqli->close();
?>

<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Limpiar configuración actual
$conn->query("TRUNCATE TABLE config_indicadores");

// 2. Insertar valores académicos (Nuevos/Actualizados)
$academicos = [
    "No ingresó a clases.", "Necesita apoyo.", "No participó en clase.", 
    "Trabajo: (Incompleto / No presentó).", "Investigación: (Incompleto / No presentó).", 
    "Cuaderno: (Incompleto / No presentó).", "Copió tarea de compañero.", 
    "No trajo material.", "No rindió evaluación.", "No trabaja en clases."
];
foreach ($academicos as $ind) {
    $stmt = $conn->prepare("INSERT INTO config_indicadores (tipo, indicador) VALUES ('académico', ?)");
    $stmt->bind_param("s", $ind);
    $stmt->execute();
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
    $stmt = $conn->prepare("INSERT INTO config_indicadores (tipo, indicador) VALUES ('conductual', ?)");
    $stmt->bind_param("s", $ind);
    $stmt->execute();
}

// 4. Insertar valores de presentación
$presentacion = [
    ['Cabello largo/fuera de norma', 'Scissors'],
    ['Uniforme incompleto', 'Shirt'],
    ['Uñas largas/Maquillaje', 'Sparkles'],
    ['Sin credencial', 'Contact2']
];
foreach ($presentacion as $p) {
    $stmt = $conn->prepare("INSERT INTO config_indicadores (tipo, indicador, icono) VALUES ('presentación', ?, ?)");
    $stmt->bind_param("ss", $p[0], $p[1]);
    $stmt->execute();
}

echo "Configuración de indicadores reiniciada con éxito.\n";

// OPCIONAL: Descomentar si quieres borrar también el historial de lo que ya se registró
/*
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE novedad_indicadores");
$conn->query("TRUNCATE TABLE novedades");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Historial de novedades borrado.";
*/

$conn->close();
?>

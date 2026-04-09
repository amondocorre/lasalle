<?php
// Script de diagnóstico para el Monitor de Accesos
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('index.php'); // Intentamos cargar el entorno de CI

$CI =& get_instance();
$CI->load->database();

echo "<h1>Prueba de Diagnóstico</h1>";

$tables = ['estudiantes', 'cursos', 'estudiante_curso', 'log_acceso_padres'];

foreach ($tables as $table) {
    $exists = $CI->db->table_exists($table);
    echo "Tabla <b>$table</b>: " . ($exists ? "<span style='color:green'>EXISTE</span>" : "<span style='color:red'>NO EXISTE</span>") . "<br>";
    
    if ($exists) {
        $fields = $CI->db->list_fields($table);
        echo "Columnas: " . implode(', ', $fields) . "<br><br>";
    }
}

echo "<h2>Prueba de Consulta Simple:</h2>";
try {
    $q = $CI->db->limit(5)->get('estudiantes');
    echo "Conexión a 'estudiantes' exitosa. Total en esta tabla: " . $CI->db->count_all('estudiantes');
} catch (Exception $e) {
    echo "Error en consulta: " . $e->getMessage();
}

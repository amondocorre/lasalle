<?php
// Script de diagnóstico de autenticación
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'colegiolasalle';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$username = 'admin';
$password_plain = 'admin123';

$res = $conn->query("SELECT * FROM usuarios WHERE username = '$username'");
if ($res->num_rows > 0) {
    $user = $res->fetch_assoc();
    echo "Usuario encontrado:\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Nombre: " . $user['nombre'] . "\n";
    echo "Activo: " . $user['activo'] . "\n";
    echo "Hash en DB: " . $user['password'] . "\n";
    
    if (password_verify($password_plain, $user['password'])) {
        echo "\nRESULTADO: ¡La contraseña coincide con el hash!\n";
    } else {
        echo "\nRESULTADO: ERROR - La contraseña NO coincide con el hash almacenado.\n";
        
        // Vamos a re-actualizarlo AHORA MISMO con un hash garantizado
        $new_hash = password_hash($password_plain, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET password = ?, activo = 1 WHERE id = ?");
        $stmt->bind_param("si", $new_hash, $user['id']);
        $stmt->execute();
        echo "ACCION: He regenerado el hash nuevamente. Nuevo hash: $new_hash\n";
        
        if (password_verify($password_plain, $new_hash)) {
            echo "VERIFICACION: El nuevo hash ahora sí coincide.\n";
        }
    }
} else {
    echo "ERROR: El usuario '$username' no existe en la base de datos.\n";
    $new_hash = password_hash($password_plain, PASSWORD_DEFAULT);
    $conn->query("INSERT INTO usuarios (username, password, nombre, rol) VALUES ('$username', '$new_hash', 'Administrador', 'admin')");
    echo "ACCION: Usuario creado con éxito.\n";
}

$conn->close();
?>

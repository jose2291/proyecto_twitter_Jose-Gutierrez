<?php
// Datos de conexión a Oracle
$usuario = 'C##proyecto_twi'; // Datos reales actualizados
$contraseña = 'proyecto';
$host = 'DESKTOP-IGUNIAF';
$puerto = '1521';
$servicio = 'XE';

// Cadena de conexión
$dsn = "(DESCRIPTION =
            (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = $puerto))
            (CONNECT_DATA =
                (SERVICE_NAME = $servicio)
            )
        )";

// Intentar conectar
$conn = oci_connect($usuario, $contraseña, $dsn);

if (!$conn) {
    $e = oci_error();
    echo "Error de conexión: " . $e['message'];
    exit;
}

// Retornar la conexión para que sea utilizada en otros archivos
return $conn;
?>

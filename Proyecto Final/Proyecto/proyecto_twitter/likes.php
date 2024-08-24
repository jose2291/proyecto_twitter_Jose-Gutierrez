<?php
session_start();
$conn = include 'conexion.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$publicacion_id = $_POST['publicacion_id'];

// Verificar si el usuario ya ha dado "Me gusta"
$check_sql = "SELECT COUNT(*) FROM Likes WHERE usuario_id = :usuario_id AND publicacion_id = :publicacion_id";
$check_stmt = oci_parse($conn, $check_sql);
oci_bind_by_name($check_stmt, ':usuario_id', $usuario_id);
oci_bind_by_name($check_stmt, ':publicacion_id', $publicacion_id);
oci_execute($check_stmt);
$row = oci_fetch_assoc($check_stmt);

if ($row['COUNT(*)'] > 0) {
    // Si ya ha dado "Me gusta", eliminar el "Me gusta"
    $delete_sql = "DELETE FROM Likes WHERE usuario_id = :usuario_id AND publicacion_id = :publicacion_id";
    $delete_stmt = oci_parse($conn, $delete_sql);
    oci_bind_by_name($delete_stmt, ':usuario_id', $usuario_id);
    oci_bind_by_name($delete_stmt, ':publicacion_id', $publicacion_id);
    oci_execute($delete_stmt);
} else {
    // Si no ha dado "Me gusta", agregar un "Me gusta"
    $insert_sql = "INSERT INTO Likes (usuario_id, publicacion_id, fecha_like) VALUES (:usuario_id, :publicacion_id, SYSDATE)";
    $insert_stmt = oci_parse($conn, $insert_sql);
    oci_bind_by_name($insert_stmt, ':usuario_id', $usuario_id);
    oci_bind_by_name($insert_stmt, ':publicacion_id', $publicacion_id);
    oci_execute($insert_stmt);
}

// Redirigir de vuelta a mi_historia.php
header("Location: mi_historia.php");
exit();
?>

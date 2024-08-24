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
$contenido_comentario = htmlspecialchars($_POST['comentario'], ENT_QUOTES, 'UTF-8');

// Verificar si el comentario no está vacío
if (!empty($contenido_comentario)) {
    // Insertar el comentario en la base de datos
    $insert_sql = "INSERT INTO Comentarios (publicacion_id, usuario_id, contenido, fecha_comentario) 
                   VALUES (:publicacion_id, :usuario_id, :contenido_comentario, SYSDATE)";
    $insert_stmt = oci_parse($conn, $insert_sql);
    oci_bind_by_name($insert_stmt, ':publicacion_id', $publicacion_id);
    oci_bind_by_name($insert_stmt, ':usuario_id', $usuario_id);
    oci_bind_by_name($insert_stmt, ':contenido_comentario', $contenido_comentario);
    
    if (oci_execute($insert_stmt)) {
        // Redirigir a mi_historia.php después de guardar el comentario
        header("Location: mi_historia.php");
        exit();
    } else {
        // Manejar error en la inserción
        echo "Error al guardar el comentario.";
    }

    oci_free_statement($insert_stmt);
} else {
    echo "El comentario no puede estar vacío.";
}

oci_close($conn);
?>

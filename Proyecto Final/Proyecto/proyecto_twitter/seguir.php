<?php
session_start();
$conn = include 'conexion.php'; // Conexión a la base de datos

if (isset($_POST['usuario_id']) && isset($_POST['publicacion_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $seguido_id = $_POST['usuario_id'];

    // Verificar si el usuario ya sigue al seguido
    $sql = "SELECT COUNT(*) AS cuenta FROM Seguidores WHERE USUARIO_ID = :seguido_id AND SEGUIDOR_USUARIO_ID = :seguidor_id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':seguido_id', $seguido_id);
    oci_bind_by_name($stmt, ':seguidor_id', $usuario_id);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    if ($row['CUENTA'] > 0) {
        // Eliminar la relación si ya lo sigue
        $sql = "DELETE FROM Seguidores WHERE USUARIO_ID = :seguido_id AND SEGUIDOR_USUARIO_ID = :seguidor_id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':seguido_id', $seguido_id);
        oci_bind_by_name($stmt, ':seguidor_id', $usuario_id);
        oci_execute($stmt);
        echo 'seguir';
    } else {
        // Añadir la relación si no lo sigue
        $sql = "INSERT INTO Seguidores (USUARIO_ID, SEGUIDOR_USUARIO_ID) VALUES (:seguido_id, :seguidor_id)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':seguido_id', $seguido_id);
        oci_bind_by_name($stmt, ':seguidor_id', $usuario_id);
        oci_execute($stmt);
        echo 'seguido';
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>

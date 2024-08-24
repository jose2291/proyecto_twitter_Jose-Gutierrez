<?php
session_start();
$conn = include 'conexion.php'; // Incluye el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$usuario_id = $_SESSION['usuario_id'];

// Obtener la información del usuario
$sql = "SELECT nombre_usuario, foto_perfil FROM Usuarios WHERE usuario_id = :usuario_id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':usuario_id', $usuario_id);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);

// Verificar si los datos del usuario se recuperaron correctamente
if ($row) {
    $nombre_usuario = $row['NOMBRE_USUARIO'];
    $foto_perfil_lob = $row['FOTO_PERFIL'];

    // Verificar si el tamaño del LOB es mayor a 0 antes de leerlo
    if (!is_null($foto_perfil_lob) && $foto_perfil_lob->size() > 0) {
        $foto_perfil_lob->rewind(); // Mueve el puntero al inicio
        $foto_perfil = $foto_perfil_lob->read($foto_perfil_lob->size());
        $foto_perfil_base64 = base64_encode($foto_perfil);
    } else {
        $foto_perfil_base64 = null;
    }
} else {
    echo "Error: No se encontró información del usuario.";
    exit();
}

// Proceso de actualización de la imagen si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['nueva_foto_perfil']) && $_FILES['nueva_foto_perfil']['error'] == UPLOAD_ERR_OK) {
    $nueva_foto_perfil = file_get_contents($_FILES['nueva_foto_perfil']['tmp_name']);

    // Actualizar la foto de perfil
    $sql_update = "UPDATE Usuarios SET foto_perfil = EMPTY_BLOB() WHERE usuario_id = :usuario_id RETURNING foto_perfil INTO :foto_perfil";
    $stmt_update = oci_parse($conn, $sql_update);

    // Crear un descriptor LOB para manejar el BLOB
    $lob = oci_new_descriptor($conn, OCI_D_LOB);
    oci_bind_by_name($stmt_update, ':usuario_id', $usuario_id);
    oci_bind_by_name($stmt_update, ':foto_perfil', $lob, -1, OCI_B_BLOB);

    if (oci_execute($stmt_update, OCI_NO_AUTO_COMMIT)) {
        // Escribir la nueva imagen en el LOB
        $lob->save($nueva_foto_perfil);
        oci_commit($conn);
        echo "Foto de perfil actualizada correctamente.";
        // Refrescar la página para mostrar la nueva imagen
        header("Refresh:0");
    } else {
        $e = oci_error($stmt_update);
        echo "Error al actualizar la foto de perfil: " . $e['message'];
    }

    // Liberar recursos
    $lob->free();
    oci_free_statement($stmt_update);
}

oci_free_statement($stmt);
oci_close($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Mi Red Social</title>
    <link rel="stylesheet" href="css/perfil.css"> <!-- Enlace al archivo CSS externo -->
</head>
<body>
    <header>
        <div class="navbar">
            <h1>Mi Red Social</h1>
            <a href="timeline.php" class="btn">Volver al Timeline</a>
        </div>
    </header>

    <div class="perfil-container">
        <h2>Perfil de @<?php echo $nombre_usuario; ?></h2>

        <!-- Mostrar la foto de perfil -->
        <?php if ($foto_perfil_base64): ?>
            <img src="data:image/jpeg;base64,<?php echo $foto_perfil_base64; ?>" alt="Foto de Perfil" class="profile-pic">
        <?php else: ?>
            <p>Foto de perfil no disponible.</p>
        <?php endif; ?>

        <h2>Cambiar Foto de Perfil</h2>
        <form action="perfil.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="nueva_foto_perfil" accept="image/*" required>
            <button type="submit">Actualizar Foto</button>
        </form>
    </div>
</body>
</html>

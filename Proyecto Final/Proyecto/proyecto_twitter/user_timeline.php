<?php
session_start();
$conn = include 'conexion.php'; // Incluye el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se ha proporcionado un término de búsqueda
if (!isset($_GET['search'])) {
    header("Location: timeline.php");
    exit();
}

$search = htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8');

// Buscar el usuario por nombre de usuario
$sql_user = "SELECT usuario_id, nombre_usuario, foto_perfil FROM Usuarios WHERE nombre_usuario = :search";
$stmt_user = oci_parse($conn, $sql_user);
oci_bind_by_name($stmt_user, ':search', $search);
oci_execute($stmt_user);
$user_data = oci_fetch_assoc($stmt_user);

if (!$user_data) {
    echo "Usuario no encontrado.";
    exit();
}

$usuario_id_buscado = $user_data['USUARIO_ID'];
$nombre_usuario_buscado = $user_data['NOMBRE_USUARIO'];
$foto_perfil_lob_buscado = $user_data['FOTO_PERFIL'];

if (!is_null($foto_perfil_lob_buscado) && $foto_perfil_lob_buscado->size() > 0) {
    $foto_perfil_lob_buscado->rewind();
    $foto_perfil_buscado = $foto_perfil_lob_buscado->read($foto_perfil_lob_buscado->size());
    $foto_perfil_base64_buscado = base64_encode($foto_perfil_buscado);
} else {
    $foto_perfil_base64_buscado = null;
}

// Obtener las publicaciones del usuario buscado
$publicaciones_sql = "SELECT contenido, fecha_publicacion, imagen_publicacion, video_publicacion 
                      FROM Publicaciones 
                      WHERE usuario_id = :usuario_id 
                      ORDER BY fecha_publicacion DESC";
$publicaciones_stmt = oci_parse($conn, $publicaciones_sql);
oci_bind_by_name($publicaciones_stmt, ':usuario_id', $usuario_id_buscado);
oci_execute($publicaciones_stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline de @<?php echo $nombre_usuario_buscado; ?></title>
    <link rel="stylesheet" href="css/timeline.css"> <!-- Reutilizamos los estilos de timeline -->
</head>
<body>
    <header>
        <div class="navbar">
            <h1>Timeline de @<?php echo $nombre_usuario_buscado; ?></h1>
            <a href="timeline.php" class="btn">Volver a mi Timeline</a>
            <a href="logout.php" class="btn">Cerrar sesión</a>
        </div>
    </header>

    <div class="subheader">
        <div class="profile-info">
            <?php if ($foto_perfil_base64_buscado): ?>
                <div class="profile-pic-container">
                    <img src="data:image/jpeg;base64,<?php echo $foto_perfil_base64_buscado; ?>" alt="Foto de Perfil" class="profile-pic">
                </div>
            <?php else: ?>
                <div class="profile-pic-container">
                    <p>Foto de perfil no disponible.</p>
                </div>
            <?php endif; ?>
            <h2 class="username">@<?php echo $nombre_usuario_buscado; ?></h2>
        </div>
    </div>

    <div class="timeline-container">
        <div class="posts">
            <?php while ($publicacion = oci_fetch_assoc($publicaciones_stmt)): ?>
                <div class="post">
                    <div class="post-header">
                        <span class="username">@<?php echo $nombre_usuario_buscado; ?></span>
                        <span class="post-date"><?php echo date('d M Y, H:i', strtotime($publicacion['FECHA_PUBLICACION'])); ?></span>
                    </div>
                    <p class="post-content"><?php echo htmlspecialchars($publicacion['CONTENIDO'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($publicacion['IMAGEN_PUBLICACION']): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($publicacion['IMAGEN_PUBLICACION']); ?>" alt="Imagen de la Publicación" class="post-image">
                    <?php endif; ?>
                    <?php if ($publicacion['VIDEO_PUBLICACION']): ?>
                        <video controls class="post-video">
                            <source src="data:video/mp4;base64,<?php echo base64_encode($publicacion['VIDEO_PUBLICACION']); ?>" type="video/mp4">
                            Tu navegador no soporta la reproducción de video.
                        </video>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>

<?php
oci_free_statement($publicaciones_stmt);
oci_free_statement($stmt_user);
oci_close($conn);
?>

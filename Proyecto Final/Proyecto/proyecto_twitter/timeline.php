<?php
session_start();
$conn = include 'conexion.php'; // Incluye el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener la información del usuario desde la sesión
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nombre_usuario, foto_perfil FROM Usuarios WHERE usuario_id = :usuario_id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':usuario_id', $usuario_id);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);

if ($row) {
    $nombre_usuario = $row['NOMBRE_USUARIO'];
    $foto_perfil_lob = $row['FOTO_PERFIL'];

    if (!is_null($foto_perfil_lob) && $foto_perfil_lob->size() > 0) {
        $foto_perfil_lob->rewind();
        $foto_perfil = $foto_perfil_lob->read($foto_perfil_lob->size());
        $foto_perfil_base64 = base64_encode($foto_perfil);
    } else {
        $foto_perfil_base64 = null;
    }
} else {
    echo "Error: No se encontró información del usuario.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline - Mi Red Social</title>
    <link rel="stylesheet" href="css/timeline.css"> <!-- Enlace al archivo CSS -->
</head>
<body>
    <header>
        <div class="navbar">
            <h1>Mi Red Social</h1>
            <form action="user_timeline.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Buscar usuarios..." required>
                <button type="submit" class="btn">Buscar</button>
            </form>
            <a href="perfil.php" class="btn btn-blue">Cambiar Foto de Perfil</a>
            <a href="mi_historia.php" class="btn btn-yellow">Mi Historia</a>
            <a href="logout.php" class="btn btn-red">Cerrar sesión</a>
        </div>
    </header>

    <div class="subheader">
        <div class="profile-info">
            <?php if ($foto_perfil_base64): ?>
                <div class="profile-pic-container">
                    <img src="data:image/jpeg;base64,<?php echo $foto_perfil_base64; ?>" alt="Foto de Perfil" class="profile-pic">
                </div>
            <?php else: ?>
                <div class="profile-pic-container">
                    <p>Foto de perfil no disponible.</p>
                </div>
            <?php endif; ?>
            <h2 class="username">@<?php echo $nombre_usuario; ?></h2>
        </div>
    </div>

    <div class="timeline-container">
        <div class="new-post">
            <form action="publicar.php" method="POST" enctype="multipart/form-data">
                <textarea name="contenido" placeholder="¿Qué está pasando?" maxlength="280" required></textarea>

                <!-- Previsualización de imagen -->
                <label for="imagen_publicacion" class="custom-file-upload">Subir Imagen</label>
                <input type="file" id="imagen_publicacion" name="imagen_publicacion" accept="image/*" style="display:none;" onchange="previewImage(event)">
                <img id="image_preview" class="image-preview" style="display:none; margin-top: 10px; max-width: 100%; height: auto;">

                <!-- Previsualización de video -->
                <label for="video_publicacion" class="custom-file-upload">Subir Video</label>
                <input type="file" id="video_publicacion" name="video_publicacion" accept="video/*" style="display:none;" onchange="previewVideo(event)">
                <video id="video_preview" class="video-preview" style="display:none; margin-top: 10px; max-width: 100%; height: auto;" controls></video>

                <button type="submit">Publicar</button>
            </form>
        </div>
    </div>

    <!-- Scripts para previsualización de imagen y video -->
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('image_preview');
                output.src = reader.result;
                output.style.display = 'block';
                document.getElementById('video_preview').style.display = 'none'; // Ocultar la previsualización de video si existe
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function previewVideo(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('video_preview');
                output.src = reader.result;
                output.style.display = 'block';
                document.getElementById('image_preview').style.display = 'none'; // Ocultar la previsualización de imagen si existe
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>

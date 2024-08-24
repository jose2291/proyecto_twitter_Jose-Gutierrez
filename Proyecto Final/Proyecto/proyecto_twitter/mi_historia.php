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

// Obtener las publicaciones solo del usuario actual
$publicaciones_sql = "SELECT p.publicacion_id, p.contenido, p.fecha_publicacion, p.imagen_publicacion, p.video_publicacion, 
                      (SELECT COUNT(*) FROM Likes l WHERE l.publicacion_id = p.publicacion_id) AS total_likes,
                      (SELECT COUNT(*) FROM Comentarios c WHERE c.publicacion_id = p.publicacion_id) AS total_comentarios,
                      (SELECT COUNT(*) FROM Seguidores s WHERE s.usuario_id = p.usuario_id AND s.seguidor_usuario_id = :usuario_id) AS es_seguidor
                      FROM Publicaciones p
                      WHERE p.usuario_id = :usuario_id
                      ORDER BY p.fecha_publicacion DESC";
$publicaciones_stmt = oci_parse($conn, $publicaciones_sql);
oci_bind_by_name($publicaciones_stmt, ':usuario_id', $usuario_id);
oci_execute($publicaciones_stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Historia - Mi Red Social</title>
    <link rel="stylesheet" href="css/mi_historia.css"> <!-- Reutilizando estilos -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluye jQuery -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- Enlace para los iconos -->
</head>
<body>
    <header>
        <div class="navbar">
            <h1>Mi Red Social</h1>
            <a href="timeline.php" class="btn btn-blue">Volver a Timeline</a>
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
        <h2>Mis Publicaciones</h2>
        <div class="posts">
            <?php while ($publicacion = oci_fetch_assoc($publicaciones_stmt)): ?>
                <div class="post">
                    <div class="post-header">
                        <span class="username">@<?php echo $nombre_usuario; ?></span>
                        <span class="post-date"><?php echo date('d M Y, H:i', strtotime($publicacion['FECHA_PUBLICACION'])); ?></span>
                    </div>
                    <p class="post-content"><?php echo htmlspecialchars($publicacion['CONTENIDO'], ENT_QUOTES, 'UTF-8'); ?></p>
                    
                    <!-- Mostrar imagen si existe y el tamaño es mayor que 0 -->
                    <?php if (!is_null($publicacion['IMAGEN_PUBLICACION']) && $publicacion['IMAGEN_PUBLICACION']->size() > 0): ?>
                        <?php
                        $imagen_blob = $publicacion['IMAGEN_PUBLICACION'];
                        $imagen_blob->rewind();
                        $imagen_contenido = $imagen_blob->read($imagen_blob->size());
                        $imagen_base64 = base64_encode($imagen_contenido);
                        ?>
                        <img src="data:image/jpeg;base64,<?php echo $imagen_base64; ?>" alt="Imagen de la Publicación" class="post-image">
                    <?php else: ?>
                        <p>No hay imagen disponible</p>
                    <?php endif; ?>
                    
                    <!-- Mostrar video si existe y el tamaño es mayor que 0 -->
                    <?php if (!is_null($publicacion['VIDEO_PUBLICACION']) && $publicacion['VIDEO_PUBLICACION']->size() > 0): ?>
                        <video controls class="post-video">
                            <source src="data:video/mp4;base64,<?php echo base64_encode($publicacion['VIDEO_PUBLICACION']); ?>" type="video/mp4">
                            Tu navegador no soporta la reproducción de video.
                        </video>
                    <?php else: ?>
                        <p>No hay video disponible</p>
                    <?php endif; ?>
                    
                    <!-- Botones de Me gusta y Comentar con contadores -->
                    <div class="post-actions">
                        <form action="likes.php" method="POST" style="display:inline;">
                            <input type="hidden" name="publicacion_id" value="<?php echo $publicacion['PUBLICACION_ID']; ?>">
                            <button type="submit" class="btn-like">
                                <i class="fas fa-thumbs-up"></i> Me gusta (<?php echo $publicacion['TOTAL_LIKES']; ?>)
                            </button>
                        </form>
                        
                        <!-- Botón para abrir la sección de comentarios -->
                        <button class="btn-comment" onclick="toggleCommentSection(<?php echo $publicacion['PUBLICACION_ID']; ?>)">
                            <i class="fas fa-comment"></i> Comentar (<?php echo $publicacion['TOTAL_COMENTARIOS']; ?>)
                        </button>

                        <!-- Botón para seguir o dejar de seguir -->
                        <button class="btn-follow" id="follow-btn-<?php echo $publicacion['PUBLICACION_ID']; ?>" 
                                onclick="toggleFollow(<?php echo $publicacion['PUBLICACION_ID']; ?>, <?php echo $usuario_id; ?>)">
                            <?php echo $publicacion['ES_SEGUIDOR'] > 0 ? 'Seguido' : 'Seguir'; ?>
                        </button>
                    </div>

                    <!-- Sección de comentarios oculta -->
                    <div class="comment-section" id="comment-section-<?php echo $publicacion['PUBLICACION_ID']; ?>" style="display: none;">
                        <form action="comentarios.php" method="POST">
                            <input type="hidden" name="publicacion_id" value="<?php echo $publicacion['PUBLICACION_ID']; ?>">
                            <textarea name="comentario" placeholder="Escribe un comentario..." required></textarea>
                            <button type="submit" class="btn-submit-comment">Enviar</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function toggleCommentSection(postId) {
            var commentSection = document.getElementById('comment-section-' + postId);
            if (commentSection.style.display === 'none') {
                commentSection.style.display = 'block';
            } else {
                commentSection.style.display = 'none';
            }
        }

        function toggleFollow(publicacionId, userId) {
            $.ajax({
                url: 'seguir.php',
                type: 'POST',
                data: { usuario_id: userId, publicacion_id: publicacionId },
                success: function(response) {
                    var btn = document.getElementById('follow-btn-' + publicacionId);
                    if (response === 'seguido') {
                        btn.textContent = 'Seguido';
                        btn.classList.add('btn-red');
                        btn.classList.remove('btn-blue');
                    } else if (response === 'seguir') {
                        btn.textContent = 'Seguir';
                        btn.classList.add('btn-blue');
                        btn.classList.remove('btn-red');
                    }
                }
            });
        }
    </script>
</body>
</html>

<?php
oci_free_statement($publicaciones_stmt);
oci_close($conn);
?>

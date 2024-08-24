<?php
session_start();
$conn = include 'conexion.php'; // Incluye el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar que se haya enviado el formulario correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $contenido = htmlspecialchars($_POST['contenido'], ENT_QUOTES, 'UTF-8');

    // Inicializamos variables para los archivos multimedia
    $imagen = null;
    $video = null;

    // Procesar la imagen si se ha subido
    if (!empty($_FILES['imagen_publicacion']['tmp_name'])) {
        $imagen = file_get_contents($_FILES['imagen_publicacion']['tmp_name']);
    }

    // Procesar el video si se ha subido
    if (!empty($_FILES['video_publicacion']['tmp_name'])) {
        $video = file_get_contents($_FILES['video_publicacion']['tmp_name']);
    }

    // Consulta SQL para insertar la publicación sin los BLOBs primero
    $sql = "INSERT INTO Publicaciones (usuario_id, contenido, fecha_publicacion) 
            VALUES (:usuario_id, :contenido, SYSDATE) 
            RETURNING publicacion_id INTO :publicacion_id";
    
    $stmt = oci_parse($conn, $sql);

    // Vincular los parámetros de la consulta
    oci_bind_by_name($stmt, ':usuario_id', $usuario_id);
    oci_bind_by_name($stmt, ':contenido', $contenido);
    oci_bind_by_name($stmt, ':publicacion_id', $publicacion_id, -1, SQLT_INT);

    // Ejecutar la consulta para insertar la publicación
    if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        // Si hay una imagen, la añadimos después
        if ($imagen !== null) {
            $sql_imagen = "UPDATE Publicaciones SET imagen_publicacion = EMPTY_BLOB() 
                           WHERE publicacion_id = :publicacion_id 
                           RETURNING imagen_publicacion INTO :imagen_blob";
            $stmt_imagen = oci_parse($conn, $sql_imagen);
            $blob_imagen = oci_new_descriptor($conn, OCI_D_LOB);
            oci_bind_by_name($stmt_imagen, ':publicacion_id', $publicacion_id);
            oci_bind_by_name($stmt_imagen, ':imagen_blob', $blob_imagen, -1, OCI_B_BLOB);

            if (oci_execute($stmt_imagen, OCI_NO_AUTO_COMMIT)) {
                if ($blob_imagen->save($imagen)) {
                    oci_commit($conn); // Commit solo si la imagen se guarda correctamente
                } else {
                    oci_rollback($conn); // Rollback si hay algún problema
                    echo "Error al guardar la imagen.";
                }
            }

            $blob_imagen->free();
            oci_free_statement($stmt_imagen);
        }

        // Si hay un video, lo añadimos después
        if ($video !== null) {
            $sql_video = "UPDATE Publicaciones SET video_publicacion = EMPTY_BLOB() 
                          WHERE publicacion_id = :publicacion_id 
                          RETURNING video_publicacion INTO :video_blob";
            $stmt_video = oci_parse($conn, $sql_video);
            $blob_video = oci_new_descriptor($conn, OCI_D_LOB);
            oci_bind_by_name($stmt_video, ':publicacion_id', $publicacion_id);
            oci_bind_by_name($stmt_video, ':video_blob', $blob_video, -1, OCI_B_BLOB);

            if (oci_execute($stmt_video, OCI_NO_AUTO_COMMIT)) {
                if ($blob_video->save($video)) {
                    oci_commit($conn); // Commit solo si el video se guarda correctamente
                } else {
                    oci_rollback($conn); // Rollback si hay algún problema
                    echo "Error al guardar el video.";
                }
            }

            $blob_video->free();
            oci_free_statement($stmt_video);
        }

        // Redirigir de nuevo al timeline o mostrar un mensaje de éxito
        header("Location: mi_historia.php");
        exit();
    } else {
        $error = oci_error($stmt);
        echo "Error al publicar: " . $error['message'];
    }

    oci_free_statement($stmt);
    oci_close($conn);
} else {
    header("Location: timeline.php");
    exit();
}
?>

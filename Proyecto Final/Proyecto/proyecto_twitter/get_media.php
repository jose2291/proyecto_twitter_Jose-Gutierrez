<?php
$conn = include 'conexion.php'; // Conexión a la base de datos

// Verificar si se ha proporcionado una ID de publicación
if (isset($_GET['publicacion_id']) && isset($_GET['tipo'])) {
    $publicacion_id = $_GET['publicacion_id'];
    $tipo = $_GET['tipo']; // 'imagen' o 'video'

    // Consultar la base de datos para obtener el BLOB de la imagen o video
    if ($tipo === 'imagen') {
        $sql = "SELECT imagen_publicacion FROM Publicaciones WHERE publicacion_id = :publicacion_id";
    } elseif ($tipo === 'video') {
        $sql = "SELECT video_publicacion FROM Publicaciones WHERE publicacion_id = :publicacion_id";
    } else {
        echo "Tipo de archivo no válido";
        exit();
    }

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':publicacion_id', $publicacion_id);
    oci_execute($stmt);

    if ($row = oci_fetch_assoc($stmt)) {
        $blob = $row[strtoupper($tipo) . '_PUBLICACION'];
        if (!is_null($blob) && $blob->size() > 0) {
            // Configurar el tipo de contenido adecuado
            if ($tipo === 'imagen') {
                header("Content-Type: image/jpeg");
            } elseif ($tipo === 'video') {
                header("Content-Type: video/mp4");
            }

            // Enviar el contenido del BLOB
            $blob->rewind();
            echo $blob->read($blob->size());
        } else {
            echo "No hay $tipo disponible.";
        }
    } else {
        echo "No se encontró el medio.";
    }

    oci_free_statement($stmt);
} else {
    echo "Solicitud no válida.";
}

oci_close($conn);
?>

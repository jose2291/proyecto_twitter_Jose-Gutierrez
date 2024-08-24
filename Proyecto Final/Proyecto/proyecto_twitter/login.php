<?php
session_start();
$conn = include 'conexion.php'; // Incluye el archivo de conexión y asigna la conexión a $conn

$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = htmlspecialchars($_POST['nombre_usuario'], ENT_QUOTES, 'UTF-8');
    $contrasena = $_POST['contrasena']; // No se escapa aún porque se maneja como hash

    // Consulta para verificar si el nombre de usuario existe
    $sql = "SELECT usuario_id, contrasena FROM Usuarios WHERE nombre_usuario = :p_nombre_usuario";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':p_nombre_usuario', $nombre_usuario);

    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    if ($row) {
        // Verificar si la contraseña ingresada coincide con el hash almacenado
        if (password_verify($contrasena, $row['CONTRASENA'])) {
            // Almacenar la sesión del usuario
            $_SESSION['usuario_id'] = $row['USUARIO_ID'];
            $_SESSION['nombre_usuario'] = $nombre_usuario;

            // Redirigir a timeline.php
            header("Location: timeline.php");
            exit();
        } else {
            $mensaje_error = "Contraseña incorrecta. Inténtalo de nuevo.";
        }
    } else {
        $mensaje_error = "El nombre de usuario no existe.";
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mi Red Social</title>
    <link rel="stylesheet" href="css/login.css"> <!-- Enlace al archivo CSS -->
</head>
<body>
    <header>
        <div class="navbar">
            <h1>TWIITER</h1>
            <nav>
                <a href="index.php" class="btn">Página Principal</a>
                <a href="registro.php" class="btn">Registrarse</a>
            </nav>
        </div>
    </header>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <!-- Mostrar mensaje de error si las credenciales son incorrectas -->
        <?php if (!empty($mensaje_error)): ?>
            <div class="mensaje-error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="text" name="nombre_usuario" placeholder="Nombre de Usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>
</body>
</html>

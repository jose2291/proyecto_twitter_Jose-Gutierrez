<?php
$conn = include 'conexion.php'; // Incluye el archivo de conexión y asigna la conexión a $conn

// Verificar si la conexión es válida
if (!$conn) {
    die("Error de conexión: no se pudo conectar a la base de datos.");
}

$mensaje_exito = "";
$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Procesamiento del formulario de registro
    $nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
    $apellido = htmlspecialchars($_POST['apellido'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $telefono = htmlspecialchars($_POST['telefono'], ENT_QUOTES, 'UTF-8');
    $sexo = htmlspecialchars($_POST['sexo'], ENT_QUOTES, 'UTF-8');
    $nombre_usuario = htmlspecialchars($_POST['nombre_usuario'], ENT_QUOTES, 'UTF-8');
    $contrasena = password_hash($_POST['contraseña'], PASSWORD_BCRYPT); 
    $ubicacion = htmlspecialchars($_POST['ubicacion'], ENT_QUOTES, 'UTF-8');

    // Verificar si el email o nombre de usuario ya existen
    $check_sql = "SELECT COUNT(*) FROM Usuarios WHERE email = :p_email OR nombre_usuario = :p_nombre_usuario";
    $check_stmt = oci_parse($conn, $check_sql);

    oci_bind_by_name($check_stmt, ':p_email', $email);
    oci_bind_by_name($check_stmt, ':p_nombre_usuario', $nombre_usuario);

    oci_execute($check_stmt);
    $row = oci_fetch_assoc($check_stmt);

    if ($row['COUNT(*)'] > 0) {
        $mensaje_error = "El correo electrónico o el nombre de usuario ya están registrados.";
    } else {
        // Consulta SQL sin la imagen de perfil
        $sql = "INSERT INTO Usuarios (nombre, apellido, email, telefono, sexo, nombre_usuario, contrasena, ubicacion)
                VALUES (:p_nombre, :p_apellido, :p_email, :p_telefono, :p_sexo, :p_nombre_usuario, :p_contrasena, :p_ubicacion)";

        // Preparar la consulta
        $stmt = oci_parse($conn, $sql);

        // Asignar los valores a los parámetros
        oci_bind_by_name($stmt, ':p_nombre', $nombre);
        oci_bind_by_name($stmt, ':p_apellido', $apellido);
        oci_bind_by_name($stmt, ':p_email', $email);
        oci_bind_by_name($stmt, ':p_telefono', $telefono);
        oci_bind_by_name($stmt, ':p_sexo', $sexo);
        oci_bind_by_name($stmt, ':p_nombre_usuario', $nombre_usuario);
        oci_bind_by_name($stmt, ':p_contrasena', $contrasena);
        oci_bind_by_name($stmt, ':p_ubicacion', $ubicacion);

        // Ejecutar la consulta
        if (oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            // Confirmar la transacción
            oci_commit($conn);
            $mensaje_exito = "Registro exitoso. Ahora puedes <a href='login.php'>iniciar sesión</a>.";
        } else {
            $e = oci_error($stmt);
            $mensaje_error = "Error al registrar el usuario: " . $e['message'];
        }

        // Liberar recursos
        oci_free_statement($stmt);
    }

    oci_free_statement($check_stmt);
    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Mi Red Social</title>
    <link rel="stylesheet" href="css/registro.css"> <!-- Enlace al archivo CSS -->
    <style>
        .mensaje-exito {
            display: none;
            font-size: 18px;
            color: #fff;
            background-color: #28a745;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
            animation: fadeInOut 6s ease-in-out forwards;
        }
        .mensaje-error {
            display: none;
            font-size: 18px;
            color: #fff;
            background-color: #dc3545;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
            animation: fadeInOut 6s ease-in-out forwards;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <h1>Mi Red Social</h1>
            <nav>
                <a href="index.php" class="btn">Página Principal</a>
                <a href="login.php" class="btn">Iniciar Sesión</a>
            </nav>
        </div>
    </header>

    <div class="registro-container">
        <h2>Registrarse</h2>

        <!-- Mostrar el mensaje dinámico si el registro fue exitoso o fallido -->
        <?php if (!empty($mensaje_exito)): ?>
            <div class="mensaje-exito"><?php echo $mensaje_exito; ?></div>
        <?php elseif (!empty($mensaje_error)): ?>
            <div class="mensaje-error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <form action="registro.php" method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="email" name="email" placeholder="Correo Electrónico" required>
            <input type="text" name="telefono" placeholder="Teléfono">
            <select name="sexo">
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
            </select>
            <input type="text" name="nombre_usuario" placeholder="Nombre de Usuario" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <input type="text" name="ubicacion" placeholder="Ubicación">
            <button type="submit">Registrarse</button>
        </form>
    </div>

    <script>
        // Mostrar el mensaje de éxito o error con animación
        document.addEventListener("DOMContentLoaded", function() {
            const mensajeExito = document.querySelector(".mensaje-exito");
            const mensajeError = document.querySelector(".mensaje-error");
            if (mensajeExito) {
                mensajeExito.style.display = "block";
            }
            if (mensajeError) {
                mensajeError.style.display = "block";
            }
        });
    </script>
</body>
</html>

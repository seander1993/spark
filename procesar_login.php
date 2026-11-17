<?php
// Iniciamos la sesión para poder guardar el estado del usuario
session_start();

// Incluimos la conexión a la base de datos
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Buscamos al usuario por su email usando una consulta preparada (seguridad contra SQL Injection)
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario existe y la contraseña coincide
        if ($usuario && password_verify($password, $usuario['password'])) {
            
            // Guardamos los datos clave en las variables de sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigimos según el rol del usuario
            if ($usuario['rol'] === 'admin') {
                header('Location: admin_dashboard.php'); // Panel de administración (RF01 y RF02)
            } else {
                header('Location: index.php'); // Tienda para el cliente (RF03)
            }
            exit();

        } else {
            echo "<p style='color:red;'>Correo o contraseña incorrectos. <a href='login.php'>Volver a intentar</a></p>";
        }

    } catch (PDOException $e) {
        echo "Error en el servidor: " . $e->getMessage();
    }
}
?>
<?php
// Iniciamos la sesión para comprobar el estado
session_start();

// Validamos que el usuario esté logueado y sea estrictamente administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Spark</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .welcome { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .menu { display: flex; gap: 15px; margin-top: 20px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #bd2130; }
    </style>
</head>
<body>

<div class="container">
    <h1>Panel de Control - Administrador</h1>
    <div class="welcome">
        <p>¡Bienvenido de nuevo, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>!</p>
        <p>Desde aquí puedes gestionar los aspectos clave de la tienda Spark.</p>
    </div>

    <h3>Gestión del Sistema</h3>
    <div class="menu">
        <a href="admin_usuarios.php" class="btn">Gestionar Usuarios (RF01)</a>
        <a href="admin_productos.php" class="btn">Gestionar Artículos (RF02)</a>
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</div>

</body>
</html>
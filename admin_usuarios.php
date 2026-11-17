<?php
// Iniciamos la sesión y comprobamos que sea administrador
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require 'conexion.php';

// 1. Lógica para eliminar usuario
if (isset($_GET['eliminar'])) {
    $id_usuario = $_GET['eliminar'];
    // Evitamos que el admin se borre a sí mismo por accidente
    if ($id_usuario != $_SESSION['id_usuario']) {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $stmt->execute(['id' => $id_usuario]);
    }
    header('Location: admin_usuarios.php');
    exit();
}

// 2. Lógica para crear un nuevo usuario desde el panel
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    try {
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)");
        $stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password,
            'rol' => $rol
        ]);
        $mensaje = "¡Usuario creado correctamente!";
    } catch (PDOException $e) {
        $error = "Error al crear el usuario (el email ya podría existir).";
    }
}

// 3. Obtenemos el listado completo de usuarios
$stmt = $conexion->query("SELECT * FROM usuarios");
$lista_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios (RF01) - Spark</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1, h3 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; padding: 5px 10px; text-decoration: none; color: white; border-radius: 3px; }
        .btn-danger:hover { background: #bd2130; }
        .btn-back { background: #6c757d; text-decoration: none; display: inline-block; padding: 8px 15px; color: white; border-radius: 4px; margin-bottom: 20px; }
        .btn-back:hover { background: #5a6268; }
        .alert-success { color: green; margin-bottom: 15px; }
        .alert-error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin_dashboard.php" class="btn-back">&larr; Volver al Panel</a>
    <h1>Gestión de Usuarios (RF01)</h1>

    <?php if (isset($mensaje)) echo "<p class='alert-success'>$mensaje</p>"; ?>
    <?php if (isset($error)) echo "<p class='alert-error'>$error</p>"; ?>

    <!-- Formulario para crear usuario -->
    <h3>Añadir Nuevo Usuario</h3>
    <form action="admin_usuarios.php" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="rol">Rol del usuario:</label>
            <select id="rol" name="rol">
                <option value="cliente">Cliente</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <button type="submit" name="crear" class="btn">Crear Usuario</button>
    </form>

    <hr style="margin: 30px 0;">

    <!-- Listado de usuarios -->
    <h3>Usuarios Registrados en el Sistema</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista_usuarios as $u): ?>
            <tr>
                <td><?php echo $u['id_usuario']; ?></td>
                <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><strong><?php echo $u['rol']; ?></strong></td>
                <td>
                    <?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                        <a href="admin_usuarios.php?eliminar=<?php echo $u['id_usuario']; ?>" class="btn-danger" onclick="return confirm('¿Seguro que quieres borrar este usuario?');">Eliminar</a>
                    <?php else: ?>
                        <small style="color: #888;">(Usuario actual)</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
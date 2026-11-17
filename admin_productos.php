<?php
// Iniciamos la sesión y comprobamos que sea estrictamente administrador
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require 'conexion.php';

// 1. Lógica para eliminar un producto del catálogo
if (isset($_GET['eliminar'])) {
    $id_producto = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM productos WHERE id_producto = :id");
    $stmt->execute(['id' => $id_producto]);
    header('Location: admin_productos.php');
    exit();
}

// 2. Lógica para añadir un nuevo artículo/zapatilla
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_producto'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $imagen = trim($_POST['imagen']); // URL o nombre del archivo de imagen

    try {
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, imagen) VALUES (:nombre, :descripcion, :precio, :stock, :imagen)");
        $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'imagen' => $imagen
        ]);
        $mensaje = "¡Zapatilla añadida al catálogo con éxito!";
    } catch (PDOException $e) {
        $error = "Error al registrar el producto: " . $e->getMessage();
    }
}

// 3. Obtenemos el listado completo de productos de la base de datos
$stmt = $conexion->query("SELECT * FROM productos");
$lista_productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Artículos (RF02) - Spark</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1, h3 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
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
    <h1>Gestión de Artículos (RF02)</h1>

    <?php if (isset($mensaje)) echo "<p class='alert-success'>$mensaje</p>"; ?>
    <?php if (isset($error)) echo "<p class='alert-error'>$error</p>"; ?>

    <!-- Formulario para añadir nueva zapatilla -->
    <h3>Añadir Nueva Zapatilla al Catálogo</h3>
    <form action="admin_productos.php" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre del modelo:</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej. Spark Air Runner">
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Descripción de la zapatilla..."></textarea>
        </div>
        <div class="form-group">
            <label for="precio">Precio (€):</label>
            <input type="number" step="0.01" id="precio" name="precio" required placeholder="89.99">
        </div>
        <div class="form-group">
            <label for="stock">Stock disponible:</label>
            <input type="number" id="stock" name="stock" required placeholder="25">
        </div>
        <div class="form-group">
            <label for="imagen">URL de la imagen:</label>
            <input type="text" id="imagen" name="imagen" placeholder="ruta_imagen.jpg">
        </div>
        <button type="submit" name="crear_producto" class="btn">Guardar Zapatilla</button>
    </form>

    <hr style="margin: 30px 0;">

    <!-- Listado del catálogo actual -->
    <h3>Catálogo Actual de Spark</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($lista_productos) > 0): ?>
                <?php foreach ($lista_productos as $p): ?>
                <tr>
                    <td><?php echo $p['id_producto']; ?></td>
                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                    <td><?php echo number_format($p['precio'], 2); ?> €</td>
                    <td><?php echo $p['stock']; ?> uds.</td>
                    <td>
                        <a href="admin_productos.php?eliminar=<?php echo $p['id_producto']; ?>" class="btn-danger" onclick="return confirm('¿Seguro que quieres borrar este artículo del catálogo?');">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #777;">No hay zapatillas registradas todavía.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
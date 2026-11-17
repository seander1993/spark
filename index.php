<?php
// Iniciamos sesión para gestionar el carrito y el estado del usuario
session_start();
require 'conexion.php';

// Si se pulsa el botón de añadir al carrito
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    // Si el carrito no existe en la sesión, lo creamos
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Si el producto ya está en el carrito, sumamos la cantidad; si no, lo añadimos
    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto] += $cantidad;
    } else {
        $_SESSION['carrito'][$id_producto] = $cantidad;
    }
    
    header('Location: index.php');
    exit();
}

// Obtenemos todas las zapatillas del catálogo
$stmt = $conexion->query("SELECT * FROM productos WHERE stock > 0");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contamos cuántos productos hay en el carrito para la insignia superior
$total_carrito = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Spark - Tienda de Zapatillas</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        header { background: #333; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; margin-left: 15px; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 8px; box-shadow: 0px 0px 8px rgba(0,0,0,0.1); padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .card h3 { margin: 0 0 10px 0; color: #333; }
        .price { font-size: 18px; color: #28a745; font-weight: bold; margin-bottom: 10px; }
        .btn { padding: 8px 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-align: center; text-decoration: none; }
        .btn:hover { background: #0056b3; }
        .badge { background: #dc3545; color: white; padding: 2px 6px; border-radius: 50%; font-size: 12px; }
    </style>
</head>
<body>

<header>
    <h1>Spark Store</h1>
    <div>
        <?php if (isset($_SESSION['nombre'])): ?>
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="admin_dashboard.php">Panel Admin</a>
            <?php endif; ?>
            <a href="carrito.php">🛒 Carrito <span class="badge"><?php echo $total_carrito; ?></span></a>
            <a href="logout.php">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar Sesión</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <h2>Catálogo de Zapatillas</h2>
    <div class="grid">
        <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $p): ?>
                <div class="card">
                    <div>
                        <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
                        <p style="color: #666; font-size: 14px;"><?php echo htmlspecialchars($p['descripcion']); ?></p>
                        <div class="price"><?php echo number_format($p['precio'], 2); ?> €</div>
                        <p style="font-size: 12px; color: #888;">Stock disponible: <?php echo $p['stock']; ?> uds.</p>
                    </div>
                    <form action="index.php" method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="accion" value="agregar">
                        <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn" style="width: 100%;">Añadir al Carrito</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay zapatillas disponibles en este momento.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
session_start();
require 'conexion.php';

// Vaciar o modificar carrito si se solicita
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header('Location: carrito.php');
    exit();
}

// Procesar la compra final (Checkout)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_compra'])) {
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: login.php');
        exit();
    }

    if (!empty($_SESSION['carrito'])) {
        try {
            // Iniciamos transacción para asegurar la integridad de los datos
            $conexion->beginTransaction();

            $id_usuario = $_SESSION['id_usuario'];
            $total_pedido = 0;

            // Primero calculamos el total sumando los precios actuales de la BD
            foreach ($_SESSION['carrito'] as $id_prod => $cant) {
                $stmt = $conexion->prepare("SELECT precio FROM productos WHERE id_producto = ?");
                $stmt->execute([$id_prod]);
                $prod = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($prod) {
                    $total_pedido += $prod['precio'] * $cant;
                }
            }

            // Insertamos el pedido principal
            $stmt_pedido = $conexion->prepare("INSERT INTO pedidos (id_usuario, total, estado) VALUES (?, ?, 'Completado')");
            $stmt_pedido->execute([$id_usuario, $total_pedido]);
            $id_pedido_nuevo = $conexion->lastInsertId();

            // Insertamos los detalles de cada producto y descontamos el stock
            foreach ($_SESSION['carrito'] as $id_prod => $cant) {
                $stmt_precio = $conexion->prepare("SELECT precio FROM productos WHERE id_producto = ?");
                $stmt_precio->execute([$id_prod]);
                $precio_unitario = $stmt_precio->fetchColumn();

                $stmt_detalle = $conexion->prepare("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                $stmt_detalle->execute([$id_pedido_nuevo, $id_prod, $cant, $precio_unitario]);

                // Actualizamos el stock en la tabla productos
                $stmt_stock = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
                $stmt_stock->execute([$cant, $id_prod]);
            }

            // Confirmamos la transacción
            $conexion->commit();

            // Vaciamos el carrito tras la compra exitosa
            unset($_SESSION['carrito']);
            $compra_exitosa = true;

        } catch (Exception $e) {
            $conexion->rollBack();
            $error_compra = "Hubo un error al procesar tu compra: " . $e->getMessage();
        }
    }
}

// Recoger los datos de los productos que están en el carrito para mostrarlos en pantalla
$articulos_carrito = [];
$total_precio = 0;
if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id_prod => $cant) {
        $stmt = $conexion->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_prod]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($producto) {
            $producto['cantidad'] = $cant;
            $producto['subtotal'] = $producto['precio'] * $cant;
            $total_precio += $producto['subtotal'];
            $articulos_carrito[] = $producto;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras (RF03) - Spark</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; padding: 8px 15px; }
        .btn-danger:hover { background: #bd2130; }
        .btn-back { background: #6c757d; margin-bottom: 20px; }
        .total-box { text-align: right; font-size: 20px; margin-top: 20px; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn btn-back">&larr; Seguir Comprando</a>
    <h1>Tu Carrito de Compras (RF03)</h1>

    <?php if (isset($compra_exitosa)): ?>
        <div class="alert-success">
            <h3>¡Compra realizada con éxito!</h3>
            <p>Tu pedido se ha registrado correctamente en el sistema y se ha actualizado el stock. ¡Gracias por confiar en Spark!</p>
        </div>
        <a href="index.php" class="btn">Volver al inicio</a>
    <?php elseif (isset($error_compra)): ?>
        <p style="color: red;"><?php echo $error_compra; ?></p>
    <?php elseif (empty($articulos_carrito)): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Precio Unidad</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articulos_carrito as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td><?php echo number_format($item['precio'], 2); ?> €</td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td><?php echo number_format($item['subtotal'], 2); ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-box">
            Total a Pagar: <?php echo number_format($total_precio, 2); ?> €
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: space-between;">
            <a href="carrito.php?vaciar=1" class="btn btn-danger">Vaciar Carrito</a>
            
            <form action="carrito.php" method="POST">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <button type="submit" name="confirmar_compra" class="btn">Confirmar Compra</button>
                <?php else: ?>
                    <p style="color: #d9534f; margin: 0; line-height: 38px;">Debes <a href="login.php">iniciar sesión</a> para confirmar la compra.</p>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
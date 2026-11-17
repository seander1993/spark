<?php
// Incluimos la conexión a la base de datos
require 'conexion.php';

try {
    // Generamos los hashes seguros con las nuevas contraseñas solicitadas
    $passAdmin = password_hash('Admin.123', PASSWORD_DEFAULT);
    $passCliente = password_hash('Cliente.123', PASSWORD_DEFAULT);

    // Insertar el usuario Administrador
    $sqlAdmin = "INSERT INTO usuarios (nombre, email, password, rol) 
                 VALUES ('Admin Spark', 'admin@spark.com', :pass, 'admin')";
    $stmt1 = $conexion->prepare($sqlAdmin);
    $stmt1->execute(['pass' => $passAdmin]);

    // Insertar el usuario Cliente
    $sqlCliente = "INSERT INTO usuarios (nombre, email, password, rol) 
                   VALUES ('Cliente Test', 'cliente@spark.com', :pass, 'cliente')";
    $stmt2 = $conexion->prepare($sqlCliente);
    $stmt2->execute(['pass' => $passCliente]);

    echo "<h3>¡Usuarios creados con éxito para Spark!</h3>";
    echo "<p>Ya puedes hacer el login con los siguientes datos:</p>";
    echo "<ul>";
    echo "<li><b>Administrador:</b> admin@spark.com / Admin.123</li>";
    echo "<li><b>Cliente:</b> cliente@spark.com / Cliente.123</li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "Error al crear los usuarios: " . $e->getMessage() . "<br>";
    echo "<small>Consejo: Asegúrate de que la tabla 'usuarios' esté vacía para evitar duplicados en el email.</small>";
}
?>
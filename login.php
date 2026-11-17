<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Spark</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); width: 300px; }
        .login-box h2 { margin-bottom: 20px; text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { width: 100%; background: #007bff; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Spark - Acceso</h2>
    <form action="procesar_login.php" method="POST">
        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Entrar</button>
    </form>
</div>

</body>
</html>
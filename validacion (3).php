<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Si ya está logueado, redirigir a la página principal.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['mail']) || !isset($_POST['password'])) {
        $error = "Por favor complete todos los campos requeridos.";
    } else {
        $host = 'siatcae.com';
        $db   = 'siatcaec_hackathon'; // Cambia esto por tu base de datos
        $user = 'siatcaec_extra';       // Cambia esto por tu usuario
        $pass = '9mJFEs4GbpzGvgfewjsU';    // Cambia esto por tu contraseña


        try {
            // Conexión PDO
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Sanitizar y validar entrada
            $mail = filter_var($_POST['mail'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            if (!filter_var($mail, FILTER_VALIDATE_EMAIL) || empty($password)) {
                throw new Exception("Por favor ingrese un correo válido y contraseña.");
            }

            $user_found = null;
            $user_type = '';
            
            // Buscar en tabla usuarios
            $stmt = $pdo->prepare("SELECT id, nombre, apellido, correo, password FROM usuarios WHERE correo = :mail");
            $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user_found = $user;
                $user_type = 'usuario';
            } else {
                // Si no se encuentra en usuarios, buscar en taxistas
                $stmt = $pdo->prepare("SELECT id, nombre, apellido, placa FROM taxistas WHERE correo = :mail");
                $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
                $stmt->execute();
                
                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $user_found = $user;
                    $user_type = 'taxista';
                }
            }
            
            if ($user_found) {
                if ($user_type === 'usuario') {
                    if (!isset($user_found['password']) || $user_found['password'] === null) {
                        throw new Exception("El usuario no tiene una contraseña configurada.");
                    }

                    // Verificar contraseña
                    if (password_verify($password, $user_found['password'])) {
                        // Guardar sesión
                        $_SESSION['user_id'] = $user_found['id'];
                        $_SESSION['correo'] = $mail;
                        $_SESSION['nombre'] = $user_found['nombre'];
                        $_SESSION['apellido'] = $user_found['apellido'];
                        $_SESSION['user_type'] = $user_type;

                        $to = $mail;
                        $subject = "Inicio de sesión exitoso - TaxiPay";
                        $message = "Hola " . $user_found['nombre'] . ",\n\n";
                        $message .= "Has iniciado sesión exitosamente en TaxiPay.\n\n";
                        $message .= "Detalles del acceso:\n";
                        $message .= "Email: " . $mail . "\n";
                        $message .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
                        $message .= "Tipo de usuario: " . ucfirst($user_type) . "\n";
                        $message .= "\nSi no reconoces esta actividad, por favor contacta al administrador inmediatamente.\n";

                        $headers = "From: noreply@taxipay.com\r\n";
                        $headers .= "Reply-To: noreply@taxipay.com\r\n";
                        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                        $headers .= "MIME-Version: 1.0\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                        mail($to, $subject, $message, $headers);

                        header('Location: dashboard.php');
                        exit;
                    } else {
                        throw new Exception("Credenciales incorrectas.");
                    }
                } else {
                    // Para taxistas, crear sesión directamente (sin verificar contraseña por ahora)
                    $_SESSION['user_id'] = $user_found['id'];
                    $_SESSION['nombre'] = $user_found['nombre'];
                    $_SESSION['apellido'] = $user_found['apellido'];
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['placa'] = $user_found['placa'];

                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                throw new Exception("Credenciales incorrectas.");
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Login - TaxiPay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-primary { background-color: #0c1d2c; }
        .bg-secondary { background-color: #42aab1; }
        .text-accent2 { color: #1c839f; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-primary">Error de Acceso</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="index.html" class="bg-secondary hover:bg-accent2 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                    Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
session_start();
require_once 'config.php';

if (!isset($pdo) || $pdo === null) {
    die("Error: No se pudo establecer conexión con la base de datos. Verifica tu archivo config.php");
}

function curpExists($curp) {
    global $pdo;
    try {
        // Check in usuarios table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE curp = ?");
        $stmt->execute([$curp]);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }
        
        // Check in taxistas table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM taxistas WHERE curp = ?");
        $stmt->execute([$curp]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking CURP: " . $e->getMessage());
        return false;
    }
}

function registerUser($data, $is_taxista = false) {
    global $pdo;
    try {
        if ($is_taxista) {
            // Insert into taxistas table
            $stmt = $pdo->prepare("INSERT INTO taxistas (nombre, apellido, curp, direccion, codigo_postal, wallet, edad, placa) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['nombre'],
                $data['apellido'], 
                $data['curp'],
                $data['direccion'],
                $data['codigo_postal'],
                $data['wallet'],
                $data['edad'],
                $data['placa']
            ]);
        } else {
            // Insert into usuarios table
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, curp, direccion, codigo_postal, wallet, edad, phone, password, correo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['nombre'],
                $data['apellido'], 
                $data['curp'],
                $data['direccion'],
                $data['codigo_postal'],
                $data['wallet'],
                $data['edad'],
                $data['phone'],
                $data['password'],
                $data['correo']
            ]);
        }
    } catch (PDOException $e) {
        error_log("Error registering user: " . $e->getMessage());
        return false;
    }
}

function extractWalletId($wallet_url) {
    if (empty($wallet_url)) {
        return null;
    }
    
    // Extract the last part after the last slash
    $parts = explode('/', trim($wallet_url));
    $wallet_id = end($parts);
    
    // Validate that it's not empty and contains valid characters
    if (!empty($wallet_id) && preg_match('/^[a-zA-Z0-9_-]+$/', $wallet_id)) {
        return $wallet_id;
    }
    
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $curp = $_POST['curp'];
    $direccion = $_POST['direccion'];
    $codigo_postal = $_POST['codigo_postal'];
    $wallet_input = $_POST['wallet'] ?? '';
    $wallet = extractWalletId($wallet_input);
    $edad = $_POST['edad'];
    $user_type = $_POST['user_type'];
    
    if ($user_type === 'usuario') {
        $phone = $_POST['phone'];
        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden";
        } elseif (strlen($password) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres";
        }
    } else {
        $placa = $_POST['placa'];
    }
    
    if (!isset($error)) {
        if (curpExists($curp)) {
            $error = "El CURP ya está registrado en el sistema";
        } elseif (!empty($wallet_input) && $wallet === null) {
            $error = "La URL de la wallet no es válida. Debe ser del formato: https://ilp.interledger-test.dev/testeduardo_1";
        } else {
            $data = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'curp' => $curp,
                'direccion' => $direccion,
                'codigo_postal' => $codigo_postal,
                'wallet' => $wallet,
                'edad' => $edad
            ];
            
            if ($user_type === 'usuario') {
                $data['phone'] = $phone;
                $data['correo'] = $correo;
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $data['placa'] = $placa;
            }
            
            if (registerUser($data, $user_type == 'taxista')) {
                $_SESSION['message'] = "Cuenta creada correctamente.";
                $_SESSION['message_type'] = 'success';
                header("Location: index.html");
                exit();
            } else {
                $error = "Error al registrar el usuario";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - TaxiService</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0c1d2c 0%, #0c4f6c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(12, 29, 44, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #42aab1, #217277, #1c839f, #3e947c);
        }

        .auth-title {
            color: #0c1d2c;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }

        .auth-subtitle {
            color: #5c8888;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .user-type-selector {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8fafb;
            border-radius: 15px;
            border: 2px solid #e1e8ed;
        }

        .user-type-selector input[type="radio"] {
            display: none;
        }

        .user-type-selector label {
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid #e1e8ed;
            background: white;
            color: #5c8888;
        }

        .user-type-selector input[type="radio"]:checked + label {
            background: #42aab1;
            color: white;
            border-color: #42aab1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 170, 177, 0.3);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0c1d2c;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-control:focus {
            outline: none;
            border-color: #42aab1;
            background: white;
            box-shadow: 0 0 0 3px rgba(66, 170, 177, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #42aab1, #217277);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            background: linear-gradient(135deg, #217277, #1c839f);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(66, 170, 177, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-error {
            background: #fee;
            color: #c53030;
            border: 2px solid #fed7d7;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e1e8ed;
            color: #5c8888;
        }

        .login-link a {
            color: #42aab1;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #217277;
        }

        .password-strength {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #5c8888;
        }

        @media (max-width: 600px) {
            .auth-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .auth-title {
                font-size: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .user-type-selector {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2 class="auth-title">Crear Cuenta</h2>
        <p class="auth-subtitle">Únete a TaxiService y comienza tu viaje</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="user-type-selector">
                <input type="radio" id="user_type_usuario" name="user_type" value="usuario" checked>
                <label for="user_type_usuario">👤 Usuario</label>
                
                <input type="radio" id="user_type_taxista" name="user_type" value="taxista">
                <label for="user_type_taxista">🚕 Taxista</label>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="curp">CURP</label>
                <input type="text" class="form-control" id="curp" name="curp" required maxlength="18">
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="codigo_postal">Código Postal</label>
                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required maxlength="5">
                </div>
                <div class="form-group">
                    <label for="edad">Edad</label>
                    <input type="number" class="form-control" id="edad" name="edad" min="18" max="100" required>
                </div>
            </div>
            
            <div class="form-group">
                <!-- Updated wallet field to be optional and accept URL -->
                <label for="wallet">Wallet Interledger (opcional)</label>
                <input type="text" class="form-control" id="wallet" name="wallet" 
                       placeholder="https://ilp.interledger-test.dev/testeduardo_1">
                <div class="password-strength">Opcional: URL completa de tu wallet Interledger</div>
            </div>
            
            <!-- Added conditional fields that show/hide based on user type -->
            <div id="usuario-fields">
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo">
                </div>
                
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="tel" class="form-control" id="phone" name="phone" maxlength="15">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6">
                    <div class="password-strength">Mínimo 6 caracteres</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
            </div>
            
            <div id="taxista-fields" style="display: none;">
                <div class="form-group">
                    <label for="placa">Placa del Vehículo</label>
                    <input type="text" class="form-control" id="placa" name="placa" maxlength="15">
                </div>
            </div>
            
            <button type="submit" class="btn">Crear Cuenta</button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="index.html">Inicia sesión aquí</a>
        </div>
    </div>

    <script>
        const usuarioRadio = document.getElementById('user_type_usuario');
        const taxistaRadio = document.getElementById('user_type_taxista');
        const usuarioFields = document.getElementById('usuario-fields');
        const taxistaFields = document.getElementById('taxista-fields');

        function toggleFields() {
            if (usuarioRadio.checked) {
                usuarioFields.style.display = 'block';
                taxistaFields.style.display = 'none';
                // Make usuario fields required
                document.getElementById('correo').required = true;
                document.getElementById('phone').required = true;
                document.getElementById('password').required = true;
                document.getElementById('confirm_password').required = true;
                document.getElementById('placa').required = false;
            } else {
                usuarioFields.style.display = 'none';
                taxistaFields.style.display = 'block';
                // Make taxista fields required
                document.getElementById('placa').required = true;
                document.getElementById('correo').required = false;
                document.getElementById('phone').required = false;
                document.getElementById('password').required = false;
                document.getElementById('confirm_password').required = false;
            }
        }

        usuarioRadio.addEventListener('change', toggleFields);
        taxistaRadio.addEventListener('change', toggleFields);

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // CURP format validation
        document.getElementById('curp').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Postal code validation
        document.getElementById('codigo_postal').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });

        document.getElementById('wallet').addEventListener('input', function() {
            const walletUrl = this.value.trim();
            if (walletUrl && !walletUrl.includes('ilp.interledger-test.dev/')) {
                this.setCustomValidity('Debe ser una URL válida de Interledger: https://ilp.interledger-test.dev/testeduardo_1');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>

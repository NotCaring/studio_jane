<?php
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['client_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_POST) {
    // Validate required fields
    $required_fields = ['name', 'email', 'phone', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . str_replace('_', ' ', $field) . " es obligatorio";
        }
    }
    
    // Validate email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    }
    
    // Validate password
    if (!empty($_POST['password']) && strlen($_POST['password']) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    // Confirm password
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = "Las contraseñas no coinciden";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            $errors[] = "Ya existe una cuenta con este email";
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO clients (name, email, phone, whatsapp, password, allergies) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['whatsapp'] ?? $_POST['phone'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['allergies'] ?? ''
            ]);
            
            $client_id = $pdo->lastInsertId();
            
            // Auto login
            $_SESSION['client_id'] = $client_id;
            $_SESSION['client_name'] = $_POST['name'];
            $_SESSION['client_email'] = $_POST['email'];
            
            $_SESSION['success_message'] = "¡Cuenta creada exitosamente! Bienvenido a Studio Jane.";
            header('Location: dashboard.php');
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Error al crear la cuenta. Intenta nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Studio Jane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/client.css" rel="stylesheet">
</head>
<body class="client-auth-page">
    <div class="auth-container">
        <div class="auth-card register-card">
            <div class="auth-header">
                <div class="brand-logo">
                    <i class="fas fa-star"></i>
                    <h2>Studio Jane</h2>
                </div>
                <p class="auth-subtitle">Crea tu cuenta</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Nombre completo"
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                   required>
                            <label for="name">
                                <i class="fas fa-user me-2"></i>Nombre Completo
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="tu@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   required>
                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="Teléfono"
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                   required>
                            <label for="phone">
                                <i class="fas fa-phone me-2"></i>Teléfono
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" 
                                   class="form-control" 
                                   id="whatsapp" 
                                   name="whatsapp" 
                                   placeholder="WhatsApp"
                                   value="<?php echo htmlspecialchars($_POST['whatsapp'] ?? ''); ?>">
                            <label for="whatsapp">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp (opcional)
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Contraseña"
                                   required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Confirmar contraseña"
                                   required>
                            <label for="confirm_password">
                                <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-floating mb-4">
                    <textarea class="form-control" 
                              id="allergies" 
                              name="allergies" 
                              placeholder="Alergias o sensibilidades"
                              style="height: 80px"><?php echo htmlspecialchars($_POST['allergies'] ?? ''); ?></textarea>
                    <label for="allergies">
                        <i class="fas fa-exclamation-triangle me-2"></i>Alergias o Sensibilidades (opcional)
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                </button>
            </form>
            
            <div class="auth-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                <a href="../index.php" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i>Volver al sitio web
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
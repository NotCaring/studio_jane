<?php
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['client_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ? AND password IS NOT NULL");
        $stmt->execute([$email]);
        $client = $stmt->fetch();
        
        if ($client && password_verify($password, $client['password'])) {
            $_SESSION['client_id'] = $client['id'];
            $_SESSION['client_name'] = $client['name'];
            $_SESSION['client_email'] = $client['email'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Studio Jane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/client.css" rel="stylesheet">
</head>
<body class="client-auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-logo">
                    <i class="fas fa-star"></i>
                    <h2>Studio Jane</h2>
                </div>
                <p class="auth-subtitle">Accede a tu cuenta</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
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
                
                <div class="form-floating mb-4">
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
                
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>
            </form>
            
            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                <a href="../index.php" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i>Volver al sitio web
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
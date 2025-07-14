<?php
session_start();
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Studio Jane Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-star me-2"></i>Studio Jane</h2>
                <p>Panel de Administración</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario o Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../index.php">
                    <i class="fas fa-arrow-left me-1"></i>Volver al sitio web
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
.login-page {
    background: var(--gradient-primary);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.login-container {
    width: 100%;
    max-width: 400px;
}

.login-card {
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.login-header {
    background: var(--gradient-primary);
    color: var(--white);
    padding: 2rem;
    text-align: center;
}

.login-header h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.login-header p {
    opacity: 0.9;
    margin-bottom: 0;
}

.login-card form {
    padding: 2rem;
}

.input-group-text {
    background: var(--bg-light);
    border: 2px solid #E9ECEF;
    border-right: none;
    color: var(--text-light);
}

.form-control {
    border-left: none;
    border: 2px solid #E9ECEF;
    padding: 12px 15px;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: none;
}

.input-group-text + .form-control:focus {
    border-left-color: var(--primary-color);
}

.login-footer {
    padding: 1rem 2rem 2rem;
    text-align: center;
}

.login-footer a {
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.login-footer a:hover {
    color: var(--primary-color);
}

@media (max-width: 576px) {
    .login-page {
        padding: 1rem;
    }
    
    .login-header {
        padding: 1.5rem;
    }
    
    .login-card form {
        padding: 1.5rem;
    }
}
</style>
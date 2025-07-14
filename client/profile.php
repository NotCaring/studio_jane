<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    header('Location: login.php');
    exit;
}

$client_id = $_SESSION['client_id'];

// Get client data
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

$errors = [];
$success = false;

if ($_POST) {
    // Validate required fields
    $required_fields = ['name', 'email', 'phone'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . str_replace('_', ' ', $field) . " es obligatorio";
        }
    }
    
    // Validate email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    }
    
    // Check if email already exists (excluding current user)
    if (empty($errors) && $_POST['email'] !== $client['email']) {
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $client_id]);
        if ($stmt->fetch()) {
            $errors[] = "Ya existe otra cuenta con este email";
        }
    }
    
    // Handle password change
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
            $errors[] = "La nueva contraseña debe tener al menos 6 caracteres";
        }
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors[] = "Las contraseñas no coinciden";
        }
    }
    
    // Handle profile image upload
    $profile_image = $client['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $client_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = 'uploads/profiles/' . $new_filename;
                
                // Delete old profile image
                if ($client['profile_image'] && file_exists('../' . $client['profile_image'])) {
                    unlink('../' . $client['profile_image']);
                }
            } else {
                $errors[] = "Error al subir la imagen";
            }
        } else {
            $errors[] = "Tipo de archivo no permitido. Solo JPG, PNG y GIF";
        }
    }
    
    if (empty($errors)) {
        try {
            $sql = "UPDATE clients SET name = ?, email = ?, phone = ?, whatsapp = ?, allergies = ?, profile_image = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $params = [
                $_POST['name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['whatsapp'] ?? $_POST['phone'],
                $_POST['allergies'] ?? '',
                $profile_image,
                $client_id
            ];
            
            // Update password if provided
            if (!empty($_POST['new_password'])) {
                $sql = "UPDATE clients SET name = ?, email = ?, phone = ?, whatsapp = ?, allergies = ?, profile_image = ?, password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $params = [
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['whatsapp'] ?? $_POST['phone'],
                    $_POST['allergies'] ?? '',
                    $profile_image,
                    password_hash($_POST['new_password'], PASSWORD_DEFAULT),
                    $client_id
                ];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Update session data
            $_SESSION['client_name'] = $_POST['name'];
            $_SESSION['client_email'] = $_POST['email'];
            
            // Refresh client data
            $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->execute([$client_id]);
            $client = $stmt->fetch();
            
            $success = true;
            $_SESSION['success_message'] = "Perfil actualizado exitosamente";
            
        } catch (Exception $e) {
            $errors[] = "Error al actualizar el perfil. Intenta nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Studio Jane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/client.css" rel="stylesheet">
</head>
<body class="client-dashboard">
    <?php include 'includes/header.php'; ?>
    
    <div class="dashboard-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="dashboard-content">
            <div class="content-header">
                <h1 class="page-title">
                    <i class="fas fa-user-edit me-3"></i>
                    Mi Perfil
                </h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Perfil actualizado exitosamente
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-user me-2"></i>
                                Información Personal
                            </h3>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="profile-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               placeholder="Nombre completo"
                                               value="<?php echo htmlspecialchars($client['name']); ?>" 
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
                                               placeholder="Email"
                                               value="<?php echo htmlspecialchars($client['email']); ?>" 
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
                                               value="<?php echo htmlspecialchars($client['phone']); ?>" 
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
                                               value="<?php echo htmlspecialchars($client['whatsapp']); ?>">
                                        <label for="whatsapp">
                                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <textarea class="form-control" 
                                          id="allergies" 
                                          name="allergies" 
                                          placeholder="Alergias o sensibilidades"
                                          style="height: 100px"><?php echo htmlspecialchars($client['allergies']); ?></textarea>
                                <label for="allergies">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Alergias o Sensibilidades
                                </label>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h4 class="mb-3">
                                <i class="fas fa-lock me-2"></i>
                                Cambiar Contraseña
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="password" 
                                               class="form-control" 
                                               id="new_password" 
                                               name="new_password" 
                                               placeholder="Nueva contraseña">
                                        <label for="new_password">
                                            <i class="fas fa-lock me-2"></i>Nueva Contraseña
                                        </label>
                                        <div class="form-text">Deja en blanco si no quieres cambiarla</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               placeholder="Confirmar contraseña">
                                        <label for="confirm_password">
                                            <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-camera me-2"></i>
                                Foto de Perfil
                            </h3>
                        </div>
                        
                        <div class="profile-image-section">
                            <div class="current-image">
                                <?php if ($client['profile_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($client['profile_image']); ?>" 
                                         alt="Foto de perfil" 
                                         class="profile-img"
                                         id="profilePreview">
                                <?php else: ?>
                                    <div class="profile-placeholder" id="profilePreview">
                                        <?php echo strtoupper(substr($client['name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="image-upload">
                                <label for="profile_image" class="btn btn-outline-primary">
                                    <i class="fas fa-camera me-2"></i>Cambiar Foto
                                </label>
                                <input type="file" 
                                       id="profile_image" 
                                       name="profile_image" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="previewImage(this)">
                                <small class="form-text text-muted">
                                    JPG, PNG o GIF. Máximo 2MB.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card mt-4">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-info-circle me-2"></i>
                                Información de Cuenta
                            </h3>
                        </div>
                        
                        <div class="account-info">
                            <div class="info-item">
                                <label>Miembro desde:</label>
                                <span><?php echo date('d/m/Y', strtotime($client['created_at'])); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <label>Última actualización:</label>
                                <span><?php echo date('d/m/Y H:i', strtotime($client['updated_at'])); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <label>Estado de cuenta:</label>
                                <span class="badge bg-success">Activa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/client.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePreview');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Replace placeholder with image
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Foto de perfil';
                        img.className = 'profile-img';
                        img.id = 'profilePreview';
                        preview.parentNode.replaceChild(img, preview);
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
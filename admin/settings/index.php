<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
if ($_POST) {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'submit') {
                $stmt = $pdo->prepare("
                    INSERT INTO site_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$key, $value]);
            }
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = "Configuración actualizada exitosamente";
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al actualizar la configuración";
    }
}

// Get current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Default values if not set
$defaults = [
    'site_name' => 'Studio Jane',
    'site_slogan' => 'Tu belleza, nuestra pasión',
    'site_email' => 'info@studiojane.com',
    'site_phone' => '+57 300 123 4567',
    'site_whatsapp' => '573001234567',
    'site_address' => 'Calle 123 #45-67, Bogotá',
    'business_hours' => 'Lun - Sáb: 9:00 AM - 7:00 PM',
    'facebook_url' => 'https://facebook.com/studiojane',
    'instagram_url' => 'https://instagram.com/studiojane',
    'google_maps_url' => 'https://maps.google.com',
    'appointment_duration' => '60',
    'max_appointments_per_day' => '20',
    'booking_advance_days' => '30',
    'cancellation_hours' => '24'
];

foreach ($defaults as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Studio Jane Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../includes/navbar.php'; ?>
            
            <div class="content-wrapper">
                <div class="page-header">
                    <div class="header-content">
                        <h1 class="page-title">
                            <i class="fas fa-cogs me-3"></i>
                            Configuración del Sistema
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-outline-primary me-2" onclick="resetToDefaults()">
                                <i class="fas fa-undo me-2"></i>Restaurar Defaults
                            </button>
                            <button type="submit" form="settingsForm" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <form id="settingsForm" method="POST">
                    <div class="row">
                        <!-- General Settings -->
                        <div class="col-lg-6">
                            <div class="data-card">
                                <h3 class="gradient-text mb-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Información General
                                </h3>
                                
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Nombre del Sitio</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_slogan" class="form-label">Eslogan</label>
                                    <input type="text" class="form-control" id="site_slogan" name="site_slogan" 
                                           value="<?php echo htmlspecialchars($settings['site_slogan']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_email" class="form-label">Email de Contacto</label>
                                    <input type="email" class="form-control" id="site_email" name="site_email" 
                                           value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_phone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="site_phone" name="site_phone" 
                                           value="<?php echo htmlspecialchars($settings['site_phone']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_whatsapp" class="form-label">WhatsApp (solo números)</label>
                                    <input type="text" class="form-control" id="site_whatsapp" name="site_whatsapp" 
                                           value="<?php echo htmlspecialchars($settings['site_whatsapp']); ?>" 
                                           placeholder="573001234567">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_address" class="form-label">Dirección</label>
                                    <textarea class="form-control" id="site_address" name="site_address" rows="2"><?php echo htmlspecialchars($settings['site_address']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="business_hours" class="form-label">Horarios de Atención</label>
                                    <input type="text" class="form-control" id="business_hours" name="business_hours" 
                                           value="<?php echo htmlspecialchars($settings['business_hours']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Social Media & Links -->
                        <div class="col-lg-6">
                            <div class="data-card">
                                <h3 class="gradient-text mb-4">
                                    <i class="fas fa-share-alt me-2"></i>
                                    Redes Sociales
                                </h3>
                                
                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">
                                        <i class="fab fa-facebook me-2"></i>Facebook URL
                                    </label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                           value="<?php echo htmlspecialchars($settings['facebook_url']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">
                                        <i class="fab fa-instagram me-2"></i>Instagram URL
                                    </label>
                                    <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                           value="<?php echo htmlspecialchars($settings['instagram_url']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="google_maps_url" class="form-label">
                                        <i class="fas fa-map-marker-alt me-2"></i>Google Maps URL
                                    </label>
                                    <input type="url" class="form-control" id="google_maps_url" name="google_maps_url" 
                                           value="<?php echo htmlspecialchars($settings['google_maps_url']); ?>">
                                </div>
                            </div>
                            
                            <div class="data-card mt-4">
                                <h3 class="gradient-text mb-4">
                                    <i class="fas fa-calendar-cog me-2"></i>
                                    Configuración de Citas
                                </h3>
                                
                                <div class="mb-3">
                                    <label for="appointment_duration" class="form-label">Duración por Defecto (minutos)</label>
                                    <input type="number" class="form-control" id="appointment_duration" name="appointment_duration" 
                                           value="<?php echo htmlspecialchars($settings['appointment_duration']); ?>" min="15" max="240">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_appointments_per_day" class="form-label">Máximo de Citas por Día</label>
                                    <input type="number" class="form-control" id="max_appointments_per_day" name="max_appointments_per_day" 
                                           value="<?php echo htmlspecialchars($settings['max_appointments_per_day']); ?>" min="1" max="50">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="booking_advance_days" class="form-label">Días de Anticipación para Reservas</label>
                                    <input type="number" class="form-control" id="booking_advance_days" name="booking_advance_days" 
                                           value="<?php echo htmlspecialchars($settings['booking_advance_days']); ?>" min="1" max="90">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cancellation_hours" class="form-label">Horas Mínimas para Cancelación</label>
                                    <input type="number" class="form-control" id="cancellation_hours" name="cancellation_hours" 
                                           value="<?php echo htmlspecialchars($settings['cancellation_hours']); ?>" min="1" max="72">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Information -->
                    <div class="data-card mt-4">
                        <h3 class="gradient-text mb-4">
                            <i class="fas fa-server me-2"></i>
                            Información del Sistema
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="system-info-card">
                                    <div class="system-icon">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <div class="system-details">
                                        <div class="system-label">Versión PHP</div>
                                        <div class="system-value"><?php echo PHP_VERSION; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="system-info-card">
                                    <div class="system-icon">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div class="system-details">
                                        <div class="system-label">Base de Datos</div>
                                        <div class="system-value">MySQL <?php echo $pdo->query('SELECT VERSION()')->fetchColumn(); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="system-info-card">
                                    <div class="system-icon">
                                        <i class="fas fa-memory"></i>
                                    </div>
                                    <div class="system-details">
                                        <div class="system-label">Memoria PHP</div>
                                        <div class="system-value"><?php echo ini_get('memory_limit'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="system-info-card">
                                    <div class="system-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="system-details">
                                        <div class="system-label">Zona Horaria</div>
                                        <div class="system-value"><?php echo date_default_timezone_get(); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        function resetToDefaults() {
            if (confirm('¿Estás seguro de restaurar la configuración por defecto? Esta acción no se puede deshacer.')) {
                // Reset form to default values
                document.getElementById('site_name').value = 'Studio Jane';
                document.getElementById('site_slogan').value = 'Tu belleza, nuestra pasión';
                document.getElementById('site_email').value = 'info@studiojane.com';
                document.getElementById('site_phone').value = '+57 300 123 4567';
                document.getElementById('site_whatsapp').value = '573001234567';
                document.getElementById('site_address').value = 'Calle 123 #45-67, Bogotá';
                document.getElementById('business_hours').value = 'Lun - Sáb: 9:00 AM - 7:00 PM';
                document.getElementById('facebook_url').value = 'https://facebook.com/studiojane';
                document.getElementById('instagram_url').value = 'https://instagram.com/studiojane';
                document.getElementById('google_maps_url').value = 'https://maps.google.com';
                document.getElementById('appointment_duration').value = '60';
                document.getElementById('max_appointments_per_day').value = '20';
                document.getElementById('booking_advance_days').value = '30';
                document.getElementById('cancellation_hours').value = '24';
                
                showNotification('Configuración restaurada a valores por defecto', 'info');
            }
        }
        
        // Form validation
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const whatsapp = document.getElementById('site_whatsapp').value;
            if (whatsapp && !/^\d+$/.test(whatsapp)) {
                e.preventDefault();
                showNotification('El número de WhatsApp debe contener solo números', 'error');
                return false;
            }
            
            showNotification('Guardando configuración...', 'info');
        });
        
        // Auto-save functionality
        let autoSaveTimeout;
        const formInputs = document.querySelectorAll('#settingsForm input, #settingsForm textarea');
        
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    // Auto-save could be implemented here
                    console.log('Auto-save triggered');
                }, 2000);
            });
        });
    </script>
</body>
</html>

<style>
.system-info-card {
    background: var(--hover-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
    margin-bottom: 1rem;
}

.system-info-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.system-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--border-radius);
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
}

.system-details {
    flex: 1;
}

.system-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.system-value {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 0.9rem;
}

.form-label i {
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .system-info-card {
        padding: 1rem;
    }
    
    .system-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>
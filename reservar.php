<?php
session_start();
require_once 'config/database.php';

// Get service ID from URL if provided
$selected_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;

// Get all services for dropdown
$stmt = $pdo->query("
    SELECT s.*, c.name as category_name 
    FROM services s 
    LEFT JOIN categories c ON s.category_id = c.id 
    WHERE s.status = 'active' 
    ORDER BY c.name, s.name
");
$services = $stmt->fetchAll();

// Get all professionals
$stmt = $pdo->query("
    SELECT * FROM professionals 
    WHERE status = 'active' AND is_available = 1 
    ORDER BY name
");
$professionals = $stmt->fetchAll();

// Get selected service details if ID provided
$selected_service = null;
if ($selected_service_id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 'active'");
    $stmt->execute([$selected_service_id]);
    $selected_service = $stmt->fetch();
}

// Handle form submission
if ($_POST) {
    $errors = [];
    
    // Validate required fields
    $required_fields = ['client_name', 'client_email', 'client_phone', 'service_id', 'appointment_date', 'appointment_time'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . str_replace('_', ' ', $field) . " es obligatorio";
        }
    }
    
    // Validate email
    if (!empty($_POST['client_email']) && !filter_var($_POST['client_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    }
    
    // Validate date (must be future date)
    if (!empty($_POST['appointment_date'])) {
        $appointment_date = $_POST['appointment_date'];
        if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
            $errors[] = "La fecha debe ser posterior a hoy";
        }
    }
    
    // Check if slot is available
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE appointment_date = ? AND appointment_time = ? 
            AND status NOT IN ('cancelled') 
            AND (professional_id = ? OR professional_id IS NULL)
        ");
        $professional_id = !empty($_POST['professional_id']) ? $_POST['professional_id'] : null;
        $stmt->execute([$_POST['appointment_date'], $_POST['appointment_time'], $professional_id]);
        
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "El horario seleccionado no está disponible";
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Get service details
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([$_POST['service_id']]);
            $service = $stmt->fetch();
            
            // Check if client exists
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? OR phone = ?");
            $stmt->execute([$_POST['client_email'], $_POST['client_phone']]);
            $client = $stmt->fetch();
            
            if ($client) {
                $client_id = $client['id'];
                // Update client info
                $stmt = $pdo->prepare("
                    UPDATE clients SET 
                    name = ?, email = ?, phone = ?, whatsapp = ?, 
                    allergies = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['client_name'],
                    $_POST['client_email'],
                    $_POST['client_phone'],
                    $_POST['client_whatsapp'] ?? $_POST['client_phone'],
                    $_POST['allergies'] ?? '',
                    $client_id
                ]);
            } else {
                // Create new client
                $stmt = $pdo->prepare("
                    INSERT INTO clients (name, email, phone, whatsapp, allergies) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['client_name'],
                    $_POST['client_email'],
                    $_POST['client_phone'],
                    $_POST['client_whatsapp'] ?? $_POST['client_phone'],
                    $_POST['allergies'] ?? ''
                ]);
                $client_id = $pdo->lastInsertId();
            }
            
            // Create appointment
            $stmt = $pdo->prepare("
                INSERT INTO appointments (
                    client_id, service_id, professional_id, appointment_date, 
                    appointment_time, duration, price, allergies, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $client_id,
                $_POST['service_id'],
                !empty($_POST['professional_id']) ? $_POST['professional_id'] : null,
                $_POST['appointment_date'],
                $_POST['appointment_time'],
                $service['duration'],
                $service['price'],
                $_POST['allergies'] ?? '',
                $_POST['notes'] ?? ''
            ]);
            
            $appointment_id = $pdo->lastInsertId();
            
            $pdo->commit();
            
            // Here you would send confirmation email
            // sendConfirmationEmail($appointment_id);
            
            $_SESSION['success_message'] = "¡Cita reservada exitosamente! Te contactaremos pronto para confirmar.";
            header('Location: reservar.php?success=1');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error al procesar la reserva. Intenta nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cita - Studio Jane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section py-5">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 text-center">
                    <h1 class="hero-title">Reservar Cita</h1>
                    <p class="hero-subtitle">Agenda tu momento de belleza y relajación</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Reservation Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
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
                    
                    <div class="admin-card">
                        <h2 class="mb-4">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Datos de la Reserva
                        </h2>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client_name" class="form-label">Nombre Completo *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="client_name" 
                                               name="client_name" 
                                               value="<?php echo htmlspecialchars($_POST['client_name'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa tu nombre completo
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client_email" class="form-label">Email *</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="client_email" 
                                               name="client_email" 
                                               value="<?php echo htmlspecialchars($_POST['client_email'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa un email válido
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client_phone" class="form-label">Teléfono *</label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="client_phone" 
                                               name="client_phone" 
                                               value="<?php echo htmlspecialchars($_POST['client_phone'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa tu número de teléfono
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client_whatsapp" class="form-label">WhatsApp (opcional)</label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="client_whatsapp" 
                                               name="client_whatsapp" 
                                               value="<?php echo htmlspecialchars($_POST['client_whatsapp'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="service_id" class="form-label">Servicio *</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">Selecciona un servicio</option>
                                    <?php 
                                    $current_category = '';
                                    foreach ($services as $service): 
                                        if ($current_category != $service['category_name']):
                                            if ($current_category != '') echo '</optgroup>';
                                            echo '<optgroup label="' . htmlspecialchars($service['category_name']) . '">';
                                            $current_category = $service['category_name'];
                                        endif;
                                    ?>
                                        <option value="<?php echo $service['id']; ?>" 
                                                <?php echo ($selected_service_id == $service['id'] || ($_POST['service_id'] ?? '') == $service['id']) ? 'selected' : ''; ?>
                                                data-price="<?php echo $service['price']; ?>"
                                                data-duration="<?php echo $service['duration']; ?>">
                                            <?php echo htmlspecialchars($service['name']); ?> 
                                            - $<?php echo number_format($service['price'], 0, ',', '.'); ?> 
                                            (<?php echo $service['duration']; ?> min)
                                        </option>
                                    <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona un servicio
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="professional_id" class="form-label">Profesional (opcional)</label>
                                <select class="form-select" id="professional_id" name="professional_id">
                                    <option value="">Sin preferencia</option>
                                    <?php foreach ($professionals as $professional): ?>
                                        <option value="<?php echo $professional['id']; ?>" 
                                                <?php echo ($_POST['professional_id'] ?? '') == $professional['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($professional['name']); ?>
                                            <?php if ($professional['specialties']): ?>
                                                - <?php echo htmlspecialchars($professional['specialties']); ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="appointment_date" class="form-label">Fecha *</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="appointment_date" 
                                               name="appointment_date" 
                                               value="<?php echo htmlspecialchars($_POST['appointment_date'] ?? ''); ?>" 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor selecciona una fecha válida
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="appointment_time" class="form-label">Hora *</label>
                                        <select class="form-select" id="appointment_time" name="appointment_time" required>
                                            <option value="">Selecciona una hora</option>
                                            <option value="09:00" <?php echo ($_POST['appointment_time'] ?? '') == '09:00' ? 'selected' : ''; ?>>9:00 AM</option>
                                            <option value="10:00" <?php echo ($_POST['appointment_time'] ?? '') == '10:00' ? 'selected' : ''; ?>>10:00 AM</option>
                                            <option value="11:00" <?php echo ($_POST['appointment_time'] ?? '') == '11:00' ? 'selected' : ''; ?>>11:00 AM</option>
                                            <option value="12:00" <?php echo ($_POST['appointment_time'] ?? '') == '12:00' ? 'selected' : ''; ?>>12:00 PM</option>
                                            <option value="14:00" <?php echo ($_POST['appointment_time'] ?? '') == '14:00' ? 'selected' : ''; ?>>2:00 PM</option>
                                            <option value="15:00" <?php echo ($_POST['appointment_time'] ?? '') == '15:00' ? 'selected' : ''; ?>>3:00 PM</option>
                                            <option value="16:00" <?php echo ($_POST['appointment_time'] ?? '') == '16:00' ? 'selected' : ''; ?>>4:00 PM</option>
                                            <option value="17:00" <?php echo ($_POST['appointment_time'] ?? '') == '17:00' ? 'selected' : ''; ?>>5:00 PM</option>
                                            <option value="18:00" <?php echo ($_POST['appointment_time'] ?? '') == '18:00' ? 'selected' : ''; ?>>6:00 PM</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor selecciona una hora
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="allergies" class="form-label">Alergias o Sensibilidades</label>
                                <textarea class="form-control" 
                                          id="allergies" 
                                          name="allergies" 
                                          rows="3" 
                                          placeholder="Menciona cualquier alergia o sensibilidad que debamos considerar..."><?php echo htmlspecialchars($_POST['allergies'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="notes" class="form-label">Notas Adicionales</label>
                                <textarea class="form-control" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Cualquier información adicional que consideres importante..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="servicios.php" class="btn btn-outline-secondary btn-lg me-md-2">
                                    <i class="fas fa-arrow-left me-1"></i>Volver a Servicios
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-check me-1"></i>Reservar Cita
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>
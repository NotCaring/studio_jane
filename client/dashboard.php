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

// Get client's appointments
$stmt = $pdo->prepare("
    SELECT a.*, s.name as service_name, s.duration, p.name as professional_name
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    LEFT JOIN professionals p ON a.professional_id = p.id
    WHERE a.client_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 10
");
$stmt->execute([$client_id]);
$appointments = $stmt->fetchAll();

// Get upcoming appointments
$stmt = $pdo->prepare("
    SELECT a.*, s.name as service_name, s.duration, p.name as professional_name
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    LEFT JOIN professionals p ON a.professional_id = p.id
    WHERE a.client_id = ? AND a.appointment_date >= CURDATE() AND a.status != 'cancelled'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 5
");
$stmt->execute([$client_id]);
$upcoming_appointments = $stmt->fetchAll();

// Get statistics
$stats = [];
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE client_id = ?");
$stmt->execute([$client_id]);
$stats['total_appointments'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM appointments WHERE client_id = ? AND status = 'completed'");
$stmt->execute([$client_id]);
$stats['completed_appointments'] = $stmt->fetch()['completed'];

$stmt = $pdo->prepare("SELECT SUM(price) as total_spent FROM appointments WHERE client_id = ? AND status = 'completed'");
$stmt->execute([$client_id]);
$stats['total_spent'] = $stmt->fetch()['total_spent'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as upcoming FROM appointments WHERE client_id = ? AND appointment_date >= CURDATE() AND status != 'cancelled'");
$stmt->execute([$client_id]);
$stats['upcoming_appointments'] = $stmt->fetch()['upcoming'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Dashboard - Studio Jane</title>
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
                <div class="welcome-section">
                    <h1 class="welcome-title">
                        ¡Hola, <?php echo htmlspecialchars($client['name']); ?>! 👋
                    </h1>
                    <p class="welcome-subtitle">Bienvenido a tu panel personal de Studio Jane</p>
                </div>
                
                <div class="header-actions">
                    <a href="../reservar.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Nueva Cita
                    </a>
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

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total_appointments']; ?></div>
                        <div class="stat-label">Total de Citas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['upcoming_appointments']; ?></div>
                        <div class="stat-label">Próximas Citas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['completed_appointments']; ?></div>
                        <div class="stat-label">Citas Completadas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">$<?php echo number_format($stats['total_spent'], 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Invertido</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Upcoming Appointments -->
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-calendar-alt me-2"></i>
                                Próximas Citas
                            </h3>
                            <a href="appointments.php" class="btn btn-outline-primary btn-sm">
                                Ver todas
                            </a>
                        </div>
                        
                        <?php if (empty($upcoming_appointments)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>No tienes citas próximas</h5>
                                <p class="text-muted">¡Es hora de agendar tu próxima sesión de belleza!</p>
                                <a href="../reservar.php" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-2"></i>Reservar Ahora
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="appointments-list">
                                <?php foreach ($upcoming_appointments as $appointment): ?>
                                    <div class="appointment-item">
                                        <div class="appointment-date">
                                            <div class="date-day"><?php echo date('d', strtotime($appointment['appointment_date'])); ?></div>
                                            <div class="date-month"><?php echo date('M', strtotime($appointment['appointment_date'])); ?></div>
                                        </div>
                                        
                                        <div class="appointment-details">
                                            <h5><?php echo htmlspecialchars($appointment['service_name']); ?></h5>
                                            <div class="appointment-meta">
                                                <span class="time">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?>
                                                </span>
                                                <span class="duration">
                                                    <i class="fas fa-hourglass-half me-1"></i>
                                                    <?php echo $appointment['duration']; ?> min
                                                </span>
                                                <?php if ($appointment['professional_name']): ?>
                                                    <span class="professional">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?php echo htmlspecialchars($appointment['professional_name']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="appointment-status">
                                            <span class="badge status-<?php echo $appointment['status']; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-bolt me-2"></i>
                                Acciones Rápidas
                            </h3>
                        </div>
                        
                        <div class="quick-actions">
                            <a href="../reservar.php" class="quick-action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="action-content">
                                    <h5>Nueva Cita</h5>
                                    <p>Agenda tu próxima sesión</p>
                                </div>
                            </a>
                            
                            <a href="profile.php" class="quick-action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div class="action-content">
                                    <h5>Editar Perfil</h5>
                                    <p>Actualiza tu información</p>
                                </div>
                            </a>
                            
                            <a href="appointments.php" class="quick-action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="action-content">
                                    <h5>Historial</h5>
                                    <p>Ve todas tus citas</p>
                                </div>
                            </a>
                            
                            <a href="../contacto.php" class="quick-action-btn">
                                <div class="action-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="action-content">
                                    <h5>Soporte</h5>
                                    <p>¿Necesitas ayuda?</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Profile Summary -->
                    <div class="dashboard-card mt-4">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-user me-2"></i>
                                Mi Perfil
                            </h3>
                        </div>
                        
                        <div class="profile-summary">
                            <div class="profile-avatar">
                                <?php if ($client['profile_image']): ?>
                                    <img src="<?php echo htmlspecialchars($client['profile_image']); ?>" alt="Perfil">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($client['name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="profile-info">
                                <h4><?php echo htmlspecialchars($client['name']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($client['email']); ?></p>
                                <p class="text-muted">
                                    <i class="fas fa-phone me-1"></i>
                                    <?php echo htmlspecialchars($client['phone']); ?>
                                </p>
                                
                                <a href="profile.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Editar Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/client.js"></script>
</body>
</html>
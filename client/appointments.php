<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    header('Location: login.php');
    exit;
}

$client_id = $_SESSION['client_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query with filters
$query = "
    SELECT a.*, s.name as service_name, s.duration, s.price as service_price,
           p.name as professional_name
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    LEFT JOIN professionals p ON a.professional_id = p.id
    WHERE a.client_id = ?
";

$params = [$client_id];

if ($status_filter) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $query .= " AND a.appointment_date = ?";
    $params[] = $date_filter;
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Get statistics
$stats = [];
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE client_id = ? GROUP BY status");
$stmt->execute([$client_id]);
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - Studio Jane</title>
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
                    <i class="fas fa-calendar-alt me-3"></i>
                    Mis Citas
                </h1>
                <div class="header-actions">
                    <a href="../reservar.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Nueva Cita
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['confirmed'] ?? 0; ?></div>
                        <div class="stat-label">Confirmadas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['completed'] ?? 0; ?></div>
                        <div class="stat-label">Completadas</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['cancelled'] ?? 0; ?></div>
                        <div class="stat-label">Canceladas</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="dashboard-card">
                <form method="GET" class="filter-form">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select class="form-select" name="status">
                                <option value="">Todos los estados</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmadas</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completadas</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Canceladas</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>Filtrar
                            </button>
                            <a href="appointments.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Appointments List -->
            <div class="dashboard-card">
                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5>No se encontraron citas</h5>
                        <p class="text-muted">Ajusta los filtros o agenda tu primera cita</p>
                        <a href="../reservar.php" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Reservar Ahora
                        </a>
                    </div>
                <?php else: ?>
                    <div class="appointments-timeline">
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-card">
                                <div class="appointment-date-badge">
                                    <div class="date-day"><?php echo date('d', strtotime($appointment['appointment_date'])); ?></div>
                                    <div class="date-month"><?php echo date('M', strtotime($appointment['appointment_date'])); ?></div>
                                    <div class="date-year"><?php echo date('Y', strtotime($appointment['appointment_date'])); ?></div>
                                </div>
                                
                                <div class="appointment-content">
                                    <div class="appointment-header">
                                        <h4><?php echo htmlspecialchars($appointment['service_name']); ?></h4>
                                        <span class="badge status-<?php echo $appointment['status']; ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <i class="fas fa-clock me-2"></i>
                                            <span><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?> 
                                                  (<?php echo $appointment['duration']; ?> min)</span>
                                        </div>
                                        
                                        <?php if ($appointment['professional_name']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-user me-2"></i>
                                                <span><?php echo htmlspecialchars($appointment['professional_name']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="detail-item">
                                            <i class="fas fa-dollar-sign me-2"></i>
                                            <span>$<?php echo number_format($appointment['price'], 0, ',', '.'); ?></span>
                                        </div>
                                        
                                        <?php if ($appointment['notes']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-sticky-note me-2"></i>
                                                <span><?php echo htmlspecialchars($appointment['notes']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="appointment-actions">
                                        <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                                            <?php if (strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']) > time() + 24*3600): ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                                    <i class="fas fa-times me-1"></i>Cancelar
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if ($appointment['status'] === 'completed'): ?>
                                            <button class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-star me-1"></i>Calificar
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="https://wa.me/<?php echo SITE_WHATSAPP; ?>?text=Hola, tengo una consulta sobre mi cita del <?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/client.js"></script>
    <script>
        function cancelAppointment(appointmentId) {
            if (confirm('¿Estás seguro de que quieres cancelar esta cita?')) {
                // Here you would make an AJAX call to cancel the appointment
                fetch('ajax/cancel_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        appointment_id: appointmentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al cancelar la cita');
                    }
                });
            }
        }
    </script>
</body>
</html>
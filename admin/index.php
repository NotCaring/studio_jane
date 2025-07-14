<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get dashboard statistics
$stats = [];

// Total appointments
$stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments");
$stats['total_appointments'] = $stmt->fetch()['total'];

// Pending appointments
$stmt = $pdo->query("SELECT COUNT(*) as pending FROM appointments WHERE status = 'pending'");
$stats['pending_appointments'] = $stmt->fetch()['pending'];

// Total clients
$stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
$stats['total_clients'] = $stmt->fetch()['total'];

// Total services
$stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE status = 'active'");
$stats['total_services'] = $stmt->fetch()['total'];

// Today's appointments
$stmt = $pdo->prepare("
    SELECT COUNT(*) as today 
    FROM appointments 
    WHERE appointment_date = CURDATE()
");
$stmt->execute();
$stats['today_appointments'] = $stmt->fetch()['today'];

// This month's revenue
$stmt = $pdo->prepare("
    SELECT SUM(price) as revenue 
    FROM appointments 
    WHERE MONTH(appointment_date) = MONTH(CURDATE()) 
    AND YEAR(appointment_date) = YEAR(CURDATE())
    AND status = 'completed'
");
$stmt->execute();
$stats['monthly_revenue'] = $stmt->fetch()['revenue'] ?? 0;

// Recent appointments
$stmt = $pdo->prepare("
    SELECT a.*, c.name as client_name, s.name as service_name
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    JOIN services s ON a.service_id = s.id
    ORDER BY a.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Studio Jane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>
            
            <div class="content-wrapper">
                <div class="page-header">
                    <div class="header-content">
                        <h1 class="page-title">
                            <i class="fas fa-chart-line me-3"></i>
                            Panel de Control
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-primary" onclick="refreshDashboard()">
                                <i class="fas fa-sync-alt me-2"></i>Actualizar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-stat="total_appointments"><?php echo $stats['total_appointments']; ?></div>
                            <div class="stat-label">Total Citas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-stat="pending_appointments"><?php echo $stats['pending_appointments']; ?></div>
                            <div class="stat-label">Citas Pendientes</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-stat="total_clients"><?php echo $stats['total_clients']; ?></div>
                            <div class="stat-label">Clientes</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number" data-stat="monthly_revenue">$<?php echo number_format($stats['monthly_revenue'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Ingresos del Mes</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Today's Appointments -->
                    <div class="col-lg-8">
                        <div class="data-card">
                            <div class="card-header">
                                <h3 class="gradient-text">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Citas de Hoy
                                </h3>
                            </div>
                            
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT a.*, c.name as client_name, s.name as service_name, p.name as professional_name
                                FROM appointments a
                                JOIN clients c ON a.client_id = c.id
                                JOIN services s ON a.service_id = s.id
                                LEFT JOIN professionals p ON a.professional_id = p.id
                                WHERE a.appointment_date = CURDATE()
                                ORDER BY a.appointment_time ASC
                            ");
                            $stmt->execute();
                            $today_appointments = $stmt->fetchAll();
                            ?>
                            
                            <?php if (empty($today_appointments)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay citas programadas para hoy</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table modern-table">
                                        <thead>
                                            <tr>
                                                <th>Hora</th>
                                                <th>Cliente</th>
                                                <th>Servicio</th>
                                                <th>Profesional</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($today_appointments as $appointment): ?>
                                                <tr data-appointment-id="<?php echo $appointment['id']; ?>">
                                                    <td class="fw-bold"><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['professional_name'] ?? 'No asignado'); ?></td>
                                                    <td>
                                                        <span class="badge status-badge badge-<?php echo getStatusClass($appointment['status']); ?>">
                                                            <?php echo ucfirst($appointment['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="/admin/appointments/view.php?id=<?php echo $appointment['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions & Summary -->
                    <div class="col-lg-4">
                        <div class="data-card">
                            <h3 class="gradient-text mb-4">
                                <i class="fas fa-bolt me-2"></i>
                                Acciones Rápidas
                            </h3>
                            
                            <div class="d-grid gap-3">
                                <a href="/admin/appointments/create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Nueva Cita
                                </a>
                                <a href="/admin/clients/index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-users me-2"></i>Ver Clientes
                                </a>
                                <a href="/admin/services/index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-spa me-2"></i>Gestionar Servicios
                                </a>
                                <a href="/admin/professionals/index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-user-tie me-2"></i>Profesionales
                                </a>
                            </div>
                        </div>
                        
                        <div class="data-card mt-4">
                            <h3 class="gradient-text mb-4">
                                <i class="fas fa-chart-bar me-2"></i>
                                Resumen Mensual
                            </h3>
                            
                            <div class="summary-items">
                                <div class="summary-item">
                                    <div class="summary-label">Citas de Hoy</div>
                                    <div class="summary-value"><?php echo $stats['today_appointments']; ?></div>
                                </div>
                                
                                <div class="summary-item">
                                    <div class="summary-label">Total Servicios</div>
                                    <div class="summary-value"><?php echo $stats['total_services']; ?></div>
                                </div>
                                
                                <div class="summary-item">
                                    <div class="summary-label">Ingresos Estimados</div>
                                    <div class="summary-value amount">$<?php echo number_format($stats['monthly_revenue'], 0, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        function refreshDashboard() {
            location.reload();
        }
        
        function getStatusClass(status) {
            const classes = {
                'pending': 'warning',
                'confirmed': 'success',
                'cancelled': 'danger',
                'completed': 'info'
            };
            return classes[status] || 'secondary';
        }
    </script>
</body>
</html>

<?php
function getStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'completed':
            return 'info';
        default:
            return 'secondary';
    }
}
?>

<style>
.card-header {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.summary-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--hover-bg);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.summary-item:hover {
    background: var(--border-color);
}

.summary-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.summary-value {
    font-weight: 700;
    color: var(--text-primary);
}

.navbar-actions {
    display: flex;
    align-items: center;
}

#notificationCount {
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .navbar-actions {
        display: none;
    }
    
    .summary-items {
        gap: 0.5rem;
    }
    
    .summary-item {
        padding: 0.5rem;
    }
}
</style>
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="fas fa-chart-line me-2"></i>
                        Panel de Control
                    </h1>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_appointments']; ?></div>
                            <div class="stat-label">Total Citas</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['pending_appointments']; ?></div>
                            <div class="stat-label">Citas Pendientes</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['total_clients']; ?></div>
                            <div class="stat-label">Clientes</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number">$<?php echo number_format($stats['monthly_revenue'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Ingresos del Mes</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Today's Appointments -->
                <div class="col-md-8">
                    <div class="admin-card">
                        <h3 class="mb-4">
                            <i class="fas fa-calendar-day me-2"></i>
                            Citas de Hoy
                        </h3>
                        
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT a.*, c.name as client_name, s.name as service_name, p.name as professional_name
                            FROM appointments a
                            JOIN clients c ON a.client_id = c.id
                            JOIN services s ON a.service_id = s.id
                            LEFT JOIN professionals p ON a.professional_id = p.id
                            WHERE a.appointment_date = CURDATE()
                            ORDER BY a.appointment_time ASC
                        ");
                        $stmt->execute();
                        $today_appointments = $stmt->fetchAll();
                        ?>
                        
                        <?php if (empty($today_appointments)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay citas programadas para hoy</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Hora</th>
                                            <th>Cliente</th>
                                            <th>Servicio</th>
                                            <th>Profesional</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($today_appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['client_name']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['professional_name'] ?? 'No asignado'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusColor($appointment['status']); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="appointments/view.php?id=<?php echo $appointment['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="col-md-4">
                    <div class="admin-card">
                        <h3 class="mb-4">
                            <i class="fas fa-bolt me-2"></i>
                            Acciones Rápidas
                        </h3>
                        
                        <div class="d-grid gap-3">
                            <a href="appointments/create.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Nueva Cita
                            </a>
                            <a href="clients/index.php" class="btn btn-outline-primary">
                                <i class="fas fa-users me-2"></i>Ver Clientes
                            </a>
                            <a href="services/index.php" class="btn btn-outline-primary">
                                <i class="fas fa-spa me-2"></i>Gestionar Servicios
                            </a>
                            <a href="professionals/index.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-tie me-2"></i>Profesionales
                            </a>
                        </div>
                    </div>
                    
                    <div class="admin-card mt-4">
                        <h3 class="mb-4">
                            <i class="fas fa-chart-bar me-2"></i>
                            Resumen Mensual
                        </h3>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Citas de Hoy</span>
                                <strong><?php echo $stats['today_appointments']; ?></strong>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Total Servicios</span>
                                <strong><?php echo $stats['total_services']; ?></strong>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Ingresos Estimados</span>
                                <strong>$<?php echo number_format($stats['monthly_revenue'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'completed':
            return 'info';
        default:
            return 'secondary';
    }
}
?>

<style>
.main-content {
    margin-left: 250px;
    min-height: 100vh;
    background: #F8F9FA;
}

.stat-card {
    background: var(--white);
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    background: var(--gradient-primary);
    color: var(--white);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1;
}

.stat-label {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.admin-card {
    background: var(--white);
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
}

.admin-card h3 {
    color: var(--text-dark);
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
}
</style>
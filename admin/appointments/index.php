<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle status updates
if (isset($_POST['update_status']) && isset($_POST['appointment_id'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $new_status = $_POST['new_status'];
    
    $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $appointment_id]);
        
        $_SESSION['success_message'] = "Estado actualizado exitosamente";
        header('Location: index.php');
        exit;
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$query = "
    SELECT a.*, c.name as client_name, c.phone as client_phone, c.email as client_email,
           s.name as service_name, s.duration as service_duration, s.price as service_price,
           p.name as professional_name
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    JOIN services s ON a.service_id = s.id
    LEFT JOIN professionals p ON a.professional_id = p.id
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $query .= " AND a.appointment_date = ?";
    $params[] = $date_filter;
}

if ($search) {
    $query .= " AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas - Studio Jane Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
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
                            <i class="fas fa-calendar-alt me-3"></i>
                            Gestión de Citas
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-outline-primary me-2" onclick="exportAppointments()">
                                <i class="fas fa-download me-2"></i>Exportar
                            </button>
                            <a href="create.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Nueva Cita
                            </a>
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

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['confirmed'] ?? 0; ?></div>
                            <div class="stat-label">Confirmadas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['cancelled'] ?? 0; ?></div>
                            <div class="stat-label">Canceladas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['completed'] ?? 0; ?></div>
                            <div class="stat-label">Completadas</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" class="filter-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Todos los estados</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmadas</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Canceladas</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completadas</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <div class="search-input-group">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" name="search" 
                                           placeholder="Buscar cliente..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Appointments Table -->
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="table modern-table" id="appointmentsTable">
                            <thead>
                                <tr>
                                    <th data-sort="id">ID</th>
                                    <th data-sort="client_name">Cliente</th>
                                    <th>Contacto</th>
                                    <th data-sort="service_name">Servicio</th>
                                    <th data-sort="appointment_date">Fecha</th>
                                    <th data-sort="appointment_time">Hora</th>
                                    <th>Profesional</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <h5>No se encontraron citas</h5>
                                                <p class="text-muted">Ajusta los filtros o crea una nueva cita</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr data-appointment-id="<?php echo $appointment['id']; ?>">
                                            <td data-sort-value="id"><?php echo $appointment['id']; ?></td>
                                            <td data-sort-value="client_name">
                                                <div class="client-info">
                                                    <div class="client-avatar">
                                                        <?php echo strtoupper(substr($appointment['client_name'], 0, 2)); ?>
                                                    </div>
                                                    <div class="client-details">
                                                        <div class="client-name"><?php echo htmlspecialchars($appointment['client_name']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($appointment['client_phone']); ?></div>
                                                    <div><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($appointment['client_email']); ?></div>
                                                </div>
                                            </td>
                                            <td data-sort-value="service_name">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($appointment['service_name']); ?></strong><br>
                                                    <small class="text-muted">
                                                        <?php echo $appointment['service_duration']; ?> min - 
                                                        $<?php echo number_format($appointment['service_price'], 0, ',', '.'); ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td data-sort-value="appointment_date"><?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?></td>
                                            <td data-sort-value="appointment_time"><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['professional_name'] ?? 'Sin asignar'); ?></td>
                                            <td>
                                                <select class="form-select form-select-sm status-select" 
                                                        data-appointment-id="<?php echo $appointment['id']; ?>"
                                                        onchange="updateStatus(this)">
                                                    <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmada</option>
                                                    <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelada</option>
                                                    <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completada</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewAppointment(<?php echo $appointment['id']; ?>)"
                                                            data-bs-toggle="tooltip" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            onclick="editAppointment(<?php echo $appointment['id']; ?>)"
                                                            data-bs-toggle="tooltip" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteAppointment(<?php echo $appointment['id']; ?>)"
                                                            data-bs-toggle="tooltip" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        // Initialize data table
        document.addEventListener('DOMContentLoaded', function() {
            initializeDataTable('appointmentsTable');
        });
        
        function updateStatus(select) {
            const appointmentId = select.dataset.appointmentId;
            const newStatus = select.value;
            
            // Show loading
            select.disabled = true;
            
            // Make AJAX request
            fetch('ajax/update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: appointmentId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                select.disabled = false;
                if (data.success) {
                    showNotification('Estado actualizado correctamente', 'success');
                } else {
                    showNotification('Error al actualizar el estado', 'error');
                    // Revert select value
                    select.value = select.dataset.originalValue || 'pending';
                }
            })
            .catch(error => {
                select.disabled = false;
                showNotification('Error de conexión', 'error');
                console.error('Error:', error);
            });
        }
        
        function viewAppointment(id) {
            window.location.href = `view.php?id=${id}`;
        }
        
        function editAppointment(id) {
            window.location.href = `edit.php?id=${id}`;
        }
        
        function deleteAppointment(id) {
            if (confirm('¿Estás seguro de eliminar esta cita?')) {
                window.location.href = `delete.php?id=${id}`;
            }
        }
        
        function exportAppointments() {
            const params = new URLSearchParams(window.location.search);
            window.open(`export.php?${params.toString()}`, '_blank');
        }
    </script>
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>
                            <i class="fas fa-calendar-alt me-2"></i>
                            Gestión de Citas
                        </h1>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nueva Cita
                        </a>
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

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['confirmed'] ?? 0; ?></div>
                            <div class="stat-label">Confirmadas</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['cancelled'] ?? 0; ?></div>
                            <div class="stat-label">Canceladas</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo $stats['completed'] ?? 0; ?></div>
                            <div class="stat-label">Completadas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="admin-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" name="status" id="status">
                            <option value="">Todos los estados</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmadas</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Canceladas</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completadas</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date" class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="date" id="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar Cliente</label>
                        <input type="text" class="form-control" name="search" id="search" 
                               placeholder="Nombre, teléfono o email..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Appointments Table -->
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Profesional</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No se encontraron citas</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($appointment['client_name']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($appointment['client_phone']); ?><br>
                                                <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($appointment['client_email']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($appointment['service_name']); ?></strong><br>
                                                <small class="text-muted">
                                                    <?php echo $appointment['service_duration']; ?> min - 
                                                    $<?php echo number_format($appointment['service_price'], 0, ',', '.'); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['professional_name'] ?? 'Sin asignar'); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmada</option>
                                                    <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelada</option>
                                                    <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completada</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view.php?id=<?php echo $appointment['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $appointment['id']; ?>" 
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $appointment['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('¿Estás seguro de eliminar esta cita?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
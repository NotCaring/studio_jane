<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle client deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $client_id = (int)$_GET['id'];
    
    try {
        $pdo->beginTransaction();
        
        // Delete appointments first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE client_id = ?");
        $stmt->execute([$client_id]);
        
        // Delete client
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        
        $pdo->commit();
        $_SESSION['success_message'] = "Cliente eliminado exitosamente";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al eliminar cliente";
    }
    
    header('Location: index.php');
    exit;
}

// Get search parameter
$search = $_GET['search'] ?? '';

// Build query with search
$query = "
    SELECT c.*, 
           COUNT(a.id) as total_appointments,
           MAX(a.appointment_date) as last_appointment,
           SUM(CASE WHEN a.status = 'completed' THEN a.price ELSE 0 END) as total_spent
    FROM clients c
    LEFT JOIN appointments a ON c.id = a.client_id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " GROUP BY c.id ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$clients = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
$total_clients = $stmt->fetch()['total'];

$stmt = $pdo->query("
    SELECT COUNT(*) as active 
    FROM clients c 
    JOIN appointments a ON c.id = a.client_id 
    WHERE a.appointment_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
");
$active_clients = $stmt->fetch()['active'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Studio Jane Admin</title>
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
                            <i class="fas fa-users me-3"></i>
                            Gestión de Clientes
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                                <i class="fas fa-plus me-2"></i>Nuevo Cliente
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

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_clients; ?></div>
                            <div class="stat-label">Total Clientes</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $active_clients; ?></div>
                            <div class="stat-label">Clientes Activos</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo count(array_filter($clients, function($c) { return $c['total_appointments'] > 0; })); ?></div>
                            <div class="stat-label">Con Citas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo count(array_filter($clients, function($c) { return $c['total_appointments'] == 0; })); ?></div>
                            <div class="stat-label">Nuevos</div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="filter-card">
                    <form method="GET" class="search-form">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="form-control search-input" 
                                   name="search" 
                                   placeholder="Buscar por nombre, teléfono o email..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary search-btn">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Clients Table -->
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="table modern-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Citas</th>
                                    <th>Última Cita</th>
                                    <th>Total Gastado</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($clients)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No se encontraron clientes</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($clients as $client): ?>
                                        <tr>
                                            <td>
                                                <div class="client-info">
                                                    <div class="client-avatar">
                                                        <?php echo strtoupper(substr($client['name'], 0, 2)); ?>
                                                    </div>
                                                    <div class="client-details">
                                                        <div class="client-name"><?php echo htmlspecialchars($client['name']); ?></div>
                                                        <?php if ($client['allergies']): ?>
                                                            <div class="client-allergies">
                                                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                                                Alergias registradas
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($client['phone']); ?></div>
                                                    <div><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($client['email']); ?></div>
                                                    <?php if ($client['whatsapp']): ?>
                                                        <div>
                                                            <a href="https://wa.me/<?php echo $client['whatsapp']; ?>" 
                                                               class="whatsapp-link" target="_blank">
                                                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $client['total_appointments']; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($client['last_appointment']): ?>
                                                    <?php echo date('d/m/Y', strtotime($client['last_appointment'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin citas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="amount">$<?php echo number_format($client['total_spent'], 0, ',', '.'); ?></span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($client['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewClient(<?php echo $client['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            onclick="editClient(<?php echo $client['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteClient(<?php echo $client['id']; ?>, '<?php echo htmlspecialchars($client['name']); ?>')">
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

    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="create.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="whatsapp" class="form-label">WhatsApp</label>
                                    <input type="tel" class="form-control" id="whatsapp" name="whatsapp">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="allergies" class="form-label">Alergias o Sensibilidades</label>
                            <textarea class="form-control" id="allergies" name="allergies" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        function viewClient(id) {
            window.location.href = `view.php?id=${id}`;
        }
        
        function editClient(id) {
            window.location.href = `edit.php?id=${id}`;
        }
        
        function deleteClient(id, name) {
            if (confirm(`¿Estás seguro de eliminar al cliente "${name}"? Esta acción eliminará también todas sus citas.`)) {
                window.location.href = `index.php?delete=1&id=${id}`;
            }
        }
    </script>
</body>
</html>
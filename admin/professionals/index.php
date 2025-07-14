<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle professional status toggle
if (isset($_POST['toggle_status']) && isset($_POST['professional_id'])) {
    $professional_id = (int)$_POST['professional_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $pdo->prepare("UPDATE professionals SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $professional_id]);
    
    $_SESSION['success_message'] = "Estado del profesional actualizado";
    header('Location: index.php');
    exit;
}

// Handle availability toggle
if (isset($_POST['toggle_availability']) && isset($_POST['professional_id'])) {
    $professional_id = (int)$_POST['professional_id'];
    $is_available = $_POST['is_available'] == '1' ? 0 : 1;
    
    $stmt = $pdo->prepare("UPDATE professionals SET is_available = ? WHERE id = ?");
    $stmt->execute([$is_available, $professional_id]);
    
    $_SESSION['success_message'] = "Disponibilidad actualizada";
    header('Location: index.php');
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$query = "
    SELECT p.*, 
           COUNT(a.id) as total_appointments,
           SUM(CASE WHEN a.status = 'completed' THEN a.price ELSE 0 END) as total_revenue
    FROM professionals p
    LEFT JOIN appointments a ON p.id = a.professional_id
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.email LIKE ? OR p.specialties LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " GROUP BY p.id ORDER BY p.name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$professionals = $stmt->fetchAll();

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM professionals WHERE status = 'active'");
$stats['active'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM professionals WHERE is_available = 1 AND status = 'active'");
$stats['available'] = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Profesionales - Studio Jane Admin</title>
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
                            <i class="fas fa-user-tie me-3"></i>
                            Gestión de Profesionales
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProfessionalModal">
                                <i class="fas fa-plus me-2"></i>Nuevo Profesional
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

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['active']; ?></div>
                            <div class="stat-label">Profesionales Activos</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['available']; ?></div>
                            <div class="stat-label">Disponibles</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo array_sum(array_column($professionals, 'total_appointments')); ?></div>
                            <div class="stat-label">Total Citas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">$<?php echo number_format(array_sum(array_column($professionals, 'total_revenue')), 0, ',', '.'); ?></div>
                            <div class="stat-label">Ingresos Generados</div>
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
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Activos</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                                </select>
                            </div>
                            
                            <div class="col-md-7">
                                <div class="search-input-group">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" name="search" 
                                           placeholder="Buscar profesionales..." value="<?php echo htmlspecialchars($search); ?>">
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

                <!-- Professionals Grid -->
                <div class="professionals-grid">
                    <?php if (empty($professionals)): ?>
                        <div class="empty-state-card">
                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                            <h5>No se encontraron profesionales</h5>
                            <p class="text-muted">Agrega tu primer profesional para comenzar</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProfessionalModal">
                                <i class="fas fa-plus me-2"></i>Agregar Profesional
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($professionals as $professional): ?>
                            <div class="professional-card hover-lift">
                                <div class="professional-image">
                                    <?php if ($professional['image']): ?>
                                        <img src="<?php echo htmlspecialchars($professional['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($professional['name']); ?>">
                                    <?php else: ?>
                                        <div class="professional-placeholder">
                                            <?php echo strtoupper(substr($professional['name'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="professional-badges">
                                        <?php if ($professional['is_available']): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check me-1"></i>Disponible
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-pause me-1"></i>No Disponible
                                            </span>
                                        <?php endif; ?>
                                        
                                        <span class="badge badge-<?php echo $professional['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $professional['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="professional-content">
                                    <div class="professional-header">
                                        <h5 class="professional-name"><?php echo htmlspecialchars($professional['name']); ?></h5>
                                        <div class="professional-contact">
                                            <?php if ($professional['email']): ?>
                                                <div><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($professional['email']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($professional['phone']): ?>
                                                <div><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($professional['phone']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($professional['specialties']): ?>
                                        <div class="professional-specialties">
                                            <strong>Especialidades:</strong>
                                            <p><?php echo htmlspecialchars($professional['specialties']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="professional-stats">
                                        <div class="stat-item">
                                            <span class="stat-value"><?php echo $professional['total_appointments']; ?></span>
                                            <span class="stat-label">Citas</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value">$<?php echo number_format($professional['total_revenue'], 0, ',', '.'); ?></span>
                                            <span class="stat-label">Ingresos</span>
                                        </div>
                                    </div>
                                    
                                    <div class="professional-actions">
                                        <div class="action-toggles">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="professional_id" value="<?php echo $professional['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $professional['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" 
                                                        class="btn btn-sm btn-outline-<?php echo $professional['status'] === 'active' ? 'warning' : 'success'; ?>">
                                                    <i class="fas fa-<?php echo $professional['status'] === 'active' ? 'pause' : 'play'; ?> me-1"></i>
                                                    <?php echo $professional['status'] === 'active' ? 'Desactivar' : 'Activar'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="professional_id" value="<?php echo $professional['id']; ?>">
                                                <input type="hidden" name="is_available" value="<?php echo $professional['is_available']; ?>">
                                                <button type="submit" name="toggle_availability" 
                                                        class="btn btn-sm btn-outline-<?php echo $professional['is_available'] ? 'warning' : 'success'; ?>">
                                                    <i class="fas fa-<?php echo $professional['is_available'] ? 'pause' : 'check'; ?> me-1"></i>
                                                    <?php echo $professional['is_available'] ? 'No Disponible' : 'Disponible'; ?>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editProfessional(<?php echo $professional['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteProfessional(<?php echo $professional['id']; ?>, '<?php echo htmlspecialchars($professional['name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Professional Modal -->
    <div class="modal fade" id="addProfessionalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Profesional
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="create.php" enctype="multipart/form-data">
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
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Foto del Profesional</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="specialties" class="form-label">Especialidades</label>
                            <textarea class="form-control" id="specialties" name="specialties" rows="3" 
                                      placeholder="Ej: Manicure, Pedicure, Nail Art"></textarea>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" checked>
                            <label class="form-check-label" for="is_available">
                                Disponible para citas
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Crear Profesional
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        function editProfessional(id) {
            window.location.href = `edit.php?id=${id}`;
        }
        
        function deleteProfessional(id, name) {
            if (confirm(`¿Estás seguro de eliminar al profesional "${name}"?`)) {
                window.location.href = `delete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>

<style>
.professionals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.professional-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
}

.professional-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
    border-color: rgba(102, 126, 234, 0.3);
}

.professional-image {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: var(--hover-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.professional-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.professional-card:hover .professional-image img {
    transform: scale(1.05);
}

.professional-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: white;
}

.professional-badges {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.professional-content {
    padding: 1.5rem;
}

.professional-header {
    margin-bottom: 1rem;
}

.professional-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.professional-contact {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.professional-contact div {
    margin-bottom: 0.25rem;
}

.professional-specialties {
    margin-bottom: 1rem;
    padding: 1rem;
    background: var(--hover-bg);
    border-radius: var(--border-radius);
}

.professional-specialties strong {
    color: var(--text-primary);
    font-size: 0.875rem;
}

.professional-specialties p {
    margin: 0.5rem 0 0 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.professional-stats {
    display: flex;
    justify-content: space-around;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--hover-bg);
    border-radius: var(--border-radius);
}

.professional-stats .stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.professional-stats .stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

.professional-stats .stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.professional-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.action-toggles {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-toggles .btn {
    flex: 1;
    min-width: 120px;
}

@media (max-width: 768px) {
    .professionals-grid {
        grid-template-columns: 1fr;
    }
    
    .action-toggles {
        flex-direction: column;
    }
    
    .action-toggles .btn {
        min-width: auto;
    }
}
</style>
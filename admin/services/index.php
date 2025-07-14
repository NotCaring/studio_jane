<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle service status toggle
if (isset($_POST['toggle_status']) && isset($_POST['service_id'])) {
    $service_id = (int)$_POST['service_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $pdo->prepare("UPDATE services SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $service_id]);
    
    $_SESSION['success_message'] = "Estado del servicio actualizado";
    header('Location: index.php');
    exit;
}

// Handle featured toggle
if (isset($_POST['toggle_featured']) && isset($_POST['service_id'])) {
    $service_id = (int)$_POST['service_id'];
    $is_featured = $_POST['is_featured'] == '1' ? 0 : 1;
    
    $stmt = $pdo->prepare("UPDATE services SET is_featured = ? WHERE id = ?");
    $stmt->execute([$is_featured, $service_id]);
    
    $_SESSION['success_message'] = "Servicio destacado actualizado";
    header('Location: index.php');
    exit;
}

// Get filter parameters
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Get categories for filter
$stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll();

// Build query with filters
$query = "
    SELECT s.*, c.name as category_name
    FROM services s
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE 1=1
";

$params = [];

if ($category_filter) {
    $query .= " AND s.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $query .= " AND s.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $query .= " AND (s.name LIKE ? OR s.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY c.name, s.name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE status = 'active'");
$stats['active'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE is_featured = 1 AND status = 'active'");
$stats['featured'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT AVG(price) as avg_price FROM services WHERE status = 'active'");
$stats['avg_price'] = $stmt->fetch()['avg_price'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios - Studio Jane Admin</title>
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
                            <i class="fas fa-spa me-3"></i>
                            Gestión de Servicios
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                <i class="fas fa-tags me-2"></i>Categorías
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                <i class="fas fa-plus me-2"></i>Nuevo Servicio
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
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-spa"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['active']; ?></div>
                            <div class="stat-label">Servicios Activos</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['featured']; ?></div>
                            <div class="stat-label">Destacados</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo count($categories); ?></div>
                            <div class="stat-label">Categorías</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">$<?php echo number_format($stats['avg_price'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Precio Promedio</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" class="filter-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Todos los estados</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Activos</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                                </select>
                            </div>
                            
                            <div class="col-md-5">
                                <div class="search-input-group">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" name="search" 
                                           placeholder="Buscar servicios..." value="<?php echo htmlspecialchars($search); ?>">
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

                <!-- Services Grid -->
                <div class="services-grid">
                    <?php if (empty($services)): ?>
                        <div class="empty-state-card">
                            <i class="fas fa-spa fa-3x text-muted mb-3"></i>
                            <h5>No se encontraron servicios</h5>
                            <p class="text-muted">Crea tu primer servicio para comenzar</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                <i class="fas fa-plus me-2"></i>Crear Servicio
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <div class="service-image">
                                    <img src="<?php echo htmlspecialchars($service['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($service['name']); ?>">
                                    
                                    <div class="service-badges">
                                        <?php if ($service['is_featured']): ?>
                                            <span class="badge badge-featured">
                                                <i class="fas fa-star me-1"></i>Destacado
                                            </span>
                                        <?php endif; ?>
                                        
                                        <span class="badge badge-<?php echo $service['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $service['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="service-content">
                                    <div class="service-header">
                                        <h5 class="service-name"><?php echo htmlspecialchars($service['name']); ?></h5>
                                        <span class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></span>
                                    </div>
                                    
                                    <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                                    
                                    <div class="service-details">
                                        <div class="service-price">$<?php echo number_format($service['price'], 0, ',', '.'); ?></div>
                                        <div class="service-duration">
                                            <i class="fas fa-clock me-1"></i><?php echo $service['duration']; ?> min
                                        </div>
                                    </div>
                                    
                                    <div class="service-actions">
                                        <div class="action-toggles">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $service['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" 
                                                        class="btn btn-sm btn-outline-<?php echo $service['status'] === 'active' ? 'warning' : 'success'; ?>">
                                                    <i class="fas fa-<?php echo $service['status'] === 'active' ? 'pause' : 'play'; ?> me-1"></i>
                                                    <?php echo $service['status'] === 'active' ? 'Desactivar' : 'Activar'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                <input type="hidden" name="is_featured" value="<?php echo $service['is_featured']; ?>">
                                                <button type="submit" name="toggle_featured" 
                                                        class="btn btn-sm btn-outline-<?php echo $service['is_featured'] ? 'warning' : 'primary'; ?>">
                                                    <i class="fas fa-star me-1"></i>
                                                    <?php echo $service['is_featured'] ? 'Quitar' : 'Destacar'; ?>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editService(<?php echo $service['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>')">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        function editService(id) {
            window.location.href = `edit.php?id=${id}`;
        }
        
        function deleteService(id, name) {
            if (confirm(`¿Estás seguro de eliminar el servicio "${name}"?`)) {
                window.location.href = `delete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
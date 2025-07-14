<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle gallery item deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $item_id = (int)$_GET['id'];
    
    try {
        // Get image path before deletion
        $stmt = $pdo->prepare("SELECT image FROM gallery WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if ($item) {
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
            $stmt->execute([$item_id]);
            
            // Delete image file if exists
            if ($item['image'] && file_exists('../../' . $item['image'])) {
                unlink('../../' . $item['image']);
            }
            
            $_SESSION['success_message'] = "Elemento eliminado exitosamente";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error al eliminar elemento";
    }
    
    header('Location: index.php');
    exit;
}

// Handle featured toggle
if (isset($_POST['toggle_featured']) && isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $is_featured = $_POST['is_featured'] == '1' ? 0 : 1;
    
    $stmt = $pdo->prepare("UPDATE gallery SET is_featured = ? WHERE id = ?");
    $stmt->execute([$is_featured, $item_id]);
    
    $_SESSION['success_message'] = "Estado destacado actualizado";
    header('Location: index.php');
    exit;
}

// Get filter parameters
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query with filters
$query = "SELECT * FROM gallery WHERE 1=1";
$params = [];

if ($category_filter) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY is_featured DESC, created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$gallery_items = $stmt->fetchAll();

// Get categories
$stmt = $pdo->query("SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL ORDER BY category");
$categories = $stmt->fetchAll();

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery WHERE status = 'active'");
$stats['active'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery WHERE is_featured = 1 AND status = 'active'");
$stats['featured'] = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Galería - Studio Jane Admin</title>
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
                            <i class="fas fa-images me-3"></i>
                            Gestión de Galería
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
                                <i class="fas fa-plus me-2"></i>Agregar Imagen
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
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['active']; ?></div>
                            <div class="stat-label">Imágenes Activas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['featured']; ?></div>
                            <div class="stat-label">Destacadas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo count($categories); ?></div>
                            <div class="stat-label">Categorías</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo count($gallery_items); ?></div>
                            <div class="stat-label">Total Elementos</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" class="filter-form">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <select class="form-select" name="category">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['category']); ?>" 
                                                <?php echo $category_filter === $category['category'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <option value="">Todos los estados</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Activos</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Filtrar
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Gallery Grid -->
                <div class="gallery-grid">
                    <?php if (empty($gallery_items)): ?>
                        <div class="empty-state-card">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <h5>No hay imágenes en la galería</h5>
                            <p class="text-muted">Agrega tu primera imagen para comenzar</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
                                <i class="fas fa-plus me-2"></i>Agregar Imagen
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($gallery_items as $item): ?>
                            <div class="gallery-card hover-lift">
                                <div class="gallery-image">
                                    <img src="../../<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         onclick="viewImage('../../<?php echo htmlspecialchars($item['image']); ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                    
                                    <div class="gallery-overlay">
                                        <div class="gallery-actions">
                                            <button class="btn btn-sm btn-light" 
                                                    onclick="viewImage('../../<?php echo htmlspecialchars($item['image']); ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="editGalleryItem(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="deleteGalleryItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="gallery-badges">
                                        <?php if ($item['is_featured']): ?>
                                            <span class="badge badge-featured">
                                                <i class="fas fa-star me-1"></i>Destacada
                                            </span>
                                        <?php endif; ?>
                                        
                                        <span class="badge badge-<?php echo $item['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $item['status'] === 'active' ? 'Activa' : 'Inactiva'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="gallery-content">
                                    <h5 class="gallery-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                    
                                    <?php if ($item['description']): ?>
                                        <p class="gallery-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['category']): ?>
                                        <div class="gallery-category">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($item['category']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="gallery-meta">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="gallery-item-actions">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="is_featured" value="<?php echo $item['is_featured']; ?>">
                                            <button type="submit" name="toggle_featured" 
                                                    class="btn btn-sm btn-outline-<?php echo $item['is_featured'] ? 'warning' : 'primary'; ?>">
                                                <i class="fas fa-star me-1"></i>
                                                <?php echo $item['is_featured'] ? 'Quitar' : 'Destacar'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Gallery Modal -->
    <div class="modal fade" id="addGalleryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Agregar Imagen a Galería
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="create.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Título *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Categoría</label>
                                    <input type="text" class="form-control" id="category" name="category" 
                                           placeholder="Ej: Uñas, Rostro, Pestañas">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Imagen *</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                            <label class="form-check-label" for="is_featured">
                                Marcar como imagen destacada
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Agregar a Galería
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="imageViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageViewTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="imageViewImg" src="" alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        function viewImage(src, title) {
            document.getElementById('imageViewImg').src = src;
            document.getElementById('imageViewTitle').textContent = title;
            new bootstrap.Modal(document.getElementById('imageViewModal')).show();
        }
        
        function editGalleryItem(id) {
            window.location.href = `edit.php?id=${id}`;
        }
        
        function deleteGalleryItem(id, title) {
            if (confirm(`¿Estás seguro de eliminar la imagen "${title}"?`)) {
                window.location.href = `index.php?delete=1&id=${id}`;
            }
        }
    </script>
</body>
</html>

<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.gallery-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    transition: var(--transition);
}

.gallery-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
    border-color: rgba(102, 126, 234, 0.3);
}

.gallery-image {
    position: relative;
    height: 250px;
    overflow: hidden;
    cursor: pointer;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.gallery-card:hover .gallery-image img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.gallery-card:hover .gallery-overlay {
    opacity: 1;
}

.gallery-actions {
    display: flex;
    gap: 0.5rem;
}

.gallery-badges {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.gallery-content {
    padding: 1.5rem;
}

.gallery-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.gallery-description {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
    line-height: 1.4;
}

.gallery-category {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
}

.gallery-meta {
    margin-bottom: 1rem;
}

.gallery-item-actions {
    text-align: center;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}
</style>
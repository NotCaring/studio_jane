<?php
session_start();
require_once 'config/database.php';

// Get all categories with their services
$stmt = $pdo->query("
    SELECT c.*, 
           COUNT(s.id) as service_count
    FROM categories c 
    LEFT JOIN services s ON c.id = s.category_id AND s.status = 'active'
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY c.name
");
$categories = $stmt->fetchAll();

// Get all services grouped by category
$services_by_category = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("
        SELECT * FROM services 
        WHERE category_id = ? AND status = 'active'
        ORDER BY name
    ");
    $stmt->execute([$category['id']]);
    $services_by_category[$category['id']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - Studio Jane</title>
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
                    <h1 class="hero-title">Nuestros Servicios</h1>
                    <p class="hero-subtitle">Descubre todos los tratamientos que tenemos para ti</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5">
        <div class="container">
            <?php foreach ($categories as $category): ?>
                <?php if (!empty($services_by_category[$category['id']])): ?>
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="category-header text-center mb-5">
                                <h2 class="section-title"><?php echo htmlspecialchars($category['name']); ?></h2>
                                <p class="section-subtitle"><?php echo htmlspecialchars($category['description']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <?php foreach ($services_by_category[$category['id']] as $service): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="service-card">
                                    <div class="service-image">
                                        <img src="<?php echo htmlspecialchars($service['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($service['name']); ?>" 
                                             class="img-fluid">
                                        <?php if ($service['is_featured']): ?>
                                            <div class="featured-badge">
                                                <i class="fas fa-star"></i> Destacado
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="service-content">
                                        <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                                        <div class="service-details">
                                            <div class="service-price">
                                                $<?php echo number_format($service['price'], 0, ',', '.'); ?>
                                            </div>
                                            <div class="service-duration">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo $service['duration']; ?> min
                                            </div>
                                        </div>
                                        <div class="service-actions mt-3">
                                            <a href="reservar.php?service_id=<?php echo $service['id']; ?>" 
                                               class="btn btn-primary btn-sm me-2">
                                                <i class="fas fa-calendar-alt me-1"></i>Reservar
                                            </a>
                                            <a href="https://wa.me/<?php echo SITE_WHATSAPP; ?>?text=Hola, me interesa el servicio de <?php echo urlencode($service['name']); ?>" 
                                               class="btn btn-success btn-sm whatsapp-btn">
                                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="section-title">¿Lista para tu transformación?</h2>
                    <p class="section-subtitle">Agenda tu cita ahora y vive la experiencia Studio Jane</p>
                    <div class="cta-buttons">
                        <a href="reservar.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-calendar-alt me-2"></i>Reservar Cita
                        </a>
                        <a href="contacto.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-phone me-2"></i>Contactar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<style>
.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--gradient-secondary);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.service-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.service-actions {
    display: flex;
    gap: 0.5rem;
}

.category-header {
    margin-bottom: 3rem;
}

.cta-buttons {
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .service-actions {
        flex-direction: column;
    }
    
    .service-actions .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .cta-buttons .btn {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }
}
</style>
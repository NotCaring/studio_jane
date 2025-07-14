<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Jane - Tu belleza, nuestra pasión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="hero-title">Studio Jane</h1>
                    <p class="hero-subtitle">Tu belleza, nuestra pasión</p>
                    <p class="hero-description">
                        Descubre la experiencia de belleza más completa en nuestro estudio. 
                        Profesionales expertos, productos de calidad y un ambiente relajante te esperan.
                    </p>
                    <div class="hero-buttons">
                        <a href="reservar.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-calendar-alt me-2"></i>Reservar Ahora
                        </a>
                        <a href="servicios.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-eye me-2"></i>Ver Servicios
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="https://images.pexels.com/photos/3985360/pexels-photo-3985360.jpeg" 
                             alt="Studio Jane" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios Destacados -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Servicios Destacados</h2>
                    <p class="section-subtitle">Los más populares entre nuestras clientas</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://images.pexels.com/photos/3985322/pexels-photo-3985322.jpeg" 
                                 alt="Manicure" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h4>Manicure Completa</h4>
                            <p>Cuidado completo de uñas con esmaltado profesional</p>
                            <div class="service-price">$25.000</div>
                            <div class="service-duration">
                                <i class="fas fa-clock me-1"></i>60 min
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://images.pexels.com/photos/3985329/pexels-photo-3985329.jpeg" 
                                 alt="Facial" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h4>Limpieza Facial</h4>
                            <p>Tratamiento profundo para una piel radiante</p>
                            <div class="service-price">$45.000</div>
                            <div class="service-duration">
                                <i class="fas fa-clock me-1"></i>90 min
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="https://images.pexels.com/photos/3985334/pexels-photo-3985334.jpeg" 
                                 alt="Extensiones" class="img-fluid">
                        </div>
                        <div class="service-content">
                            <h4>Extensiones de Pestañas</h4>
                            <p>Mirada perfecta con pestañas naturales</p>
                            <div class="service-price">$65.000</div>
                            <div class="service-duration">
                                <i class="fas fa-clock me-1"></i>120 min
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="servicios.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-list me-2"></i>Ver Todos los Servicios
                </a>
            </div>
        </div>
    </section>

    <!-- Opiniones -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Lo que dicen nuestras clientas</h2>
                    <p class="section-subtitle">Testimonios reales de quienes confían en nosotros</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="review-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="review-text">
                            "Excelente atención y resultados increíbles. 
                            Mi manicure duró perfecto por más de 2 semanas."
                        </p>
                        <div class="review-author">
                            <strong>María González</strong>
                            <small class="text-muted">Manicure Completa</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="review-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="review-text">
                            "El lugar es hermoso y muy limpio. 
                            Las profesionales son expertas y muy amables."
                        </p>
                        <div class="review-author">
                            <strong>Ana Rodríguez</strong>
                            <small class="text-muted">Limpieza Facial</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="review-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="review-text">
                            "Mis pestañas quedaron perfectas. 
                            Definitivamente vuelvo para mantener el look."
                        </p>
                        <div class="review-author">
                            <strong>Sofía Martínez</strong>
                            <small class="text-muted">Extensiones de Pestañas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Galería -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Galería de Trabajos</h2>
                    <p class="section-subtitle">Algunos de nuestros trabajos más recientes</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="https://images.pexels.com/photos/3997993/pexels-photo-3997993.jpeg" 
                             alt="Trabajo 1" class="img-fluid rounded">
                        <div class="gallery-overlay">
                            <div class="gallery-content">
                                <h5>Nail Art</h5>
                                <p>Diseño personalizado</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="https://images.pexels.com/photos/3985327/pexels-photo-3985327.jpeg" 
                             alt="Trabajo 2" class="img-fluid rounded">
                        <div class="gallery-overlay">
                            <div class="gallery-content">
                                <h5>Tratamiento Facial</h5>
                                <p>Piel radiante</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="gallery-item">
                        <img src="https://images.pexels.com/photos/3985333/pexels-photo-3985333.jpeg" 
                             alt="Trabajo 3" class="img-fluid rounded">
                        <div class="gallery-overlay">
                            <div class="gallery-content">
                                <h5>Pestañas Volumen</h5>
                                <p>Mirada impactante</p>
                            </div>
                        </div>
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
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - Studio Jane</title>
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
                    <h1 class="hero-title">Sobre Nosotros</h1>
                    <p class="hero-subtitle">Conoce la historia detrás de Studio Jane</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title">Nuestra Historia</h2>
                    <p class="lead">
                        Studio Jane nació en 2020 con una visión clara: crear un espacio donde la belleza 
                        y el bienestar se encuentren en perfecta armonía.
                    </p>
                    <p>
                        Fundado por Jane Rodríguez, una apasionada profesional en el mundo de la belleza 
                        con más de 10 años de experiencia, nuestro estudio se ha convertido en un referente 
                        de calidad y excelencia en el cuidado personal.
                    </p>
                    <p>
                        Creemos que cada persona es única y merece un tratamiento personalizado. 
                        Por eso, nos especializamos en ofrecer servicios de alta calidad adaptados 
                        a las necesidades individuales de cada cliente.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="https://images.pexels.com/photos/3985360/pexels-photo-3985360.jpeg" 
                             alt="Studio Jane Interior" 
                             class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Nuestros Valores</h2>
                    <p class="section-subtitle">Los principios que guían nuestro trabajo diario</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="value-card text-center">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Pasión</h4>
                        <p>
                            Amamos lo que hacemos y esa pasión se refleja en cada servicio 
                            que ofrecemos a nuestras clientas.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="value-card text-center">
                        <div class="value-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h4>Excelencia</h4>
                        <p>
                            Nos esforzamos por superar las expectativas, utilizando 
                            productos de primera calidad y técnicas actualizadas.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="value-card text-center">
                        <div class="value-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h4>Personalización</h4>
                        <p>
                            Cada cliente es único, por eso adaptamos nuestros servicios 
                            a las necesidades específicas de cada persona.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Nuestro Equipo</h2>
                    <p class="section-subtitle">Profesionales expertos al servicio de tu belleza</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg" 
                                 alt="Jane Rodríguez" 
                                 class="img-fluid">
                        </div>
                        <div class="team-content">
                            <h4>Jane Rodríguez</h4>
                            <p class="team-role">Fundadora y Directora</p>
                            <p class="team-specialties">
                                <strong>Especialidades:</strong> Nail Art, Diseño de Uñas, Gestión
                            </p>
                            <p>
                                Con más de 10 años de experiencia, Jane es reconocida por su 
                                creatividad y atención al detalle en cada trabajo.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg" 
                                 alt="María González" 
                                 class="img-fluid">
                        </div>
                        <div class="team-content">
                            <h4>María González</h4>
                            <p class="team-role">Especialista en Tratamientos Faciales</p>
                            <p class="team-specialties">
                                <strong>Especialidades:</strong> Limpieza Facial, Anti-edad, Hidratación
                            </p>
                            <p>
                                Experta en cuidado de la piel con certificaciones internacionales 
                                en tratamientos faciales y dermocosmética.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg" 
                                 alt="Ana López" 
                                 class="img-fluid">
                        </div>
                        <div class="team-content">
                            <h4>Ana López</h4>
                            <p class="team-role">Especialista en Pestañas</p>
                            <p class="team-specialties">
                                <strong>Especialidades:</strong> Extensiones, Lifting, Volumen
                            </p>
                            <p>
                                Técnica certificada en extensiones de pestañas con más de 5 años 
                                de experiencia creando miradas perfectas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Certifications -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Certificaciones</h2>
                    <p class="section-subtitle">Respaldados por las mejores instituciones</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="certification-card">
                        <div class="cert-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h5>Certificación Internacional en Nail Art</h5>
                        <p>Instituto de Belleza Internacional - 2022</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="certification-card">
                        <div class="cert-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h5>Especialización en Tratamientos Faciales</h5>
                        <p>Academia de Cosmetología Avanzada - 2021</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="certification-card">
                        <div class="cert-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h5>Certificación en Extensiones de Pestañas</h5>
                        <p>Lash Academy International - 2020</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="section-title">¿Lista para conocernos?</h2>
                    <p class="section-subtitle">Te esperamos en Studio Jane para vivir una experiencia única</p>
                    <div class="cta-buttons">
                        <a href="reservar.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-calendar-alt me-2"></i>Reservar Cita
                        </a>
                        <a href="contacto.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-map-marker-alt me-2"></i>Visitarnos
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
.value-card {
    background: var(--white);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    height: 100%;
    transition: transform 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
}

.value-icon {
    background: var(--gradient-primary);
    color: var(--white);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
}

.team-card {
    background: var(--white);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
    height: 100%;
}

.team-card:hover {
    transform: translateY(-5px);
}

.team-image {
    height: 300px;
    overflow: hidden;
}

.team-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.team-card:hover .team-image img {
    transform: scale(1.05);
}

.team-content {
    padding: 1.5rem;
}

.team-role {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 1rem;
}

.team-specialties {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.certification-card {
    background: var(--white);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.certification-card:hover {
    transform: translateY(-5px);
}

.cert-icon {
    background: var(--gradient-secondary);
    color: var(--white);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.certification-card h5 {
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.certification-card p {
    color: var(--text-light);
    font-size: 0.9rem;
}

.about-image {
    position: relative;
}

.about-image::before {
    content: '';
    position: absolute;
    top: -20px;
    left: -20px;
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    border-radius: 15px;
    z-index: -1;
}

.cta-buttons {
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .cta-buttons .btn {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }
    
    .team-card {
        margin-bottom: 2rem;
    }
}
</style>
<?php
session_start();
require_once 'config/database.php';

// Handle contact form submission
if ($_POST) {
    $errors = [];
    
    // Validate required fields
    $required_fields = ['name', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . str_replace('_', ' ', $field) . " es obligatorio";
        }
    }
    
    // Validate email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    }
    
    if (empty($errors)) {
        // Here you would typically send the email
        // For now, we'll just show a success message
        $_SESSION['success_message'] = "¡Mensaje enviado exitosamente! Te contactaremos pronto.";
        header('Location: contacto.php?success=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Studio Jane</title>
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
                    <h1 class="hero-title">Contacto</h1>
                    <p class="hero-subtitle">Estamos aquí para ayudarte</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Info -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Dirección</h5>
                        <p>Calle 123 #45-67<br>Bogotá, Colombia</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h5>Teléfono</h5>
                        <p>+57 300 123 4567</p>
                        <a href="tel:+573001234567" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-1"></i>Llamar
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h5>WhatsApp</h5>
                        <p>+57 300 123 4567</p>
                        <a href="https://wa.me/573001234567" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp me-1"></i>Escribir
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5>Email</h5>
                        <p>info@studiojane.com</p>
                        <a href="mailto:info@studiojane.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Enviar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Hours -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Horarios de Atención</h2>
                    <p class="section-subtitle">Te esperamos en estos horarios</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="hours-card">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="hours-item">
                                    <div class="day">Lunes - Viernes</div>
                                    <div class="time">9:00 AM - 7:00 PM</div>
                                </div>
                                <div class="hours-item">
                                    <div class="day">Sábado</div>
                                    <div class="time">9:00 AM - 6:00 PM</div>
                                </div>
                                <div class="hours-item">
                                    <div class="day">Domingo</div>
                                    <div class="time">Cerrado</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hours-note">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Importante:</strong> Recomendamos agendar cita previa 
                                    para garantizar tu atención en el horario deseado.
                                </div>
                                <div class="hours-cta">
                                    <a href="reservar.php" class="btn btn-primary">
                                        <i class="fas fa-calendar-alt me-1"></i>Reservar Ahora
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Envíanos un Mensaje</h2>
                    <p class="section-subtitle">¿Tienes alguna pregunta? Estamos aquí para ayudarte</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="contact-form-card">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nombre Completo *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa tu nombre
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                               required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa un email válido
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Teléfono</label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Asunto *</label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="">Selecciona un asunto</option>
                                            <option value="reserva" <?php echo ($_POST['subject'] ?? '') == 'reserva' ? 'selected' : ''; ?>>Reserva de cita</option>
                                            <option value="consulta" <?php echo ($_POST['subject'] ?? '') == 'consulta' ? 'selected' : ''; ?>>Consulta de servicios</option>
                                            <option value="reclamo" <?php echo ($_POST['subject'] ?? '') == 'reclamo' ? 'selected' : ''; ?>>Reclamo o sugerencia</option>
                                            <option value="otro" <?php echo ($_POST['subject'] ?? '') == 'otro' ? 'selected' : ''; ?>>Otro</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor selecciona un asunto
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Mensaje *</label>
                                <textarea class="form-control" 
                                          id="message" 
                                          name="message" 
                                          rows="5" 
                                          placeholder="Escribe tu mensaje aquí..." 
                                          required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                <div class="invalid-feedback">
                                    Por favor escribe tu mensaje
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Enviar Mensaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Nuestra Ubicación</h2>
                    <p class="section-subtitle">Fácil acceso y parqueadero disponible</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3976.7395488229745!2d-74.07884908573867!3d4.6097102964063165!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3f9bfd2da6cb29%3A0x239d635520a33914!2sBogot%C3%A1%2C%20Colombia!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                            width="100%" 
                            height="400" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="section-title">Síguenos en Redes Sociales</h2>
                    <p class="section-subtitle">Mantente al día con nuestras novedades y trabajos</p>
                    
                    <div class="social-buttons">
                        <a href="#" class="social-btn facebook">
                            <i class="fab fa-facebook-f"></i>
                            <span>Facebook</span>
                        </a>
                        <a href="#" class="social-btn instagram">
                            <i class="fab fa-instagram"></i>
                            <span>Instagram</span>
                        </a>
                        <a href="https://wa.me/573001234567" class="social-btn whatsapp">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>

<style>
.contact-card {
    background: var(--white);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
    height: 100%;
    transition: transform 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-5px);
}

.contact-icon {
    background: var(--gradient-primary);
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

.contact-card h5 {
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.contact-card p {
    color: var(--text-light);
    margin-bottom: 1rem;
}

.hours-card {
    background: var(--white);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #E9ECEF;
}

.hours-item:last-child {
    border-bottom: none;
}

.day {
    font-weight: 600;
    color: var(--text-dark);
}

.time {
    color: var(--text-light);
}

.hours-note {
    background: var(--bg-light);
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    color: var(--text-light);
}

.hours-cta {
    text-align: center;
}

.contact-form-card {
    background: var(--white);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.map-container {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.social-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.social-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-radius: 50px;
    text-decoration: none;
    color: var(--white);
    font-weight: 600;
    transition: all 0.3s ease;
}

.social-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    color: var(--white);
}

.social-btn.facebook {
    background: #1877F2;
}

.social-btn.instagram {
    background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF, #515BD4);
}

.social-btn.whatsapp {
    background: #25D366;
}

@media (max-width: 768px) {
    .social-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .social-btn {
        width: 200px;
        justify-content: center;
    }
    
    .contact-card {
        margin-bottom: 2rem;
    }
}
</style>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS (Animate On Scroll)
    initializeScrollAnimations();
    
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
        } else {
            navbar.classList.remove('scrolled');
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        }
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                showNotification('Por favor completa todos los campos requeridos', 'error');
            }
            form.classList.add('was-validated');
        });
    });

    // WhatsApp button functionality
    document.querySelectorAll('.whatsapp-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const message = encodeURIComponent('Hola, me gustaría hacer una reserva en Studio Jane');
            const whatsappUrl = `https://wa.me/573001234567?text=${message}`;
            window.open(whatsappUrl, '_blank');
        });
    });
    
    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.hero-section');
        if (parallax) {
            const speed = scrolled * 0.5;
            parallax.style.transform = `translateY(${speed}px)`;
        }
    });
    
    // Counter animation for statistics
    animateCounters();
    
    // Image lazy loading
    initializeLazyLoading();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Preloader
    hidePreloader();
});

// Initialize scroll animations
function initializeScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-on-scroll', 'animated');
                
                // Animate counters when they come into view
                if (entry.target.classList.contains('stat-number')) {
                    animateCounter(entry.target);
                }
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.service-card, .review-card, .gallery-item, .contact-card, .stat-card').forEach(element => {
        element.classList.add('animate-on-scroll');
        observer.observe(element);
    });
}

// Animate counters
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(counter => {
        const target = parseInt(counter.textContent);
        if (target > 0) {
            animateCounter(counter, target);
        }
    });
}

function animateCounter(element, target = null) {
    if (!target) {
        target = parseInt(element.textContent);
    }
    
    let current = 0;
    const increment = target / 50;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 30);
}

// Lazy loading for images
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Initialize tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Hide preloader
function hidePreloader() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 300);
        }, 1000);
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification-toast`;
    notification.innerHTML = `
        <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Reservation form specific functions
function updateAvailableSlots() {
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    
    if (!dateInput || !timeSelect) return;
    
    const selectedDate = dateInput.value;
    if (!selectedDate) return;
    
    // Clear existing options
    timeSelect.innerHTML = '<option value="">Selecciona una hora</option>';
    
    // Generate time slots (this would normally come from the backend)
    const slots = [
        '09:00', '10:00', '11:00', '12:00', 
        '14:00', '15:00', '16:00', '17:00', '18:00'
    ];
    
    slots.forEach(slot => {
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = slot;
        timeSelect.appendChild(option);
    });
    
    // Here you would make an AJAX call to check availability
    // and disable booked slots
}

// Service selection functionality
function selectService(serviceId, serviceName, servicePrice) {
    const serviceInput = document.getElementById('service_id');
    const serviceDisplay = document.getElementById('selected_service');
    
    if (serviceInput) {
        serviceInput.value = serviceId;
    }
    
    if (serviceDisplay) {
        serviceDisplay.innerHTML = `
            <strong>Servicio seleccionado:</strong> ${serviceName} - $${servicePrice}
        `;
    }
}

// Gallery modal functionality
function openGalleryModal(imageSrc, title, description) {
    const modal = document.getElementById('galleryModal');
    const modalImg = modal.querySelector('.modal-body img');
    const modalTitle = modal.querySelector('.modal-title');
    const modalDescription = modal.querySelector('.modal-body p');
    
    modalImg.src = imageSrc;
    modalTitle.textContent = title;
    modalDescription.textContent = description;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Admin panel functions
function confirmDelete(itemName) {
    return confirm(`¿Estás seguro de que quieres eliminar "${itemName}"?`);
}

// Smooth page transitions
function initializePageTransitions() {
    const links = document.querySelectorAll('a[href]:not([href^="#"]):not([target="_blank"])');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                window.location.href = href;
            }, 300);
        });
    });
}

// Performance optimization
function optimizePerformance() {
    // Debounce scroll events
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(handleScroll, 10);
    });
    
    // Preload critical resources
    preloadCriticalResources();
}

function handleScroll() {
    // Handle scroll events here
    const scrolled = window.pageYOffset;
    
    // Update navbar
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (scrolled > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
}

function preloadCriticalResources() {
    // Preload critical CSS
    const criticalCSS = [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
    ];
    
    criticalCSS.forEach(href => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'style';
        link.href = href;
        document.head.appendChild(link);
    });
}

// Initialize everything
document.addEventListener('DOMContentLoaded', function() {
    optimizePerformance();
    initializePageTransitions();
});

// Service Worker for offline support
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
        .then(function(registration) {
            console.log('ServiceWorker registration successful');
        })
        .catch(function(error) {
            console.log('ServiceWorker registration failed');
        });
    });
}
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        }
    });

    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeInUp');
            }
        });
    }, observerOptions);

    // Observe all service cards and review cards
    document.querySelectorAll('.service-card, .review-card, .gallery-item').forEach(card => {
        observer.observe(card);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
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
});

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

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
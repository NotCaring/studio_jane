// Client Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });

    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('.client-sidebar');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Profile image preview
    const profileImageInput = document.getElementById('profile_image');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePreview');
                    if (preview) {
                        if (preview.tagName === 'IMG') {
                            preview.src = e.target.result;
                        } else {
                            // Replace placeholder with image
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'Foto de perfil';
                            img.className = 'profile-img';
                            img.id = 'profilePreview';
                            preview.parentNode.replaceChild(img, preview);
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Password confirmation validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (passwordInput && confirmPasswordInput) {
        function validatePasswords() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }
        
        passwordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
    }

    // New password validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmNewPasswordInput = document.getElementById('confirm_password');
    
    if (newPasswordInput && confirmNewPasswordInput) {
        function validateNewPasswords() {
            if (newPasswordInput.value && newPasswordInput.value !== confirmNewPasswordInput.value) {
                confirmNewPasswordInput.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmNewPasswordInput.setCustomValidity('');
            }
        }
        
        newPasswordInput.addEventListener('input', validateNewPasswords);
        confirmNewPasswordInput.addEventListener('input', validateNewPasswords);
    }

    // Animate statistics numbers
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        animateNumber(stat, 0, finalValue, 1000);
    });

    // Check for notifications
    checkNotifications();
    setInterval(checkNotifications, 60000); // Check every minute

    // Initialize date inputs with minimum date
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.min = new Date().toISOString().split('T')[0];
        }
    });

    // Auto-save form data to localStorage
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        // Load saved data
        const savedValue = localStorage.getItem(`client_form_${input.name}`);
        if (savedValue && !input.value) {
            input.value = savedValue;
        }

        // Save data on change
        input.addEventListener('input', function() {
            localStorage.setItem(`client_form_${input.name}`, input.value);
        });
    });

    // Clear saved form data on successful submission
    const successAlerts = document.querySelectorAll('.alert-success');
    if (successAlerts.length > 0) {
        formInputs.forEach(input => {
            localStorage.removeItem(`client_form_${input.name}`);
        });
    }
});

// Animate numbers
function animateNumber(element, start, end, duration) {
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = Math.floor(start + (end - start) * progress);
        element.textContent = current.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Check for notifications
function checkNotifications() {
    fetch('ajax/notifications.php')
    .then(response => response.json())
    .then(data => {
        const notificationBtn = document.getElementById('notificationsBtn');
        const notificationBadge = notificationBtn.querySelector('.notification-badge');
        
        if (data.count > 0) {
            notificationBadge.textContent = data.count;
            notificationBadge.style.display = 'flex';
        } else {
            notificationBadge.style.display = 'none';
        }
        
        // Show new notifications
        if (data.notifications && data.notifications.length > 0) {
            data.notifications.forEach(notification => {
                showNotification(notification.message, notification.type);
            });
        }
    })
    .catch(error => {
        console.error('Error checking notifications:', error);
    });
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
    
    // Position notification
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.maxWidth = '400px';
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
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

// Cancel appointment
function cancelAppointment(appointmentId) {
    if (confirm('¿Estás seguro de que quieres cancelar esta cita?')) {
        showLoading();
        
        fetch('ajax/cancel_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                appointment_id: appointmentId
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification('Cita cancelada exitosamente', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification('Error al cancelar la cita', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Error de conexión', 'error');
            console.error('Error:', error);
        });
    }
}

// Loading functions
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading-overlay';
    loading.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(248, 187, 217, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
    `;
    
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    const time = new Date(`2000-01-01 ${timeString}`);
    return time.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(amount);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + D for dashboard
    if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        window.location.href = 'dashboard.php';
    }
    
    // Ctrl/Cmd + P for profile
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        window.location.href = 'profile.php';
    }
    
    // Ctrl/Cmd + A for appointments
    if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
        e.preventDefault();
        window.location.href = 'appointments.php';
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modal = bootstrap.Modal.getInstance(openModal);
            if (modal) {
                modal.hide();
            }
        }
    }
});

// Performance monitoring
function trackPageLoad() {
    window.addEventListener('load', function() {
        const loadTime = performance.now();
        console.log(`Client dashboard loaded in ${loadTime.toFixed(2)}ms`);
    });
}

// Initialize performance tracking
trackPageLoad();

// Service Worker registration for offline support
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

// PWA install prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button
    const installBtn = document.getElementById('installBtn');
    if (installBtn) {
        installBtn.style.display = 'block';
        installBtn.addEventListener('click', () => {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                }
                deferredPrompt = null;
            });
        });
    }
});
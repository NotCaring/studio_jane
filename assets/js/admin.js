// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Sidebar toggle for mobile
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('.admin-sidebar');
    const mainContent = document.querySelector('.main-content');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('sidebar-open');
        });
    }

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

    // Search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Auto-submit search form after 500ms of no typing
                const form = input.closest('form');
                if (form && input.value.length > 2) {
                    form.submit();
                }
            }, 500);
        });
    });

    // Table row selection
    const tableRows = document.querySelectorAll('.modern-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && !e.target.closest('button') && !e.target.closest('a')) {
                row.classList.toggle('selected');
            }
        });
    });

    // Bulk actions
    const selectAllCheckbox = document.querySelector('#selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }

    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });

    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        if (bulkActions) {
            bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
        }
    }

    // Status update via AJAX
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const appointmentId = this.dataset.appointmentId;
            const newStatus = this.value;
            
            updateAppointmentStatus(appointmentId, newStatus);
        });
    });

    // Real-time notifications
    initializeNotifications();

    // Dashboard auto-refresh
    if (document.querySelector('.dashboard')) {
        setInterval(refreshDashboardStats, 30000); // Refresh every 30 seconds
    }

    // Image preview
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector(`#${input.id}-preview`);
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Date range picker
    const dateRangeInputs = document.querySelectorAll('.date-range');
    dateRangeInputs.forEach(input => {
        // Initialize date range picker if library is available
        if (typeof flatpickr !== 'undefined') {
            flatpickr(input, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                locale: 'es'
            });
        }
    });

    // Export functionality
    const exportButtons = document.querySelectorAll('[data-export]');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const format = this.dataset.export;
            const table = this.dataset.table;
            exportData(format, table);
        });
    });

    // Print functionality
    const printButtons = document.querySelectorAll('[data-print]');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.dataset.print;
            printElement(target);
        });
    });
});

// AJAX Functions
function updateAppointmentStatus(appointmentId, newStatus) {
    showLoading();
    
    fetch('ajax/update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            appointment_id: appointmentId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showNotification('Estado actualizado correctamente', 'success');
            // Update UI elements
            updateStatusBadge(appointmentId, newStatus);
        } else {
            showNotification('Error al actualizar el estado', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showNotification('Error de conexión', 'error');
        console.error('Error:', error);
    });
}

function refreshDashboardStats() {
    fetch('ajax/dashboard_stats.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDashboardStats(data.stats);
        }
    })
    .catch(error => {
        console.error('Error refreshing stats:', error);
    });
}

function updateDashboardStats(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            animateNumber(element, parseInt(element.textContent), stats[key]);
        }
    });
}

function animateNumber(element, start, end) {
    const duration = 1000;
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

// Notification System
function initializeNotifications() {
    // Check for new notifications every minute
    setInterval(checkNotifications, 60000);
}

function checkNotifications() {
    fetch('ajax/notifications.php')
    .then(response => response.json())
    .then(data => {
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

// Loading Functions
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
        background: rgba(15, 15, 35, 0.8);
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

// Export Functions
function exportData(format, table) {
    showLoading();
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'ajax/export.php';
    form.style.display = 'none';
    
    const formatInput = document.createElement('input');
    formatInput.name = 'format';
    formatInput.value = format;
    
    const tableInput = document.createElement('input');
    tableInput.name = 'table';
    tableInput.value = table;
    
    form.appendChild(formatInput);
    form.appendChild(tableInput);
    document.body.appendChild(form);
    
    form.submit();
    document.body.removeChild(form);
    
    setTimeout(hideLoading, 1000);
}

function printElement(selector) {
    const element = document.querySelector(selector);
    if (!element) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Imprimir</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: Arial, sans-serif; }
                @media print {
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${element.outerHTML}
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                };
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Utility Functions
function updateStatusBadge(appointmentId, status) {
    const badge = document.querySelector(`[data-appointment-id="${appointmentId}"] .status-badge`);
    if (badge) {
        badge.className = `badge status-badge badge-${getStatusClass(status)}`;
        badge.textContent = getStatusText(status);
    }
}

function getStatusClass(status) {
    const classes = {
        pending: 'warning',
        confirmed: 'success',
        cancelled: 'danger',
        completed: 'info'
    };
    return classes[status] || 'secondary';
}

function getStatusText(status) {
    const texts = {
        pending: 'Pendiente',
        confirmed: 'Confirmada',
        cancelled: 'Cancelada',
        completed: 'Completada'
    };
    return texts[status] || status;
}

// Form Helpers
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        return form.checkValidity();
    }
    return false;
}

// Data Table Enhancements
function initializeDataTable(tableId, options = {}) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Add sorting functionality
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            const direction = this.dataset.direction === 'asc' ? 'desc' : 'asc';
            sortTable(table, column, direction);
            this.dataset.direction = direction;
        });
    });
    
    // Add search functionality
    const searchInput = document.querySelector(`[data-table-search="${tableId}"]`);
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTable(table, this.value);
        });
    }
}

function sortTable(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';
        const bValue = b.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';
        
        if (direction === 'asc') {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

function filterTable(table, searchTerm) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

// Theme Management
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.dataset.theme || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    body.dataset.theme = newTheme;
    localStorage.setItem('admin-theme', newTheme);
}

function initializeTheme() {
    const savedTheme = localStorage.getItem('admin-theme') || 'dark';
    document.body.dataset.theme = savedTheme;
}

// Initialize theme on load
document.addEventListener('DOMContentLoaded', initializeTheme);

// Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.focus();
        }
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

// Performance Monitoring
function trackPageLoad() {
    window.addEventListener('load', function() {
        const loadTime = performance.now();
        console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
        
        // Send to analytics if needed
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_load_time', {
                value: Math.round(loadTime)
            });
        }
    });
}

// Initialize performance tracking
trackPageLoad();
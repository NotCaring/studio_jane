<div class="admin-sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-star me-2"></i>Studio Jane</h4>
        <small>Panel Admin</small>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                   href="/admin/index.php">
                    <i class="fas fa-chart-line me-2"></i>Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'appointments') !== false ? 'active' : ''; ?>" 
                   href="/admin/appointments/index.php">
                    <i class="fas fa-calendar-alt me-2"></i>Citas
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'clients') !== false ? 'active' : ''; ?>" 
                   href="/admin/clients/index.php">
                    <i class="fas fa-users me-2"></i>Clientes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'services') !== false ? 'active' : ''; ?>" 
                   href="/admin/services/index.php">
                    <i class="fas fa-spa me-2"></i>Servicios
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'professionals') !== false ? 'active' : ''; ?>" 
                   href="/admin/professionals/index.php">
                    <i class="fas fa-user-tie me-2"></i>Profesionales
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'gallery') !== false ? 'active' : ''; ?>" 
                   href="/admin/gallery/index.php">
                    <i class="fas fa-images me-2"></i>Galería
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : ''; ?>" 
                   href="/admin/reports/index.php">
                    <i class="fas fa-chart-bar me-2"></i>Reportes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>" 
                   href="/admin/settings/index.php">
                    <i class="fas fa-cogs me-2"></i>Configuración
                </a>
            </li>
        </ul>
    </nav>
</div>

<style>
.admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: var(--gradient-primary);
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 2rem 1.5rem;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h4 {
    color: var(--white);
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.sidebar-header small {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

.sidebar-nav {
    padding: 1rem 0;
}

.sidebar-nav .nav-link {
    color: var(--white) !important;
    padding: 1rem 1.5rem;
    margin-bottom: 0.5rem;
    border-radius: 0;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar-nav .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    border-left-color: var(--white);
    transform: translateX(5px);
}

.sidebar-nav .nav-link.active {
    background: rgba(255, 255, 255, 0.2);
    border-left-color: var(--secondary-color);
}

.sidebar-nav .nav-link i {
    width: 20px;
    text-align: center;
}

@media (max-width: 768px) {
    .admin-sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .sidebar-nav {
        display: flex;
        overflow-x: auto;
        padding: 0.5rem;
    }
    
    .sidebar-nav .nav-item {
        flex-shrink: 0;
    }
    
    .sidebar-nav .nav-link {
        padding: 0.75rem 1rem;
        margin-right: 0.5rem;
        border-radius: 10px;
        border-left: none;
        white-space: nowrap;
    }
    
    .sidebar-nav .nav-link:hover {
        transform: none;
    }
}
</style>
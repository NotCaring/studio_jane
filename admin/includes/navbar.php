<nav class="admin-navbar">
    <div class="navbar-content">
        <div class="navbar-left">
            <button class="btn btn-outline-primary btn-sm me-3 d-lg-none" type="button" data-sidebar-toggle>
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></h5>
        </div>
        
        <div class="navbar-right">
            <div class="navbar-actions me-3">
                <button class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#quickActionsModal">
                    <i class="fas fa-bolt me-1"></i>Acciones Rápidas
                </button>
                <button class="btn btn-outline-info btn-sm" id="notificationsBtn">
                    <i class="fas fa-bell me-1"></i>
                    <span class="badge bg-danger" id="notificationCount" style="display: none;">0</span>
                </button>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/admin/profile.php">
                        <i class="fas fa-user me-2"></i>Mi Perfil
                    </a></li>
                    <li><a class="dropdown-item" href="/admin/settings/index.php">
                        <i class="fas fa-cogs me-2"></i>Configuración
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/admin/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Quick Actions Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="/admin/appointments/create.php" class="btn btn-primary w-100">
                            <i class="fas fa-plus mb-2 d-block"></i>
                            Nueva Cita
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="/admin/clients/index.php" class="btn btn-info w-100">
                            <i class="fas fa-users mb-2 d-block"></i>
                            Ver Clientes
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="/admin/services/index.php" class="btn btn-success w-100">
                            <i class="fas fa-spa mb-2 d-block"></i>
                            Servicios
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="/admin/reports/index.php" class="btn btn-warning w-100">
                            <i class="fas fa-chart-bar mb-2 d-block"></i>
                            Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Menu Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <i class="fas fa-star me-2"></i>Studio Jane
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="mobile-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-chart-line me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="appointments/index.php">
                        <i class="fas fa-calendar-alt me-2"></i>Citas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clients/index.php">
                        <i class="fas fa-users me-2"></i>Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="services/index.php">
                        <i class="fas fa-spa me-2"></i>Servicios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="professionals/index.php">
                        <i class="fas fa-user-tie me-2"></i>Profesionales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gallery/index.php">
                        <i class="fas fa-images me-2"></i>Galería
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports/index.php">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings/index.php">
                        <i class="fas fa-cogs me-2"></i>Configuración
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<style>
.admin-navbar {
    background: var(--white);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 1rem 2rem;
    margin-bottom: 2rem;
}

.navbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar-left h5 {
    color: var(--text-dark);
    font-weight: 600;
}

.mobile-nav .nav-link {
    color: var(--text-dark) !important;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.mobile-nav .nav-link:hover {
    background: var(--bg-light);
    color: var(--primary-color) !important;
}

.offcanvas-header {
    background: var(--gradient-primary);
    color: var(--white);
}

.offcanvas-title {
    color: var(--white);
    font-weight: 600;
}

.btn-close {
    filter: invert(1);
}

@media (min-width: 769px) {
    .admin-navbar .btn[data-bs-toggle="offcanvas"] {
        display: none;
    }
}
</style>
<nav class="client-navbar">
    <div class="navbar-content">
        <div class="navbar-brand">
            <a href="dashboard.php">
                <i class="fas fa-star me-2"></i>
                <span>Studio Jane</span>
            </a>
        </div>
        
        <div class="navbar-actions">
            <div class="notifications">
                <button class="btn btn-outline-light btn-sm" id="notificationsBtn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" style="display: none;">0</span>
                </button>
            </div>
            
            <div class="user-menu">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['client_name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="appointments.php">
                                <i class="fas fa-calendar-alt me-2"></i>Mis Citas
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="../index.php">
                                <i class="fas fa-home me-2"></i>Sitio Web
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
<div class="client-sidebar">
    <div class="sidebar-header">
        <div class="user-info">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->execute([$_SESSION['client_id']]);
            $current_client = $stmt->fetch();
            ?>
            
            <div class="user-avatar">
                <?php if ($current_client['profile_image']): ?>
                    <img src="../<?php echo htmlspecialchars($current_client['profile_image']); ?>" alt="Perfil">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($current_client['name'], 0, 2)); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="user-details">
                <h4><?php echo htmlspecialchars($current_client['name']); ?></h4>
                <p><?php echo htmlspecialchars($current_client['email']); ?></p>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-chart-line me-2"></i>Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>" 
                   href="appointments.php">
                    <i class="fas fa-calendar-alt me-2"></i>Mis Citas
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" 
                   href="profile.php">
                    <i class="fas fa-user-edit me-2"></i>Mi Perfil
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../reservar.php">
                    <i class="fas fa-calendar-plus me-2"></i>Nueva Cita
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../servicios.php">
                    <i class="fas fa-spa me-2"></i>Servicios
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../contacto.php">
                    <i class="fas fa-headset me-2"></i>Soporte
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../index.php" class="btn btn-outline-light btn-sm w-100">
            <i class="fas fa-home me-2"></i>Ir al Sitio Web
        </a>
    </div>
</div>
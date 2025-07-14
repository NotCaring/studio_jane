<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get date range from request
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

// Revenue Report
$stmt = $pdo->prepare("
    SELECT 
        DATE(a.appointment_date) as date,
        COUNT(*) as total_appointments,
        SUM(CASE WHEN a.status = 'completed' THEN a.price ELSE 0 END) as revenue,
        SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
    FROM appointments a
    WHERE a.appointment_date BETWEEN ? AND ?
    GROUP BY DATE(a.appointment_date)
    ORDER BY date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_stats = $stmt->fetchAll();

// Services Report
$stmt = $pdo->prepare("
    SELECT 
        s.name,
        COUNT(a.id) as bookings,
        SUM(CASE WHEN a.status = 'completed' THEN a.price ELSE 0 END) as revenue,
        AVG(a.price) as avg_price
    FROM services s
    LEFT JOIN appointments a ON s.id = a.service_id 
        AND a.appointment_date BETWEEN ? AND ?
    GROUP BY s.id, s.name
    ORDER BY bookings DESC
");
$stmt->execute([$start_date, $end_date]);
$services_stats = $stmt->fetchAll();

// Clients Report
$stmt = $pdo->prepare("
    SELECT 
        c.name,
        c.email,
        COUNT(a.id) as total_appointments,
        SUM(CASE WHEN a.status = 'completed' THEN a.price ELSE 0 END) as total_spent,
        MAX(a.appointment_date) as last_visit
    FROM clients c
    LEFT JOIN appointments a ON c.id = a.client_id 
        AND a.appointment_date BETWEEN ? AND ?
    GROUP BY c.id, c.name, c.email
    HAVING total_appointments > 0
    ORDER BY total_spent DESC
    LIMIT 20
");
$stmt->execute([$start_date, $end_date]);
$top_clients = $stmt->fetchAll();

// Professionals Report
$stmt = $pdo->prepare("
    SELECT 
        p.name,
        COUNT(a.id) as appointments_handled,
        SUM(CASE WHEN a.status = 'completed' THEN a.price ELSE 0 END) as revenue_generated
    FROM professionals p
    LEFT JOIN appointments a ON p.id = a.professional_id 
        AND a.appointment_date BETWEEN ? AND ?
    GROUP BY p.id, p.name
    ORDER BY appointments_handled DESC
");
$stmt->execute([$start_date, $end_date]);
$professionals_stats = $stmt->fetchAll();

// Summary Statistics
$total_revenue = array_sum(array_column($daily_stats, 'revenue'));
$total_appointments = array_sum(array_column($daily_stats, 'total_appointments'));
$total_cancelled = array_sum(array_column($daily_stats, 'cancelled_count'));
$avg_daily_revenue = count($daily_stats) > 0 ? $total_revenue / count($daily_stats) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Studio Jane Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../includes/navbar.php'; ?>
            
            <div class="content-wrapper">
                <div class="page-header">
                    <div class="header-content">
                        <h1 class="page-title">
                            <i class="fas fa-chart-bar me-3"></i>
                            Reportes y Estadísticas
                        </h1>
                        <div class="header-actions">
                            <button class="btn btn-outline-primary me-2" onclick="exportReport()">
                                <i class="fas fa-download me-2"></i>Exportar PDF
                            </button>
                            <button class="btn btn-primary" onclick="printReport()">
                                <i class="fas fa-print me-2"></i>Imprimir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="filter-card">
                    <form method="GET" class="filter-form">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Filtrar
                                </button>
                            </div>
                            <div class="col-md-3">
                                <div class="date-range-info">
                                    <small class="text-muted">
                                        Período: <?php echo date('d/m/Y', strtotime($start_date)); ?> - 
                                        <?php echo date('d/m/Y', strtotime($end_date)); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Summary Statistics -->
                <div class="stats-grid">
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">$<?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                            <div class="stat-label">Ingresos Totales</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_appointments; ?></div>
                            <div class="stat-label">Total Citas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">$<?php echo number_format($avg_daily_revenue, 0, ',', '.'); ?></div>
                            <div class="stat-label">Promedio Diario</div>
                        </div>
                    </div>
                    
                    <div class="stat-card hover-lift">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_cancelled; ?></div>
                            <div class="stat-label">Cancelaciones</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Revenue Chart -->
                    <div class="col-lg-8">
                        <div class="data-card">
                            <h3 class="gradient-text mb-4">
                                <i class="fas fa-chart-area me-2"></i>
                                Ingresos Diarios
                            </h3>
                            <canvas id="revenueChart" height="100"></canvas>
                        </div>
                    </div>
                    
                    <!-- Services Performance -->
                    <div class="col-lg-4">
                        <div class="data-card">
                            <h3 class="gradient-text mb-4">
                                <i class="fas fa-spa me-2"></i>
                                Servicios Más Solicitados
                            </h3>
                            <canvas id="servicesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports -->
                <div class="row">
                    <!-- Top Clients -->
                    <div class="col-lg-6">
                        <div class="data-card">
                            <h3 class="gradient-text mb-4">
                                <i class="fas fa-users me-2"></i>
                                Mejores Clientes
                            </h3>
                            <div class="table-responsive">
                                <table class="table modern-table">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Citas</th>
                                            <th>Total Gastado</th>
                                            <th>Última Visita</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_clients as $client): ?>
                                            <tr>
                                                <td>
                                                    <div class="client-info">
                                                        <div class="client-avatar">
                                                            <?php echo strtoupper(substr($client['name'], 0, 2)); ?>
                                                        </div>
                                                        <div class="client-details">
                                                            <div class="client-name"><?php echo htmlspecialchars($client['name']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge badge-primary"><?php echo $client['total_appointments']; ?></span></td>
                                                <td><span class="amount">$<?php echo number_format($client['total_spent'], 0, ',', '.'); ?></span></td>
                                                <td><?php echo $client['last_visit'] ? date('d/m/Y', strtotime($client['last_visit'])) : 'N/A'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Services Performance Table -->
                    <div class="col-lg-6">
                        <div class="data-card">
                            <h3 class="gradient-text mb-4">
                                <i class="fas fa-chart-pie me-2"></i>
                                Rendimiento por Servicio
                            </h3>
                            <div class="table-responsive">
                                <table class="table modern-table">
                                    <thead>
                                        <tr>
                                            <th>Servicio</th>
                                            <th>Reservas</th>
                                            <th>Ingresos</th>
                                            <th>Precio Prom.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services_stats as $service): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                                <td><span class="badge badge-info"><?php echo $service['bookings']; ?></span></td>
                                                <td><span class="amount">$<?php echo number_format($service['revenue'], 0, ',', '.'); ?></span></td>
                                                <td>$<?php echo number_format($service['avg_price'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professionals Performance -->
                <?php if (!empty($professionals_stats)): ?>
                <div class="data-card">
                    <h3 class="gradient-text mb-4">
                        <i class="fas fa-user-tie me-2"></i>
                        Rendimiento por Profesional
                    </h3>
                    <div class="row">
                        <?php foreach ($professionals_stats as $professional): ?>
                            <div class="col-md-4 mb-3">
                                <div class="professional-card">
                                    <div class="professional-avatar">
                                        <?php echo strtoupper(substr($professional['name'], 0, 2)); ?>
                                    </div>
                                    <div class="professional-info">
                                        <h5><?php echo htmlspecialchars($professional['name']); ?></h5>
                                        <div class="professional-stats">
                                            <div class="stat-item">
                                                <span class="stat-value"><?php echo $professional['appointments_handled']; ?></span>
                                                <span class="stat-label">Citas</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-value">$<?php echo number_format($professional['revenue_generated'], 0, ',', '.'); ?></span>
                                                <span class="stat-label">Ingresos</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("','", array_map(function($item) { return date('d/m', strtotime($item['date'])); }, array_reverse($daily_stats))) . "'"; ?>],
                datasets: [{
                    label: 'Ingresos Diarios',
                    data: [<?php echo implode(',', array_column(array_reverse($daily_stats), 'revenue')); ?>],
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#a0a0a0',
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: '#2a2a3e'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#a0a0a0'
                        },
                        grid: {
                            color: '#2a2a3e'
                        }
                    }
                }
            }
        });

        // Services Chart
        const servicesCtx = document.getElementById('servicesChart').getContext('2d');
        const servicesChart = new Chart(servicesCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo "'" . implode("','", array_map(function($item) { return htmlspecialchars($item['name']); }, array_slice($services_stats, 0, 5))) . "'"; ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column(array_slice($services_stats, 0, 5), 'bookings')); ?>],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(168, 85, 247, 0.8)'
                    ],
                    borderColor: [
                        'rgb(102, 126, 234)',
                        'rgb(249, 115, 22)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(168, 85, 247)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#ffffff',
                            padding: 20
                        }
                    }
                }
            }
        });

        function exportReport() {
            const params = new URLSearchParams(window.location.search);
            window.open(`export_report.php?${params.toString()}`, '_blank');
        }

        function printReport() {
            window.print();
        }
    </script>
</body>
</html>

<style>
.professional-card {
    background: var(--hover-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
    transition: var(--transition);
    border: 1px solid var(--border-color);
}

.professional-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.professional-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    margin: 0 auto 1rem;
    font-size: 1.25rem;
}

.professional-info h5 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.professional-stats {
    display: flex;
    justify-content: space-around;
}

.professional-stats .stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.professional-stats .stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

.professional-stats .stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.date-range-info {
    display: flex;
    align-items: center;
    height: 100%;
}

@media print {
    .header-actions,
    .filter-card,
    .admin-sidebar,
    .admin-navbar {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
    }
    
    .content-wrapper {
        padding: 0 !important;
    }
    
    body {
        background: white !important;
        color: black !important;
    }
    
    .data-card {
        background: white !important;
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        page-break-inside: avoid;
        margin-bottom: 2rem !important;
    }
}
</style>
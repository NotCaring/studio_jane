<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit;
}

$client_id = $_SESSION['client_id'];

try {
    // Get upcoming appointments (next 7 days)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM appointments 
        WHERE client_id = ? 
        AND appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$client_id]);
    $upcoming_count = $stmt->fetch()['count'];
    
    // Get pending confirmations
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM appointments 
        WHERE client_id = ? 
        AND status = 'pending'
        AND appointment_date >= CURDATE()
    ");
    $stmt->execute([$client_id]);
    $pending_count = $stmt->fetch()['pending'];
    
    $notifications = [];
    $total_count = 0;
    
    if ($upcoming_count > 0) {
        $notifications[] = [
            'message' => "Tienes {$upcoming_count} cita(s) próxima(s) en los próximos 7 días",
            'type' => 'info'
        ];
        $total_count += $upcoming_count;
    }
    
    if ($pending_count > 0) {
        $notifications[] = [
            'message' => "Tienes {$pending_count} cita(s) pendiente(s) de confirmación",
            'type' => 'warning'
        ];
        $total_count += $pending_count;
    }
    
    echo json_encode([
        'count' => $total_count,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    echo json_encode(['count' => 0, 'notifications' => []]);
}
?>
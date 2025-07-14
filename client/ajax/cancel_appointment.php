<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$client_id = $_SESSION['client_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$appointment_id = (int)$input['appointment_id'];

if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'ID de cita inválido']);
    exit;
}

try {
    // Verify appointment belongs to client and can be cancelled
    $stmt = $pdo->prepare("
        SELECT * FROM appointments 
        WHERE id = ? AND client_id = ? 
        AND status IN ('pending', 'confirmed')
        AND CONCAT(appointment_date, ' ', appointment_time) > NOW() + INTERVAL 24 HOUR
    ");
    $stmt->execute([$appointment_id, $client_id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'No se puede cancelar esta cita']);
        exit;
    }
    
    // Cancel appointment
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$appointment_id]);
    
    echo json_encode(['success' => true, 'message' => 'Cita cancelada exitosamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cancelar la cita']);
}
?>
<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$errors = [];

if ($_POST) {
    // Validate required fields
    $required_fields = ['name', 'email', 'phone'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . str_replace('_', ' ', $field) . " es obligatorio";
        }
    }
    
    // Validate email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            $errors[] = "Ya existe un cliente con este email";
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO clients (name, email, phone, whatsapp, allergies, notes) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['whatsapp'] ?? $_POST['phone'],
                $_POST['allergies'] ?? '',
                $_POST['notes'] ?? ''
            ]);
            
            $_SESSION['success_message'] = "Cliente creado exitosamente";
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Error al crear el cliente";
        }
    }
}

// If there are errors, redirect back with errors
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: index.php');
    exit;
}
?>
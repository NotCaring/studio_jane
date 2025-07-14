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
    if (empty($_POST['name'])) {
        $errors[] = "El nombre es obligatorio";
    }
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../../uploads/professionals/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'professional_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/professionals/' . $new_filename;
            } else {
                $errors[] = "Error al subir la imagen";
            }
        } else {
            $errors[] = "Tipo de archivo no permitido. Solo JPG, PNG y GIF";
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO professionals (name, email, phone, specialties, image, is_available) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'] ?? null,
                $_POST['phone'] ?? null,
                $_POST['specialties'] ?? null,
                $image_path,
                isset($_POST['is_available']) ? 1 : 0
            ]);
            
            $_SESSION['success_message'] = "Profesional creado exitosamente";
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Error al crear el profesional";
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
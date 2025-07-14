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
    $required_fields = ['name', 'category_id', 'price', 'duration'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo " . str_replace('_', ' ', $field) . " es obligatorio";
        }
    }
    
    // Handle image upload
    $image_path = 'https://images.pexels.com/photos/3985322/pexels-photo-3985322.jpeg'; // Default image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../../uploads/services/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'service_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/services/' . $new_filename;
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
                INSERT INTO services (category_id, name, description, duration, price, image, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['category_id'],
                $_POST['name'],
                $_POST['description'] ?? '',
                $_POST['duration'],
                $_POST['price'],
                $image_path,
                isset($_POST['is_featured']) ? 1 : 0
            ]);
            
            $_SESSION['success_message'] = "Servicio creado exitosamente";
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Error al crear el servicio";
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
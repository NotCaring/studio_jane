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
    if (empty($_POST['title'])) {
        $errors[] = "El título es obligatorio";
    }
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipo de archivo no permitido. Solo JPG, PNG y GIF";
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB
            $errors[] = "El archivo es demasiado grande. Máximo 5MB";
        } else {
            $upload_dir = '../../uploads/gallery/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'gallery_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/gallery/' . $new_filename;
            } else {
                $errors[] = "Error al subir la imagen";
            }
        }
    } else {
        $errors[] = "La imagen es obligatoria";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO gallery (title, description, image, category, is_featured) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['title'],
                $_POST['description'] ?? '',
                $image_path,
                $_POST['category'] ?? null,
                isset($_POST['is_featured']) ? 1 : 0
            ]);
            
            $_SESSION['success_message'] = "Imagen agregada a la galería exitosamente";
            header('Location: index.php');
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Error al agregar la imagen";
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
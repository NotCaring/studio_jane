-- Studio Jane Database Schema
CREATE DATABASE IF NOT EXISTS studio_jane;
USE studio_jane;

-- Users table for admin authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration INT NOT NULL, -- in minutes
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Professionals table
CREATE TABLE professionals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    specialties TEXT,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clients table
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    whatsapp VARCHAR(20),
    allergies TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    service_id INT,
    professional_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    allergies TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE SET NULL
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    service_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Gallery table
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@studiojane.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default categories
INSERT INTO categories (name, description, image) VALUES 
('Uñas', 'Servicios de manicure, pedicure y nail art', 'https://images.pexels.com/photos/3997993/pexels-photo-3997993.jpeg'),
('Rostro', 'Limpiezas faciales y tratamientos para el rostro', 'https://images.pexels.com/photos/3985329/pexels-photo-3985329.jpeg'),
('Pestañas', 'Extensiones y tratamientos para pestañas', 'https://images.pexels.com/photos/3985334/pexels-photo-3985334.jpeg'),
('Depilación', 'Servicios de depilación con cera y láser', 'https://images.pexels.com/photos/3985322/pexels-photo-3985322.jpeg');

-- Insert default services
INSERT INTO services (category_id, name, description, duration, price, image, is_featured) VALUES 
(1, 'Manicure Completa', 'Cuidado completo de uñas con esmaltado profesional', 60, 25000, 'https://images.pexels.com/photos/3985322/pexels-photo-3985322.jpeg', TRUE),
(1, 'Pedicure Spa', 'Tratamiento completo para pies con exfoliación y masaje', 90, 35000, 'https://images.pexels.com/photos/3997993/pexels-photo-3997993.jpeg', TRUE),
(2, 'Limpieza Facial', 'Tratamiento profundo para una piel radiante', 90, 45000, 'https://images.pexels.com/photos/3985329/pexels-photo-3985329.jpeg', TRUE),
(2, 'Tratamiento Anti-edad', 'Tratamiento especializado para combatir signos de la edad', 120, 85000, 'https://images.pexels.com/photos/3985327/pexels-photo-3985327.jpeg', FALSE),
(3, 'Extensiones de Pestañas', 'Mirada perfecta con pestañas naturales', 120, 65000, 'https://images.pexels.com/photos/3985334/pexels-photo-3985334.jpeg', TRUE),
(3, 'Lifting de Pestañas', 'Realza tu mirada natural', 75, 35000, 'https://images.pexels.com/photos/3985333/pexels-photo-3985333.jpeg', FALSE),
(4, 'Depilación Piernas', 'Depilación completa con cera', 45, 25000, 'https://images.pexels.com/photos/3985360/pexels-photo-3985360.jpeg', FALSE),
(4, 'Depilación Facial', 'Depilación delicada del rostro', 30, 15000, 'https://images.pexels.com/photos/3985360/pexels-photo-3985360.jpeg', FALSE);

-- Insert default professionals
INSERT INTO professionals (name, email, phone, specialties, image) VALUES 
('Jane Rodríguez', 'jane@studiojane.com', '+57 300 123 4567', 'Uñas, Nail Art', 'https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg'),
('María González', 'maria@studiojane.com', '+57 300 123 4568', 'Tratamientos faciales, Limpieza', 'https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg'),
('Ana López', 'ana@studiojane.com', '+57 300 123 4569', 'Pestañas, Extensiones', 'https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg');

-- Insert gallery items
INSERT INTO gallery (title, description, image, category, is_featured) VALUES 
('Nail Art Personalizado', 'Diseño único para cada cliente', 'https://images.pexels.com/photos/3997993/pexels-photo-3997993.jpeg', 'Uñas', TRUE),
('Tratamiento Facial', 'Resultados visibles desde la primera sesión', 'https://images.pexels.com/photos/3985327/pexels-photo-3985327.jpeg', 'Rostro', TRUE),
('Pestañas Volumen', 'Mirada impactante y natural', 'https://images.pexels.com/photos/3985333/pexels-photo-3985333.jpeg', 'Pestañas', TRUE),
('Manicure Francesa', 'Clásico elegante y sofisticado', 'https://images.pexels.com/photos/3985322/pexels-photo-3985322.jpeg', 'Uñas', FALSE),
('Pedicure Spa', 'Relajación y belleza para tus pies', 'https://images.pexels.com/photos/3997993/pexels-photo-3997993.jpeg', 'Uñas', FALSE);

-- Insert site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES 
('site_name', 'Studio Jane'),
('site_slogan', 'Tu belleza, nuestra pasión'),
('site_email', 'info@studiojane.com'),
('site_phone', '+57 300 123 4567'),
('site_whatsapp', '573001234567'),
('site_address', 'Calle 123 #45-67, Bogotá'),
('business_hours', 'Lun - Sáb: 9:00 AM - 7:00 PM'),
('facebook_url', 'https://facebook.com/studiojane'),
('instagram_url', 'https://instagram.com/studiojane'),
('google_maps_url', 'https://maps.google.com');

-- Create indexes for better performance
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_services_category ON services(category_id);
CREATE INDEX idx_services_featured ON services(is_featured);
CREATE INDEX idx_reviews_approved ON reviews(is_approved);
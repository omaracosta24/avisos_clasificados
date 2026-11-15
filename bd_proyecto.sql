-- Active: 1793969283677@@localhost@3306
CREATE DATABASE IF NOT EXISTS sistema_avisos;
USE sistema_avisos;

CREATE TABLE Roles (
    id_roles INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(45) NOT NULL
);

CREATE TABLE Permisos (
    id_permisos INT AUTO_INCREMENT PRIMARY KEY,
    nombre_permiso VARCHAR(45) NOT NULL,
    descripcion VARCHAR (45)
);

CREATE TABLE Roles_Permisos (
    id_roles INT,
    id_permisos INT,
    PRIMARY KEY (id_roles, id_permisos),
    FOREIGN KEY (id_roles) REFERENCES Roles(id_roles) ON DELETE CASCADE,
    FOREIGN KEY (id_permisos) REFERENCES Permisos(id_permisos) ON DELETE CASCADE
);

CREATE TABLE Autores (
    id_autores INT AUTO_INCREMENT PRIMARY KEY,
    nombre_autor VARCHAR(45) NOT NULL,
    email VARCHAR(45) NOT NULL UNIQUE, -- El email debe ser único
    telefono VARCHAR(45),
    id_roles INT,
    FOREIGN KEY (id_roles) REFERENCES Roles(id_roles) ON DELETE RESTRICT
);

CREATE TABLE Categorias (
    id_categorias INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(45) NOT NULL
);

CREATE TABLE Avisos (
    id_avisos INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(45) NOT NULL,
    fecha_hora_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Permite orden cronológico exacto
    indicador_resaltado TINYINT(1) DEFAULT 0, -- RETO: 0 = Normal, 1 = Resaltado (paga el doble)
    id_categorias INT,
    id_autores INT,
    FOREIGN KEY (id_categorias) REFERENCES Categorias(id_categorias) ON DELETE RESTRICT,
    FOREIGN KEY (id_autores) REFERENCES Autores(id_autores) ON DELETE RESTRICT
);

CREATE TABLE Avisos_Productos (
    id_avisos INT PRIMARY KEY,
    descripcion_producto VARCHAR (45) NOT NULL,
    precio_producto DECIMAL(10,2) NOT NULL,
    --  Si borro el aviso en la tabla Padre, se borra automáticamente aquí
    FOREIGN KEY (id_avisos) REFERENCES Avisos(id_avisos) ON DELETE CASCADE
);

-- Subtipo: Laborales
CREATE TABLE Avisos_Laborales (
    id_avisos INT PRIMARY KEY,
    empresa_contrata VARCHAR(45) NOT NULL,
    descripcion_cargo VARCHAR(45) NOT NULL,
    sueldo_mensual DECIMAL(10,2) NULL, -- Es opcional según la letra
    FOREIGN KEY (id_avisos) REFERENCES Avisos(id_avisos) ON DELETE CASCADE
);

-- Subtipo: Inmobiliarios
CREATE TABLE Avisos_Inmobiliarios (
    id_avisos INT PRIMARY KEY,
    tipo_inmueble VARCHAR(100) NOT NULL,
    ubicacion_inmueble VARCHAR(100) NOT NULL,
    superficie_inmueble DECIMAL(10,2) NOT NULL,
    precio_inmueble DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_avisos) REFERENCES Avisos(id_avisos) ON DELETE CASCADE
);

-- =========================================================================
-- INSERCIÓN DE DATOS INICIALES Y ASIGNACIÓN RBAC
-- =========================================================================

-- Insertar los roles requeridos
INSERT INTO Roles (nombre_rol) VALUES ('Administrador'), ('Publicador'), ('Visitante');

-- Insertar los permisos del sistema
INSERT INTO Permisos (nombre_permiso, descripcion) VALUES 
('crear_aviso', 'Permite publicar un aviso clasificado'),
('eliminar_aviso', 'Permite dar de baja un aviso del sistema'),
('ver_avisos', 'Permite visualizar la cartelera de avisos');

-- Asignar permisos a los roles en la tabla puente (Roles_Permisos)
-- Administrador (id_roles = 1) tiene todos los permisos (id 1, 2 y 3)
INSERT INTO Roles_Permisos (id_roles, id_permisos) VALUES (1, 1), (1, 2), (1, 3);
-- Publicador (id_roles = 2) puede crear y ver (id 1 y 3)
INSERT INTO Roles_Permisos (id_roles, id_permisos) VALUES (2, 1), (2, 3);
-- Visitante (id_roles = 3) solo puede ver (id 3)
INSERT INTO Roles_Permisos (id_roles, id_permisos) VALUES (3, 3);

-- Datos base restantes
INSERT INTO Categorias (nombre_categoria) VALUES ('Laboral'), ('Producto'), ('Inmobiliario');
INSERT INTO Autores (nombre_autor, email, telefono, id_roles) VALUES ('Carlos Gómez', 'carlos@correo.com', '099111222', 2);
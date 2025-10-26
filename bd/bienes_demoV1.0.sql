-- Script SQL compatible con versiones antiguas de MySQL/phpMyAdmin
/*
    CAMBIOS:
- No se usan valores por defecto con CURRENT_TIMESTAMP en campos DATE. Se debe asignar la fecha desde la aplicación
- Las restricciones UNIQUE se agregan como índices después de la creación de la tabla.
- Las claves foráneas se agregan al final para evitar errores de orden de creación.
- Uso consistente de comillas simples en valores en lugar de comillas dobles.
- Se agrego los estados para verificar si está activo o deshabilitado.
- Se eliminaron campos de trazabilidad, debido a mucha cantidad de codigo, y falta de experiencia
*/
-- Tabla de roles
CREATE TABLE rol (
    rol_id INT NOT NULL AUTO_INCREMENT,
    rol_nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (rol_id)
);

-- Tabla de usuarios
CREATE TABLE usuario (
    usuario_id INT NOT NULL AUTO_INCREMENT,
    usuario_nombre VARCHAR(100) NOT NULL,
    usuario_apellido VARCHAR(100) NOT NULL,
    usuario_correo VARCHAR(254) NOT NULL,
    usuario_telefono VARCHAR(20),
    usuario_cedula VARCHAR(10) NOT NULL,
    usuario_sexo TINYINT(1) NOT NULL,
    usuario_nac DATE NOT NULL,
    usuario_direccion VARCHAR(100),
    usuario_clave VARCHAR(255) NOT NULL,
    usuario_usuario VARCHAR(100) NOT NULL,
    rol_id INT NOT NULL,
    usuario_foto VARCHAR(255),
    usuario_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (usuario_id),
    UNIQUE KEY idx_usuario_email (usuario_correo),
    UNIQUE KEY idx_usuario_cedula (usuario_cedula),
    UNIQUE KEY idx_usuario_usuario (usuario_usuario)
);

-- Tabla de categorías
CREATE TABLE categoria (
    categoria_id INT NOT NULL AUTO_INCREMENT,
    categoria_nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (categoria_id)
);

-- Tabla de clasificaciones
CREATE TABLE clasificacion (
    clasificacion_id INT NOT NULL AUTO_INCREMENT,
    clasificacion_nombre VARCHAR(100) NOT NULL,
    categoria_id INT NOT NULL,
    clasificacion_descripcion VARCHAR(200),
    clasificacion_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (clasificacion_id)
);

-- Tabla de estados
CREATE TABLE estado (
    estado_id INT NOT NULL AUTO_INCREMENT,
    estado_nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (estado_id)
);

-- Tabla de áreas
CREATE TABLE area (
    area_id INT NOT NULL AUTO_INCREMENT,
    area_nombre VARCHAR(100) NOT NULL,
    area_descripcion VARCHAR(200),
    area_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (area_id)
);

-- Tabla de marcas
CREATE TABLE marca (
    marca_id INT NOT NULL AUTO_INCREMENT,
    marca_nombre VARCHAR(100) NOT NULL,
    marca_imagen VARCHAR(255),
    marca_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (marca_id)
);

-- Tabla de tipo de bienes
CREATE TABLE bien_tipo (
    bien_codigo VARCHAR(20) NOT NULL,
    categoria_id INT NOT NULL,
    clasificacion_id INT NOT NULL,
    bien_nombre VARCHAR(100) NOT NULL,
    bien_modelo VARCHAR(100),
    marca_id INT,
    bien_descripcion VARCHAR(200),
    estado_id INT DEFAULT 1,
    bien_imagen VARCHAR(255),
    -- CLAVES PRIMARIAS Y FORANEAS
    PRIMARY KEY (bien_codigo)
);

-- Tabla de bienes (instancias individuales de bien_tipo)
CREATE TABLE bien (
    bien_id INT NOT NULL AUTO_INCREMENT,
    bien_tipo_codigo VARCHAR(20) NOT NULL, -- FK a bien_tipo.bien_codigo
    bien_serie VARCHAR(100),
    estado_id INT DEFAULT 1, -- FK a estado.estado_id
    PRIMARY KEY (bien_id),
    UNIQUE KEY idx_bien_serie (bien_serie)
);

-- Tabla de personas
CREATE TABLE persona (
    persona_id INT NOT NULL AUTO_INCREMENT,
    persona_nombre VARCHAR(100) NOT NULL,
    persona_apellido VARCHAR(100) NOT NULL,
    persona_cargo VARCHAR(100) NOT NULL,
    persona_correo VARCHAR(254) NOT NULL,
    persona_telefono VARCHAR(20),
    persona_cedula VARCHAR(10) NOT NULL,
    persona_sexo TINYINT(1) NOT NULL,
    persona_nac DATE NOT NULL,
    persona_direccion VARCHAR(100) NOT NULL,
    persona_foto VARCHAR(255),
    persona_estado TINYINT(1) NOT NULL DEFAULT 1,
    -- CLAVES PRIMARIAS Y FORANEAS
    PRIMARY KEY (persona_id),
    UNIQUE KEY idx_persona_email (persona_correo),
    UNIQUE KEY idx_persona_cedula (persona_cedula)
);


-- Tabla de asignaciones (ahora referencia bien.bien_id)
CREATE TABLE asignacion (
    asignacion_id INT NOT NULL AUTO_INCREMENT,
    bien_id INT NOT NULL, -- Referencia a la instancia específica del bien
    area_id INT NOT NULL,
    persona_id INT NOT NULL,
    asignacion_fecha DATE NOT NULL,
    asignacion_fecha_fin DATE,
    asignacion_estado TINYINT(2) NOT NULL DEFAULT 1,
    PRIMARY KEY (asignacion_id)
);

-- Tabla de Recepcion (ahora referencia bien.bien_id)
CREATE TABLE recepcion (
    recepcion_id INT NOT NULL AUTO_INCREMENT,
    bien_id INT, -- Referencia a la instancia específica del bien
    recepcion_cantidad INT NOT NULL,
    recepcion_fecha DATE NOT NULL,
    recepcion_descripcion VARCHAR(200),
    PRIMARY KEY (recepcion_id)
);

-- Tabla de Desincorporacion (ahora referencia bien.bien_id)
CREATE TABLE desincorporacion (
    desin_id INT NOT NULL AUTO_INCREMENT,
    bien_id INT, -- Referencia a la instancia específica del bien
    desin_cantidad INT NOT NULL,
    desin_fecha DATE NOT NULL,
    desin_descripcion VARCHAR(200),
    PRIMARY KEY (desin_id)
);

-- Claves foráneas
ALTER TABLE usuario ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES rol(rol_id);

ALTER TABLE clasificacion ADD CONSTRAINT fk_clasificacion_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);

ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_clasificacion FOREIGN KEY (clasificacion_id) REFERENCES clasificacion(clasificacion_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_marca FOREIGN KEY (marca_id) REFERENCES marca(marca_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_estado FOREIGN KEY (estado_id) REFERENCES estado(estado_id);

ALTER TABLE bien ADD CONSTRAINT fk_bien_bien_tipo FOREIGN KEY (bien_tipo_codigo) REFERENCES bien_tipo(bien_codigo);
ALTER TABLE bien ADD CONSTRAINT fk_bien_estado FOREIGN KEY (estado_id) REFERENCES estado(estado_id);

ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_bien FOREIGN KEY (bien_id) REFERENCES bien(bien_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_area FOREIGN KEY (area_id) REFERENCES area(area_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_persona FOREIGN KEY (persona_id) REFERENCES persona(persona_id);

ALTER TABLE recepcion ADD CONSTRAINT fk_recepcion_bien FOREIGN KEY (bien_id) REFERENCES bien(bien_id);

ALTER TABLE desincorporacion ADD CONSTRAINT fk_desin_bien FOREIGN KEY (bien_id) REFERENCES bien(bien_id);


-- Datos de ejemplo
INSERT INTO categoria (categoria_nombre) VALUES ('Tecnologico'), ('Mobiliario'), ('Otros');
INSERT INTO rol (rol_nombre) VALUES ('Administrador'), ('Director'), ('Vicereptor');
INSERT INTO estado (estado_nombre) VALUES ('Disponible'), ('Asignado'), ('Mantenimiento'), ('Desincorporado');
INSERT INTO usuario (
    usuario_nombre, usuario_apellido, usuario_correo, usuario_telefono,
    usuario_sexo, usuario_cedula, usuario_nac, usuario_direccion,
    usuario_clave, usuario_usuario, rol_id, usuario_foto
) VALUES
('Administrador', 'Principal', 'admin@gmail.com', '', 0, 'V-11111111', '2005-11-14', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'admin123', 1, 'img/icons/perfil.png'),
('Carlos', 'Ramírez', 'carlos.ramirez@gmail.com', '04141234568', 0, 'V-22222222', '1990-06-22', 'Calle 10, Maracay', '$2y$10$abc2', 'carlos90', 1, 'img/icons/perfil.png'),
('María', 'González', 'maria.gonzalez@gmail.com', '04141234569', 1, 'V-33333333', '1985-03-15', 'Sector El Centro, Valencia', '$2y$10$abc3', 'mariagonz', 1, 'img/icons/perfil.png'),
('Luis', 'Fernández', 'luis.fernandez@gmail.com', '04141234570', 0, 'V-44444444', '1992-09-10', 'Av. Universidad, Mérida', '$2y$10$abc4', 'luisf92', 1, 'img/icons/perfil.png'),
('Ana', 'Torres', 'ana.torres@gmail.com', '04141234571', 1, 'V-55555555', '1998-12-01', 'Calle Sucre, Barquisimeto', '$2y$10$abc5', 'anatorres', 1, 'img/icons/perfil.png'),
('José', 'Martínez', 'jose.martinez@gmail.com', '04141234572', 0, 'V-66666666', '1980-07-07', 'Av. Principal, San Cristóbal', '$2y$10$abc6', 'josem80', 1, 'img/icons/perfil.png'),
('Laura', 'Pérez', 'laura.perez@gmail.com', '04141234573', 1, 'V-77777777', '1995-05-20', 'Urbanización La Floresta, Caracas', '$2y$10$abc7', 'laurap95', 1, 'img/icons/perfil.png'),
('Miguel', 'Rodríguez', 'miguel.rodriguez@gmail.com', '04141234574', 0, 'V-88888888', '1988-11-30', 'Calle 5, Puerto Ordaz', '$2y$10$abc8', 'miguelr88', 1, 'img/icons/perfil.png'),
('Sofía', 'Morales', 'sofia.morales@gmail.com', '04141234575', 1, 'V-99999999', '1993-04-18', 'Av. Las Delicias, Maracay', '$2y$10$abc9', 'sofia93', 1, 'img/icons/perfil.png'),
('Andrés', 'López', 'andres.lopez@gmail.com', '04141234576', 0, 'V-10101010', '1991-01-25', 'Calle Miranda, Cumaná', '$2y$10$abc10', 'andresl91', 1, 'img/icons/perfil.png');

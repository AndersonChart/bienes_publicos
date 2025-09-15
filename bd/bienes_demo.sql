-- Script SQL compatible con versiones antiguas de MySQL/phpMyAdmin
/*
    CAMBIOS:
- No se usan valores por defecto con CURRENT_TIMESTAMP en campos DATE. Se debe asignar la fecha desde la aplicación
- Las restricciones UNIQUE se agregan como índices después de la creación de la tabla.
- Las claves foráneas se agregan al final para evitar errores de orden de creación.
- Uso consistente de comillas simples en valores en lugar de comillas dobles.
- Se agrego los estados para verificar si está activo o deshabilitado.
*/

CREATE TABLE rol (
    rol_id INT NOT NULL AUTO_INCREMENT,
    rol_nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (rol_id)
);

CREATE TABLE usuario (
    usuario_id INT NOT NULL AUTO_INCREMENT,
    usuario_nombre VARCHAR(100) NOT NULL,
    usuario_apellido VARCHAR(100) NOT NULL,
    usuario_email VARCHAR(100) NOT NULL,
    usuario_telefono VARCHAR(20),
    usuario_clave VARCHAR(255) NOT NULL,
    usuario_usuario VARCHAR(100) NOT NULL,
    rol_id INT NOT NULL,
    usuario_foto VARCHAR(255),
    usuario_estado TINYINT(1) NOT NULL DEFAULT 1,
    usuario_sexo TINYINT(1) NOT NULL,
    PRIMARY KEY (usuario_id),
    KEY idx_rol_id (rol_id)
);

CREATE UNIQUE INDEX idx_usuario_email ON usuario(usuario_email);
CREATE UNIQUE INDEX idx_usuario_usuario ON usuario(usuario_usuario);

CREATE TABLE categoria (
    categoria_id INT NOT NULL AUTO_INCREMENT,
    categoria_nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (categoria_id)
);

CREATE TABLE estado (
    estado_id INT NOT NULL AUTO_INCREMENT,
    estado_nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (estado_id)
);

CREATE TABLE area (
    area_id INT NOT NULL AUTO_INCREMENT,
    area_nombre VARCHAR(100) NOT NULL,
    area_descripcion VARCHAR(100),
    area_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (area_id)
);

CREATE TABLE marca (
    marca_id INT NOT NULL AUTO_INCREMENT,
    marca_nombre VARCHAR(100) NOT NULL,
    marca_imagen VARCHAR(255),
    marca_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (marca_id)
);

CREATE TABLE modelo (
    modelo_id INT NOT NULL AUTO_INCREMENT,
    modelo_nombre VARCHAR(100) NOT NULL,
    modelo_estado TINYINT(1) NOT NULL DEFAULT 1,
    marca_id INT,
    PRIMARY KEY (modelo_id),
    KEY idx_marca_id (marca_id)
);

CREATE TABLE bien (
    bien_id INT NOT NULL AUTO_INCREMENT,
    bien_serie VARCHAR(100) NOT NULL,
    bien_nombre VARCHAR(100) NOT NULL,
    bien_descripcion VARCHAR(100),
    categoria_id INT NOT NULL,
    fecha_add DATE NOT NULL,
    marca_id INT NOT NULL,
    modelo_id INT NOT NULL,
    estado_id INT DEFAULT 1,
    bien_imagen VARCHAR(255),
    bien_acta VARCHAR(255),
    PRIMARY KEY (bien_id),
    KEY idx_modelo_id (modelo_id),
    KEY idx_marca_id (marca_id),
    KEY idx_categoria_id (categoria_id),
    KEY idx_estado_id (estado_id)
);

CREATE UNIQUE INDEX idx_bien_serie ON bien(bien_serie);

CREATE TABLE persona (
    persona_id INT NOT NULL AUTO_INCREMENT,
    persona_nombre VARCHAR(100) NOT NULL,
    persona_apellido VARCHAR(100) NOT NULL,
    persona_email VARCHAR(100) NOT NULL,
    persona_telefono VARCHAR(20),
    persona_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (persona_id)
);

CREATE UNIQUE INDEX idx_persona_email ON persona(persona_email);

CREATE TABLE asignacion (
    asignacion_id INT NOT NULL AUTO_INCREMENT,
    bien_id INT,
    area_id INT,
    persona_id INT,
    asignacion_fecha DATE,
    asignacion_acta VARCHAR(255) NOT NULL,
    PRIMARY KEY (asignacion_id),
    KEY idx_bien_id (bien_id),
    KEY idx_area_id (area_id),
    KEY idx_persona_id (persona_id)
);

CREATE TABLE movimiento (
    movimiento_id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT,
    movimiento_fecha DATE,
    movimiento_descripcion TEXT,
    PRIMARY KEY (movimiento_id),
    KEY idx_usuario_id (usuario_id)
);

-- Claves foráneas agregadas después para compatibilidad
ALTER TABLE usuario ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES rol(rol_id);
ALTER TABLE modelo ADD CONSTRAINT fk_modelo_marca FOREIGN KEY (marca_id) REFERENCES marca(marca_id);
ALTER TABLE bien ADD CONSTRAINT fk_bien_modelo FOREIGN KEY (modelo_id) REFERENCES modelo(modelo_id);
ALTER TABLE bien ADD CONSTRAINT fk_bien_marca FOREIGN KEY (marca_id) REFERENCES marca(marca_id);
ALTER TABLE bien ADD CONSTRAINT fk_bien_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);
ALTER TABLE bien ADD CONSTRAINT fk_bien_estado FOREIGN KEY (estado_id) REFERENCES estado(estado_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_bien FOREIGN KEY (bien_id) REFERENCES bien(bien_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_area FOREIGN KEY (area_id) REFERENCES area(area_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_persona FOREIGN KEY (persona_id) REFERENCES persona(persona_id);
ALTER TABLE movimiento ADD CONSTRAINT fk_movimiento_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);

-- Insertar catálogos

INSERT INTO marca (marca_nombre) VALUES ('Desconocido');
INSERT INTO modelo (modelo_nombre, marca_id) VALUES ('Desconocido', 1);
INSERT INTO categoria (categoria_nombre) VALUES ('Ninguno'), ('Tecnologico'), ('Mobiliario'), ('Equipo Especializado');
INSERT INTO rol (rol_nombre) VALUES ('Administrador'), ('Director'), ('persona');

-- Clave y Usuario por defecto (admin - admin)
INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_email, usuario_clave, usuario_usuario, rol_id) VALUES ('Administrador', 'Principal', 'admin@gmail.com', '$2y$10$dOSvUCQFohsnR/MWanlpLOU1rd4.ueMhqCOgr44WMxuXSzNnEvFNS', 'admin', 1);
INSERT INTO estado (estado_nombre) VALUES ('Disponible'), ('Asignado'), ('Mantenimiento'), ('Desincorporado');
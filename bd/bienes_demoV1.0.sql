-- Script SQL compatible con versiones antiguas de MySQL/phpMyAdmin
/*
    CAMBIOS:
- No se usan valores por defecto con CURRENT_TIMESTAMP en campos DATE. Se debe asignar la fecha desde la aplicación
- Las restricciones UNIQUE se agregan como índices después de la creación de la tabla.
- Las claves foráneas se agregan al final para evitar errores de orden de creación.
- Uso consistente de comillas simples en valores en lugar de comillas dobles.
- Se agrego los estados para verificar si está activo o deshabilitado.
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
    usuario_email VARCHAR(254) NOT NULL,
    usuario_telefono VARCHAR(20),
    usuario_cedula VARCHAR(8) NOT NULL,
    usuario_sexo TINYINT(1) NOT NULL,
    usuario_clave VARCHAR(255) NOT NULL,
    usuario_usuario VARCHAR(100) NOT NULL,
    rol_id INT NOT NULL,
    usuario_foto VARCHAR(255),
    usuario_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (usuario_id),
    UNIQUE KEY idx_usuario_email (usuario_email),
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
    usuario_id INT NOT NULL,
    usuario_mod INT,
    clasificacion_fecha DATE NOT NULL,
    clasificacion_fecha_mod DATE,
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
    usuario_id INT NOT NULL,
    usuario_mod INT,
    area_fecha DATE NOT NULL,
    area_fecha_mod DATE,
    PRIMARY KEY (area_id)
);

-- Tabla de marcas
CREATE TABLE marca (
    marca_id INT NOT NULL AUTO_INCREMENT,
    marca_nombre VARCHAR(100) NOT NULL,
    marca_imagen VARCHAR(255),
    marca_estado TINYINT(1) NOT NULL DEFAULT 1,
    usuario_id INT NOT NULL,
    usuario_mod INT,
    marca_fecha DATE NOT NULL,
    marca_fecha_mod DATE,
    PRIMARY KEY (marca_id)
);

-- Tabla de tipo de bienes
CREATE TABLE bien_tipo (
    bien_codigo VARCHAR(20) NOT NULL,
    bien_nombre VARCHAR(100) NOT NULL,
    categoria_id INT NOT NULL,
    clasificacion_id INT NOT NULL,
    marca_id INT,
    bien_modelo VARCHAR(100),
    bien_descripcion VARCHAR(200),
    bien_precio DECIMAL(18,8) DEFAULT NULL,
    estado_id INT DEFAULT 1,
    bien_imagen VARCHAR(255),
    -- TRAZABILIDAD
    usuario_id INT NOT NULL,
    usuario_mod INT,
    bien_fecha DATE NOT NULL,
    bien_fecha_mod DATE,
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
    persona_sexo TINYINT(1) NOT NULL,
    persona_cargo VARCHAR(100) NOT NULL,
    persona_email VARCHAR(254) NOT NULL,
    persona_cedula VARCHAR(8) NOT NULL,
    persona_telefono VARCHAR(20),
    persona_estado TINYINT(1) NOT NULL DEFAULT 1,
    -- TRAZABILIDAD
    usuario_id INT NOT NULL,
    usuario_mod INT,
    persona_fecha DATE NOT NULL,
    persona_fecha_mod DATE,
    -- CLAVES PRIMARIAS Y FORANEAS
    PRIMARY KEY (persona_id),
    UNIQUE KEY idx_persona_email (persona_email),
    UNIQUE KEY idx_persona_cedula (persona_cedula)
);

-- Tabla de Proveedores
CREATE TABLE proveedor (
    proveedor_id INT NOT NULL AUTO_INCREMENT,
    proveedor_nombre VARCHAR(100) NOT NULL,
    proveedor_ubicacion VARCHAR(100),
    proveedor_descripcion VARCHAR(200),
    proveedor_contacto VARCHAR(50),
    proveedor_email VARCHAR(254),
    proveedor_estado TINYINT(1) NOT NULL DEFAULT 1,    
    usuario_id INT NOT NULL,
    usuario_mod INT,
    proveedor_fecha DATE NOT NULL,
    proveedor_fecha_mod DATE,
    PRIMARY KEY (proveedor_id)
);

-- Tabla de Almacenes
CREATE TABLE almacen (
    almacen_id INT NOT NULL AUTO_INCREMENT,
    almacen_nombre VARCHAR(100) NOT NULL,
    almacen_ubicacion VARCHAR(100),
    almacen_estado TINYINT(1) NOT NULL DEFAULT 1,    
    usuario_id INT NOT NULL,
    usuario_mod INT,
    almacen_fecha DATE NOT NULL,
    almacen_fecha_mod DATE,
    PRIMARY KEY (almacen_id)
);

-- Tabla de asignaciones (ahora referencia bien.bien_id)
CREATE TABLE asignacion (
    asignacion_id INT NOT NULL AUTO_INCREMENT,
    bien_id INT NOT NULL, -- Referencia a la instancia específica del bien
    almacen_id INT NOT NULL,
    area_id INT NOT NULL,
    persona_id INT NOT NULL,
    usuario_id INT NOT NULL,
    usuario_mod INT,
    asignacion_fecha DATE NOT NULL,
    asignacion_fecha_fin DATE,
    asignacion_fecha_mod DATE,
    asignacion_fecha_fin_mod DATE,
    asignacion_estado TINYINT(2) NOT NULL DEFAULT 1,
    PRIMARY KEY (asignacion_id)
);

-- Tabla de Recepcion (ahora referencia bien.bien_id)
CREATE TABLE recepcion (
    recepcion_id INT NOT NULL AUTO_INCREMENT,
    proveedor_id INT NOT NULL,
    almacen_id INT NOT NULL,
    recepcion_fecha DATE NOT NULL,
    usuario_id INT NOT NULL,
    usuario_mod INT,
    recepcion_fecha_mod DATE,
    recepcion_descripcion VARCHAR(200),
    bien_id INT, -- Referencia a la instancia específica del bien
    recepcion_cantidad INT NOT NULL,
    PRIMARY KEY (recepcion_id)
);

-- Tabla de Desincorporacion (ahora referencia bien.bien_id)
CREATE TABLE desincorporacion (
    desin_id INT NOT NULL AUTO_INCREMENT,
    desin_fecha DATE NOT NULL,
    usuario_id INT NOT NULL,
    usuario_mod INT,
    desin_fecha_mod DATE,
    desin_descripcion VARCHAR(200),
    bien_id INT, -- Referencia a la instancia específica del bien
    desin_cantidad INT NOT NULL,
    PRIMARY KEY (desin_id)
);

-- Tabla de movimientos
CREATE TABLE movimiento (
    movimiento_id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT,
    movimiento_fecha DATE,
    movimiento_descripcion VARCHAR(200),
    PRIMARY KEY (movimiento_id)
);

-- Claves foráneas
ALTER TABLE usuario ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES rol(rol_id);

ALTER TABLE clasificacion ADD CONSTRAINT fk_clasificacion_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);
ALTER TABLE clasificacion ADD CONSTRAINT fk_clasificacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE clasificacion ADD CONSTRAINT fk_clasificacion_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE area ADD CONSTRAINT fk_area_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE area ADD CONSTRAINT fk_area_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE marca ADD CONSTRAINT fk_marca_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE marca ADD CONSTRAINT fk_marca_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_categoria FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_clasificacion FOREIGN KEY (clasificacion_id) REFERENCES clasificacion(clasificacion_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_marca FOREIGN KEY (marca_id) REFERENCES marca(marca_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_estado FOREIGN KEY (estado_id) REFERENCES estado(estado_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE bien_tipo ADD CONSTRAINT fk_bien_tipo_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE bien ADD CONSTRAINT fk_bien_bien_tipo FOREIGN KEY (bien_tipo_codigo) REFERENCES bien_tipo(bien_codigo);
ALTER TABLE bien ADD CONSTRAINT fk_bien_estado FOREIGN KEY (estado_id) REFERENCES estado(estado_id);

ALTER TABLE persona ADD CONSTRAINT fk_persona_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE persona ADD CONSTRAINT fk_persona_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE proveedor ADD CONSTRAINT fk_proveedor_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE proveedor ADD CONSTRAINT fk_proveedor_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE almacen ADD CONSTRAINT fk_almacen_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE almacen ADD CONSTRAINT fk_almacen_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_bien FOREIGN KEY (bien_id) REFERENCES bien(bien_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_almacen FOREIGN KEY (almacen_id) REFERENCES almacen(almacen_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_area FOREIGN KEY (area_id) REFERENCES area(area_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_persona FOREIGN KEY (persona_id) REFERENCES persona(persona_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE asignacion ADD CONSTRAINT fk_asignacion_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE recepcion ADD CONSTRAINT fk_recepcion_bien FOREIGN KEY (bien_id) REFERENCES bien(bien_id);
ALTER TABLE recepcion ADD CONSTRAINT fk_recepcion_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedor(proveedor_id);
ALTER TABLE recepcion ADD CONSTRAINT fk_recepcion_almacen FOREIGN KEY (almacen_id) REFERENCES almacen(almacen_id);
ALTER TABLE recepcion ADD CONSTRAINT fk_recepcion_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE recepcion ADD CONSTRAINT fk_recepcion_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE desincorporacion ADD CONSTRAINT fk_desin_bien FOREIGN KEY (bien_id) REFERENCES bien(bien_id);
ALTER TABLE desincorporacion ADD CONSTRAINT fk_desin_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);
ALTER TABLE desincorporacion ADD CONSTRAINT fk_desin_usuario_mod FOREIGN KEY (usuario_mod) REFERENCES usuario(usuario_id);

ALTER TABLE movimiento ADD CONSTRAINT fk_movimiento_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id);

-- Datos de ejemplo
INSERT INTO categoria (categoria_nombre) VALUES ('Tecnologico'), ('Mobiliario'), ('Otros');
INSERT INTO rol (rol_nombre) VALUES ('Administrador'), ('Director'), ('Persona');
INSERT INTO estado (estado_nombre) VALUES ('Disponible'), ('Asignado'), ('Mantenimiento'), ('Desincorporado');
INSERT INTO usuario (
    usuario_nombre, usuario_apellido, usuario_email, usuario_sexo,
    usuario_cedula, usuario_clave, usuario_usuario, rol_id
) VALUES (
    'Administrador', 'Principal', 'admin@gmail.com', 0, '11111111',
    '$2y$10$dOSvUCQFohsnR/MWanlpLOU1rd4.ueMhqCOgr44WMxuXSzNnEvFNS', 'admin', 1
);


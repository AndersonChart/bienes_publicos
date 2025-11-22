-- Script SQL compatible con versiones antiguas de MySQL/phpMyAdmin
/*
    CAMBIOS:
- No se usan valores por defecto con CURRENT_TIMESTAMP en campos DATE. Se debe asignar la fecha desde la aplicación
- Las restricciones UNIQUE se eliminaron por falta de flexibilidad de datos duplicados entre estado "inactivo" y "activo"
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
    PRIMARY KEY (usuario_id)
);

-- Tabla de categorías
CREATE TABLE categoria (
    categoria_id INT NOT NULL AUTO_INCREMENT,
    categoria_codigo VARCHAR(20) NOT NULL,
    categoria_nombre VARCHAR(100) NOT NULL,
    categoria_tipo TINYINT(1) NOT NULL, -- 1. Completo (todos los campos de bienes) 0. basico (no coloca marcas ni modelos)
    categoria_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (categoria_id)
);

-- Tabla de clasificaciones
CREATE TABLE clasificacion (
    clasificacion_id INT NOT NULL AUTO_INCREMENT,
    clasificacion_codigo VARCHAR(20) NOT NULL,
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
    area_codigo VARCHAR(20) NOT NULL,
    area_nombre VARCHAR(100) NOT NULL,
    area_descripcion VARCHAR(200),
    area_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (area_id)
);

-- Tabla de marcas
CREATE TABLE marca (
    marca_id INT NOT NULL AUTO_INCREMENT,
    marca_codigo VARCHAR(20) NOT NULL,
    marca_nombre VARCHAR(100) NOT NULL,
    marca_imagen VARCHAR(255),
    marca_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (marca_id)
);

-- Tabla de tipo de bienes
CREATE TABLE bien_tipo (
    bien_tipo_id INT NOT NULL AUTO_INCREMENT,
    bien_tipo_codigo VARCHAR(20) NOT NULL,
    categoria_id INT NOT NULL,
    clasificacion_id INT NOT NULL,
    bien_nombre VARCHAR(100) NOT NULL,
    bien_modelo VARCHAR(100),
    marca_id INT,
    bien_descripcion VARCHAR(200),
    bien_estado TINYINT(1) NOT NULL DEFAULT 1,
    bien_imagen VARCHAR(255),
    PRIMARY KEY (bien_tipo_id)
);

-- Tabla de bienes
CREATE TABLE bien (
    bien_id INT NOT NULL AUTO_INCREMENT,
    bien_tipo_id INT NOT NULL,
    bien_serie VARCHAR(100),
    estado_id INT DEFAULT 1,
    PRIMARY KEY (bien_id)
);

-- Tabla de cargos
CREATE TABLE cargo (
    cargo_id INT NOT NULL AUTO_INCREMENT,
    cargo_codigo VARCHAR(20) NOT NULL,
    cargo_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (cargo_id)
);

-- Tabla de personas
CREATE TABLE persona (
    persona_id INT NOT NULL AUTO_INCREMENT,
    persona_nombre VARCHAR(100) NOT NULL,
    persona_apellido VARCHAR(100) NOT NULL,
    cargo_id INT NOT NULL,
    persona_correo VARCHAR(254) NOT NULL,
    persona_telefono VARCHAR(20),
    persona_cedula VARCHAR(10) NOT NULL,
    persona_sexo TINYINT(1) NOT NULL,
    persona_nac DATE NOT NULL,
    persona_direccion VARCHAR(100) NOT NULL,
    persona_foto VARCHAR(255),
    persona_estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (persona_id)
);

-- Tabla de notas de asignacion
CREATE TABLE asignacion (
    asignacion_id INT NOT NULL AUTO_INCREMENT,
    area_id INT NOT NULL,
    persona_id INT NOT NULL,
    asignacion_fecha DATE NOT NULL,
    asignacion_fecha_fin DATE,
    asignacion_estado TINYINT(2) NOT NULL DEFAULT 1,
    PRIMARY KEY (asignacion_id)
);

-- Tabla de bienes asignados
CREATE TABLE asignacion_bien (
    asignacion_bien_id INT NOT NULL AUTO_INCREMENT,
    bien_id INT NOT NULL,
    asignacion_id INT NOT NULL,
    PRIMARY KEY (asignacion_bien_id)
);

-- Tabla de recepción/desincorporación
CREATE TABLE ajuste (
    ajuste_id INT NOT NULL AUTO_INCREMENT,
    ajuste_fecha DATE NOT NULL,
    ajuste_descripcion VARCHAR(200),
    ajuste_tipo TINYINT(2) NOT NULL, -- 1. Entrada 0. Salida
    PRIMARY KEY (ajuste_id)
);

-- Tabla de bienes por recepción/desincorporación
CREATE TABLE ajuste_bien (
    ajuste_bien_id INT NOT NULL AUTO_INCREMENT,
    bien_id INT NOT NULL,
    ajuste_id INT NOT NULL,
    PRIMARY KEY (ajuste_bien_id)
);


-- Índices y valores únicos
ALTER TABLE clasificacion ADD UNIQUE (clasificacion_codigo);
ALTER TABLE area ADD UNIQUE (area_codigo);
ALTER TABLE marca ADD UNIQUE (marca_codigo);

CREATE INDEX idx_usuario_usuario ON usuario(usuario_usuario);
CREATE INDEX idx_usuario_cedula ON usuario(usuario_cedula);
CREATE INDEX idx_persona_cedula ON persona(persona_cedula);

-- Claves foráneas
-- Usuario → Rol
ALTER TABLE usuario 
    ADD CONSTRAINT fk_usuario_rol 
    FOREIGN KEY (rol_id) REFERENCES rol(rol_id);

-- Clasificación → Categoría
ALTER TABLE clasificacion 
    ADD CONSTRAINT fk_clasificacion_categoria 
    FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);

-- Bien Tipo → Categoría
ALTER TABLE bien_tipo 
    ADD CONSTRAINT fk_bien_tipo_categoria 
    FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);

-- Bien Tipo → Clasificación
ALTER TABLE bien_tipo 
    ADD CONSTRAINT fk_bien_tipo_clasificacion 
    FOREIGN KEY (clasificacion_id) REFERENCES clasificacion(clasificacion_id);

-- Bien Tipo → Marca
ALTER TABLE bien_tipo 
    ADD CONSTRAINT fk_bien_tipo_marca 
    FOREIGN KEY (marca_id) REFERENCES marca(marca_id);

-- Bien → Bien Tipo
ALTER TABLE bien 
    ADD CONSTRAINT fk_bien_bien_tipo 
    FOREIGN KEY (bien_tipo_id) REFERENCES bien_tipo(bien_tipo_id);

-- Bien → Estado
ALTER TABLE bien 
    ADD CONSTRAINT fk_bien_estado 
    FOREIGN KEY (estado_id) REFERENCES estado(estado_id);

-- Persona → Cargo
ALTER TABLE persona 
    ADD CONSTRAINT fk_persona_cargo 
    FOREIGN KEY (cargo_id) REFERENCES cargo(cargo_id);

-- Asignación → Área
ALTER TABLE asignacion 
    ADD CONSTRAINT fk_asignacion_area 
    FOREIGN KEY (area_id) REFERENCES area(area_id);

-- Asignación → Persona
ALTER TABLE asignacion 
    ADD CONSTRAINT fk_asignacion_persona 
    FOREIGN KEY (persona_id) REFERENCES persona(persona_id);

-- Asignación Bien → Asignación
ALTER TABLE asignacion_bien 
    ADD CONSTRAINT fk_asignacion_bien_asignacion 
    FOREIGN KEY (asignacion_id) REFERENCES asignacion(asignacion_id);

-- Asignación Bien → Bien
ALTER TABLE asignacion_bien 
    ADD CONSTRAINT fk_asignacion_bien_bien 
    FOREIGN KEY (bien_id) REFERENCES bien(bien_id);

-- Ajuste Bien → Ajuste
ALTER TABLE ajuste_bien 
    ADD CONSTRAINT fk_ajuste_bien_ajuste 
    FOREIGN KEY (ajuste_id) REFERENCES ajuste(ajuste_id);

-- Ajuste Bien → Bien
ALTER TABLE ajuste_bien 
    ADD CONSTRAINT fk_ajuste_bien_bien 
    FOREIGN KEY (bien_id) REFERENCES bien(bien_id);

-- Datos de ejemplo
INSERT INTO categoria (categoria_nombre, categoria_codigo, categoria_tipo) VALUES ('Tecnologico', 'TEC001', 1), ('Mobiliario', 'MOB001', 0);
INSERT INTO rol (rol_nombre) VALUES ('Administrador'), ('Administrador Principal'), ('Ingeniero');
INSERT INTO estado (estado_nombre) VALUES ('Disponible'), ('Asignado'), ('Mantenimiento'), ('Desincorporado');

-- Registros de Usuario (el primero es el principal)
INSERT INTO usuario (
    usuario_nombre, usuario_apellido, usuario_correo, usuario_telefono,
    usuario_sexo, usuario_cedula, usuario_nac, usuario_direccion,
    usuario_clave, usuario_usuario, rol_id, usuario_foto
) VALUES
('Administrador', 'Principal', 'admin@gmail.com', '', 0, 'V-11111111', '2005-11-14', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'admin123', 3, 'img/icons/ing.png'),
('Administrador', '1', 'admin.1@gmail.com', '04141230001', 0, 'V-11111101', '2000-01-01', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'dayana01', 2, 'img/icons/perfil.png'),
('María', 'González', 'maria.gonzalez@gmail.com', '04141230002', 1, 'V-11111102', '2000-02-02', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'maria02', 1, 'img/icons/perfil.png'),
('Luis', 'Fernández', 'luis.fernandez@gmail.com', '04141230003', 0, 'V-11111103', '2000-03-03', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'luis03', 1, 'img/icons/perfil.png'),
('Ana', 'Torres', 'ana.torres@gmail.com', '04141230004', 1, 'V-11111104', '2000-04-04', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'ana04', 1, 'img/icons/perfil.png'),
('José', 'Martínez', 'jose.martinez@gmail.com', '04141230005', 0, 'V-11111105', '2000-05-05', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'jose05', 1, 'img/icons/perfil.png'),
('Laura', 'Pérez', 'laura.perez@gmail.com', '04141230006', 1, 'V-11111106', '2000-06-06', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'laura06', 1, 'img/icons/perfil.png'),
('Miguel', 'Rodríguez', 'miguel.rodriguez@gmail.com', '04141230007', 0, 'V-11111107', '2000-07-07', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'miguel07', 1, 'img/icons/perfil.png'),
('Sofía', 'Morales', 'sofia.morales@gmail.com', '04141230008', 1, 'V-11111108', '2000-08-08', '', '$2y$10$nX5HEVQrpwMp8cLUKZ88OewI8p8t2rU/SrcrCuuYzCCplsRl9TF2i', 'sofia08', 1, 'img/icons/perfil.png');

-- Registros de clasificaciones
INSERT INTO clasificacion (
    clasificacion_codigo,
    clasificacion_nombre,
    categoria_id,
    clasificacion_descripcion,
    clasificacion_estado
) VALUES
('TEC-A01', 'Laptop HP ProBook', 1, 'Equipo portátil para uso administrativo', 1),
('TEC-B02', 'Monitor LG 24"', 1, 'Pantalla LED para estaciones de trabajo', 1),
('TEC-C03', 'Impresora Epson L3250', 1, 'Impresión multifuncional con sistema continuo', 1),
('TEC-D04', 'Router TP-Link AX1800', 1, 'Red WiFi para oficinas pequeñas', 1),
('TEC-E05', 'Teclado Logitech K120', 1, '', 1),

('MOB-A06', 'Escritorio Ejecutivo', 2, 'Madera laminada con gavetas', 1),
('MOB-B07', 'Silla Ergonómica', 2, 'Respaldo ajustable y ruedas giratorias', 1),
('MOB-C08', 'Archivador Metálico', 2, '3 gavetas con cerradura', 1),
('MOB-D09', 'Mesa de Reunión Ovalada', 2, '', 1),
('MOB-E10', 'Estantería Modular', 2, 'Ideal para almacenamiento de documentos', 1),

('OTR-A11', 'Extintor de CO2', 3, 'Equipo de seguridad contra incendios', 1),
('OTR-B12', 'Reloj de Pared', 3, '', 1),
('OTR-C13', 'Dispensador de Agua', 3, 'Sistema de enfriamiento y calentamiento', 1),
('OTR-D14', 'Caja de Herramientas', 3, 'Contiene destornilladores, llaves y martillo', 1),
('OTR-E15', 'Botiquín de Primeros Auxilios', 3, '', 1),

('TEC-F16', 'Tablet Samsung Galaxy Tab A', 1, 'Uso para inspecciones móviles', 1),
('TEC-G17', 'Cámara Web Logitech C920', 1, '', 1),
('MOB-F18', 'Panel Divisor de Oficina', 2, 'Separador acústico de espacios', 1),
('MOB-G19', 'Lámpara de Escritorio LED', 2, '', 1),
('OTR-F20', 'Pizarrón Acrílico Blanco', 3, 'Uso en salas de capacitación', 1);

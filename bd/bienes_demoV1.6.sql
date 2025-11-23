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
    categoria_tipo TINYINT(1) NOT NULL, -- 1 = Completo (modelo/marca), 0 = Básico
    categoria_descripcion VARCHAR(200),
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

-- Tabla de estados (operativos del serial)
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

-- Tabla de tipo de artículos (antes bien_tipo)
CREATE TABLE articulo (
    articulo_id INT NOT NULL AUTO_INCREMENT,
    articulo_codigo VARCHAR(20) NOT NULL,
    clasificacion_id INT NOT NULL,
    articulo_nombre VARCHAR(100) NOT NULL,
    articulo_modelo VARCHAR(100),
    marca_id INT,
    articulo_descripcion VARCHAR(200),
    articulo_estado TINYINT(1) NOT NULL DEFAULT 1,
    articulo_imagen VARCHAR(255),
    PRIMARY KEY (articulo_id)
);

-- Tabla de artículos (serializados/instancias) (antes bien)
CREATE TABLE articulo_serial (
    articulo_serial_id INT NOT NULL AUTO_INCREMENT,
    articulo_id INT NOT NULL,
    articulo_serial VARCHAR(100),
    estado_id INT DEFAULT 1,
    PRIMARY KEY (articulo_serial_id)
);

-- Tabla de cargos
CREATE TABLE cargo (
    cargo_id INT NOT NULL AUTO_INCREMENT,
    cargo_codigo VARCHAR(20) NOT NULL,
    cargo_nombre VARCHAR(100) NOT NULL,
    cargo_descripcion VARCHAR(200),
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

-- Tabla de notas de asignación
CREATE TABLE asignacion (
    asignacion_id INT NOT NULL AUTO_INCREMENT,
    area_id INT NOT NULL,
    persona_id INT NOT NULL,
    asignacion_fecha DATE NOT NULL,
    asignacion_fecha_fin DATE,
    asignacion_estado TINYINT(2) NOT NULL DEFAULT 1,
    PRIMARY KEY (asignacion_id)
);

-- Tabla de artículos asignados (antes asignacion_bien)
CREATE TABLE asignacion_articulo (
    asignacion_articulo_id INT NOT NULL AUTO_INCREMENT,
    articulo_serial_id INT NOT NULL,
    asignacion_id INT NOT NULL,
    PRIMARY KEY (asignacion_articulo_id)
);

-- Tabla de recepción/desincorporación
CREATE TABLE ajuste (
    ajuste_id INT NOT NULL AUTO_INCREMENT,
    ajuste_fecha DATE NOT NULL,
    ajuste_descripcion VARCHAR(200),
    ajuste_tipo TINYINT(2) NOT NULL, -- 1 = Entrada, 0 = Salida
    PRIMARY KEY (ajuste_id)
);

-- Tabla de artículos por recepción/desincorporación (antes ajuste_bien)
CREATE TABLE ajuste_articulo (
    ajuste_articulo_id INT NOT NULL AUTO_INCREMENT,
    articulo_serial_id INT NOT NULL,
    ajuste_id INT NOT NULL,
    PRIMARY KEY (ajuste_articulo_id)
);

-- Índices y valores únicos
ALTER TABLE clasificacion ADD UNIQUE (clasificacion_codigo);
ALTER TABLE area ADD UNIQUE (area_codigo);
ALTER TABLE marca ADD UNIQUE (marca_codigo);

CREATE INDEX idx_usuario_usuario ON usuario(usuario_usuario);
CREATE INDEX idx_usuario_cedula ON usuario(usuario_cedula);
CREATE INDEX idx_persona_cedula ON persona(persona_cedula);

-- Índices útiles para FKs y filtros
CREATE INDEX idx_clasificacion_categoria ON clasificacion(categoria_id);
CREATE INDEX idx_articulo_clasificacion ON articulo(clasificacion_id);
CREATE INDEX idx_articulo_marca ON articulo(marca_id);
CREATE INDEX idx_serial_articulo ON articulo_serial(articulo_id);
CREATE INDEX idx_serial_estado ON articulo_serial(estado_id);
CREATE INDEX idx_asignacion_area ON asignacion(area_id);
CREATE INDEX idx_asignacion_persona ON asignacion(persona_id);
CREATE INDEX idx_asig_articulo_serial ON asignacion_articulo(articulo_serial_id);
CREATE INDEX idx_asig_articulo_asignacion ON asignacion_articulo(asignacion_id);
CREATE INDEX idx_ajuste_articulo_serial ON ajuste_articulo(articulo_serial_id);
CREATE INDEX idx_ajuste_articulo_ajuste ON ajuste_articulo(ajuste_id);

    -- Claves foráneas
    -- Usuario → Rol
    ALTER TABLE usuario 
    ADD CONSTRAINT fk_usuario_rol 
    FOREIGN KEY (rol_id) REFERENCES rol(rol_id);

    -- Clasificación → Categoría
    ALTER TABLE clasificacion 
    ADD CONSTRAINT fk_clasificacion_categoria 
    FOREIGN KEY (categoria_id) REFERENCES categoria(categoria_id);

    -- Artículo → Clasificación (antes bien_tipo → clasificacion)
    ALTER TABLE articulo 
    ADD CONSTRAINT fk_articulo_clasificacion 
    FOREIGN KEY (clasificacion_id) REFERENCES clasificacion(clasificacion_id);

    -- Artículo → Marca (antes bien_tipo → marca)
    ALTER TABLE articulo 
    ADD CONSTRAINT fk_articulo_marca 
    FOREIGN KEY (marca_id) REFERENCES marca(marca_id);

    -- Artículo serial → Artículo (antes bien → bien_tipo)
    ALTER TABLE articulo_serial 
    ADD CONSTRAINT fk_articulo_serial_articulo 
    FOREIGN KEY (articulo_id) REFERENCES articulo(articulo_id);

    -- Artículo serial → Estado (antes bien → estado)
    ALTER TABLE articulo_serial 
    ADD CONSTRAINT fk_articulo_serial_estado 
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

    -- Asignación Artículo → Asignación (antes asignacion_bien)
    ALTER TABLE asignacion_articulo 
    ADD CONSTRAINT fk_asignacion_articulo_asignacion 
    FOREIGN KEY (asignacion_id) REFERENCES asignacion(asignacion_id);

    -- Asignación Artículo → Artículo serial (antes asignacion_bien → bien)
    ALTER TABLE asignacion_articulo 
    ADD CONSTRAINT fk_asignacion_articulo_serial 
    FOREIGN KEY (articulo_serial_id) REFERENCES articulo_serial(articulo_serial_id);

    -- Ajuste Artículo → Ajuste (antes ajuste_bien)
    ALTER TABLE ajuste_articulo 
    ADD CONSTRAINT fk_ajuste_articulo_ajuste 
    FOREIGN KEY (ajuste_id) REFERENCES ajuste(ajuste_id);

    -- Ajuste Artículo → Artículo serial (antes ajuste_bien → bien)
    ALTER TABLE ajuste_articulo 
    ADD CONSTRAINT fk_ajuste_articulo_serial 
    FOREIGN KEY (articulo_serial_id) REFERENCES articulo_serial(articulo_serial_id);

-- Datos por defecto
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

-- Registros de clasificaciones (ajustados)
INSERT INTO clasificacion (
    clasificacion_codigo,
    clasificacion_nombre,
    categoria_id,
    clasificacion_descripcion,
    clasificacion_estado
) VALUES
-- TECNOLÓGICO (categoria_id = 1)
('TEC-A01', 'PORTÁTILES', 1, 'EQUIPOS PORTÁTILES PARA USO ADMINISTRATIVO', 1),
('TEC-B02', 'MONITORES', 1, 'PANTALLAS PARA ESTACIONES DE TRABAJO', 1),
('TEC-C03', 'IMPRESORAS', 1, 'EQUIPOS DE IMPRESIÓN MULTIFUNCIONAL', 1),
('TEC-D04', 'REDES', 1, 'EQUIPOS DE CONECTIVIDAD Y REDES', 1),
('TEC-E05', 'PERIFÉRICOS', 1, 'TECLADOS, RATONES Y OTROS DISPOSITIVOS', 1),
('TEC-F16', 'TABLETS', 1, 'DISPOSITIVOS MÓVILES PARA INSPECCIONES', 1),
('TEC-G17', 'ACCESORIOS DIGITALES', 1, 'CÁMARAS WEB Y OTROS ACCESORIOS', 1),

-- MOBILIARIO (categoria_id = 2)
('MOB-A06', 'ESCRITORIOS', 2, 'MOBILIARIO PARA ESTACIONES DE TRABAJO', 1),
('MOB-B07', 'SILLAS', 2, 'MOBILIARIO ERGONÓMICO PARA OFICINA', 1),
('MOB-C08', 'ARCHIVADORES', 2, 'MOBILIARIO PARA ALMACENAMIENTO DE DOCUMENTOS', 1),
('MOB-D09', 'MESAS DE REUNIÓN', 2, 'MOBILIARIO PARA SALAS DE REUNIÓN', 1),
('MOB-E10', 'ESTANTERÍAS', 2, 'MOBILIARIO PARA ORGANIZACIÓN DE DOCUMENTOS', 1),
('MOB-F18', 'DIVISORES DE OFICINA', 2, 'MOBILIARIO PARA SEPARACIÓN DE ESPACIOS', 1),
('MOB-G19', 'ILUMINACIÓN DE OFICINA', 2, 'MOBILIARIO DE APOYO EN ILUMINACIÓN', 1);

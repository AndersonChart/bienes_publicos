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
    articulo_serial_observacion VARCHAR(200),
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
    persona_direccion VARCHAR(100),
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
    asignacion_descripcion VARCHAR(200),
    asignacion_estado TINYINT(1) NOT NULL DEFAULT 1, -- 2 = Vencido, 1 = Activo y 0 = Deshabilitado
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
    ajuste_acta VARCHAR(255),
    ajuste_estado TINYINT(2) NOT NULL DEFAULT 1,
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

INSERT INTO categoria (categoria_codigo, categoria_nombre, categoria_tipo, categoria_descripcion, categoria_estado) VALUES
('MOB001','MOBILIARIO',0,'MOBILIARIO BÁSICO PARA OFICINAS Y SALAS DE REUNIÓN',1),
('MOB002','ILUMINACIÓN',0,'ILUMINACIÓN GENERAL PARA ESPACIOS DE TRABAJO',1),
('MOB003','DECORACIÓN',0,'ELEMENTOS DECORATIVOS PARA AMBIENTES LABORALES',1),
('MOB004','DIVISIONES',0,'DIVISIONES Y PANELERÍA PARA SEPARAR ÁREAS DE TRABAJO',1),
('MOB005','ARCHIVOS',0,'MOBILIARIO PARA ARCHIVO Y ORGANIZACIÓN DOCUMENTAL',1),
('MOB006','MESAS',0,'',1),
('MOB007','SILLAS',0,'',1),
('MOB008','ESTANTERÍAS',0,'',1),
('MOB009','PIZARRAS',0,'',1),
('MOB010','RECEPCIÓN',0,'',1),
('TEC001','TECNOLÓGICO',1,'EQUIPOS TECNOLÓGICOS PARA USO ADMINISTRATIVO Y OPERATIVO',1),
('TEC002','ELECTRODOMÉSTICO',1,'ELECTRODOMÉSTICOS DE APOYO EN ÁREAS DE SERVICIO',1),
('TEC003','COMUNICACIONES',1,'DISPOSITIVOS DE COMUNICACIÓN Y REDES CORPORATIVAS',1),
('TEC004','AUDIOVISUAL',1,'EQUIPOS AUDIOVISUALES PARA PRESENTACIONES Y CAPACITACIONES',1),
('TEC005','SEGURIDAD',1,'DISPOSITIVOS DE SEGURIDAD Y CONTROL DE ACCESO',1),
('TEC006','PORTÁTILES',1,'',1),
('TEC007','MONITORES',1,'',1),
('TEC008','IMPRESORAS',1,'',1),
('TEC009','TABLETS',1,'',1),
('TEC010','PERIFÉRICOS',1,'',1);

INSERT INTO clasificacion (clasificacion_codigo, clasificacion_nombre, categoria_id, clasificacion_descripcion, clasificacion_estado) VALUES
('MOB-C01','MESAS DE OFICINA',1,'MESAS DE TRABAJO PARA PERSONAL ADMINISTRATIVO',1),
('MOB-C02','SILLAS ERGONÓMICAS',1,'SILLAS DISEÑADAS PARA CONFORT Y ERGONOMÍA',1),
('MOB-C03','ARCHIVADORES METÁLICOS',1,'ARCHIVADORES RESISTENTES PARA DOCUMENTOS IMPORTANTES',1),
('ILU-C04','LÁMPARAS LED',2,'ILUMINACIÓN EFICIENTE PARA ESPACIOS DE TRABAJO',1),
('ILU-C05','PANELES DE LUZ',2,'',1),
('DEC-C06','CUADROS DECORATIVOS',3,'',1),
('DIV-C07','PANELES DIVISORES',4,'DIVISIONES PARA SEPARAR ÁREAS DE TRABAJO',1),
('ARC-C08','ARCHIVOS DE MADERA',5,'',1),
('TEC-C09','LAPTOPS',11,'EQUIPOS PORTÁTILES PARA USO ADMINISTRATIVO',1),
('TEC-C10','MONITORES',11,'PANTALLAS PARA ESTACIONES DE TRABAJO',1),
('TEC-C11','IMPRESORAS',11,'EQUIPOS DE IMPRESIÓN MULTIFUNCIONAL',1),
('TEC-C12','TABLETS',11,'DISPOSITIVOS MÓVILES PARA INSPECCIONES',1),
('TEC-C13','PERIFÉRICOS',11,'TECLADOS, RATONES Y OTROS DISPOSITIVOS',1),
('ELE-C14','REFRIGERADORES',12,'ELECTRODOMÉSTICOS PARA ÁREAS DE SERVICIO',1),
('ELE-C15','MICROONDAS',12,'',1),
('COM-C16','TELÉFONOS FIJOS',13,'DISPOSITIVOS DE COMUNICACIÓN CORPORATIVA',1),
('COM-C17','RADIOS DE BANDA',13,'',1),
('AUD-C18','PROYECTORES',14,'EQUIPOS AUDIOVISUALES PARA PRESENTACIONES',1),
('SEG-C19','CÁMARAS DE SEGURIDAD',15,'DISPOSITIVOS DE SEGURIDAD Y CONTROL DE ACCESO',1),
('SEG-C20','SENSORES DE MOVIMIENTO',15,'',1);

INSERT INTO area (area_codigo, area_nombre, area_descripcion, area_estado) VALUES
('AR001','RECURSOS HUMANOS','GESTIÓN DE PERSONAL Y PROCESOS ADMINISTRATIVOS',1),
('AR002','CONTRALORÍA','SUPERVISIÓN DE PROCESOS FINANCIEROS Y ADMINISTRATIVOS',1),
('AR003','SERVICIO TÉCNICO','SOPORTE Y MANTENIMIENTO DE EQUIPOS',1),
('AR004','REDES Y TELECOMUNICACIONES','ADMINISTRACIÓN DE REDES Y SISTEMAS DE COMUNICACIÓN',1),
('AR005','LOGÍSTICA','COORDINACIÓN DE INVENTARIOS Y DISTRIBUCIÓN',1),
('AR006','ALMACÉN','CONTROL DE BIENES Y SUMINISTROS',1),
('AR007','COMPRAS','GESTIÓN DE ADQUISICIONES DE BIENES Y SERVICIOS',1),
('AR008','VENTAS','GESTIÓN DE CLIENTES Y PROCESOS COMERCIALES',1),
('AR009','ATENCIÓN AL CLIENTE','SERVICIO Y SOPORTE A USUARIOS',1),
('AR010','GERENCIA GENERAL','DIRECCIÓN Y SUPERVISIÓN DE LA ORGANIZACIÓN',1),
('AR011','AUDITORÍA','',1),
('AR012','TESORERÍA','',1),
('AR013','CONTABILIDAD','',1),
('AR014','MARKETING','',1),
('AR015','PUBLICIDAD','',1),
('AR016','CAPACITACIÓN','',1),
('AR017','PROYECTOS','',1),
('AR018','DESARROLLO DE SOFTWARE','',1),
('AR019','CALIDAD','',1),
('AR020','PLANIFICACIÓN','',1);

INSERT INTO marca (marca_codigo, marca_nombre, marca_imagen, marca_estado) VALUES
('MAR001','HP','img/icons/marca.png',1),
('MAR002','DELL','img/icons/marca.png',1),
('MAR003','LENOVO','img/icons/marca.png',1),
('MAR004','APPLE','img/icons/marca.png',1),
('MAR005','SAMSUNG','img/icons/marca.png',1),
('MAR006','SONY','img/icons/marca.png',1),
('MAR007','LG','img/icons/marca.png',1),
('MAR008','ASUS','img/icons/marca.png',1),
('MAR009','ACER','img/icons/marca.png',1),
('MAR010','TOSHIBA','img/icons/marca.png',1),
('MAR011','HUAWEI','img/icons/marca.png',1),
('MAR012','XIAOMI','img/icons/marca.png',1),
('MAR013','NOKIA','img/icons/marca.png',1),
('MAR014','PANASONIC','img/icons/marca.png',1),
('MAR015','PHILIPS','img/icons/marca.png',1),
('MAR016','MICROSOFT','img/icons/marca.png',1),
('MAR017','IBM','img/icons/marca.png',1),
('MAR018','CISCO','img/icons/marca.png',1),
('MAR019','INTEL','img/icons/marca.png',1),
('MAR020','AMD','img/icons/marca.png',1);


INSERT INTO cargo (cargo_codigo, cargo_nombre, cargo_descripcion, cargo_estado) VALUES
('CAR001','ANALISTA','RESPONSABLE DE ANÁLISIS DE PROCESOS ADMINISTRATIVOS',1),
('CAR002','ATENCIÓN AL CLIENTE','RESPONSABLE DE SOPORTE Y SERVICIO A USUARIOS',1),
('CAR003','GERENTE','RESPONSABLE DE DIRECCIÓN Y SUPERVISIÓN DE ÁREAS',1),
('CAR004','AUDITOR','RESPONSABLE DE REVISIÓN DE PROCESOS FINANCIEROS',1),
('CAR005','TESORERO','RESPONSABLE DE MANEJO DE FONDOS Y TESORERÍA',1),
('CAR006','CONTADOR','RESPONSABLE DE REGISTROS CONTABLES Y FINANCIEROS',1),
('CAR007','MARKETING','RESPONSABLE DE PROMOCIÓN Y PUBLICIDAD',1),
('CAR008','PUBLICISTA','RESPONSABLE DE CAMPAÑAS PUBLICITARIAS',1),
('CAR009','CAPACITADOR','RESPONSABLE DE FORMACIÓN DE PERSONAL',1),
('CAR010','JEFE DE PROYECTOS','RESPONSABLE DE COORDINAR LOS PROYECTOS',1),
('CAR011','ASISTENTE','',1),
('CAR012','SECRETARIA','',1),
('CAR013','OPERADOR','',1),
('CAR014','SUPERVISOR','',1),
('CAR015','COORDINADOR','',1),
('CAR016','INGENIERO','',1),
('CAR017','TÉCNICO','',1),
('CAR018','PROGRAMADOR','',1),
('CAR019','DISEÑADOR','',1),
('CAR020','AUXILIAR','',1);

INSERT INTO persona (persona_nombre, persona_apellido, cargo_id, persona_correo, persona_telefono, persona_cedula, persona_sexo, persona_nac, persona_direccion, persona_foto, persona_estado) VALUES
('JUAN','PÉREZ',1,'JUAN.PEREZ@EMPRESA.COM','04121234567','V-12345678',1,'1985-05-10','CALLE PRINCIPAL EDIFICIO A','',1),
('MARÍA','GÓMEZ',2,'MARIA.GOMEZ@EMPRESA.COM','04121234568','V-22345678',0,'1990-07-15','AVENIDA B SECTOR CENTRO','',1),
('CARLOS','RAMÍREZ',3,'CARLOS.RAMIREZ@EMPRESA.COM','04121234569','V-32345678',1,'1982-03-20','URBANIZACIÓN LOS PINOS','',1),
('ANA','TORRES',4,'ANA.TORRES@EMPRESA.COM','04121234570','V-42345678',0,'1988-11-25','CALLE 5 CASA 10','',1),
('PEDRO','MARTÍNEZ',5,'PEDRO.MARTINEZ@EMPRESA.COM','04121234571','V-52345678',1,'1979-09-12','AVENIDA PRINCIPAL BLOQUE 3','',1),
('LUISA','FERNÁNDEZ',6,'LUISA.FERNANDEZ@EMPRESA.COM','04121234572','V-62345678',0,'1992-01-30','CALLE 8 SECTOR NORTE','',1),
('JORGE','SUÁREZ',7,'JORGE.SUAREZ@EMPRESA.COM','04121234573','V-72345678',1,'1987-06-18','URBANIZACIÓN EL CENTRO','',1),
('SOFÍA','DÍAZ',8,'SOFIA.DIAZ@EMPRESA.COM','04121234574','V-82345678',0,'1995-04-22','CALLE 12 CASA 4','',1),
('MIGUEL','CASTRO',9,'MIGUEL.CASTRO@EMPRESA.COM','04121234575','V-92345678',1,'1983-08-05','AVENIDA SUR BLOQUE 2','',1),
('CARMEN','RODRÍGUEZ',10,'CARMEN.RODRIGUEZ@EMPRESA.COM','04121234576','V-102345678',0,'1986-12-14','CALLE PRINCIPAL CASA 7','',1),
('RAFAEL','LOPEZ',11,'RAFAEL.LOPEZ@EMPRESA.COM','','V-112345678',1,'1984-02-11','','',1),
('PATRICIA','MENDOZA',12,'PATRICIA.MENDOZA@EMPRESA.COM','','V-122345678',0,'1991-07-09','','',1),
('DANIEL','HERRERA',13,'DANIEL.HERRERA@EMPRESA.COM','','E-132345678',1,'1980-10-03','','',1),
('VERÓNICA','RAMOS',14,'VERONICA.RAMOS@EMPRESA.COM','','E-142345678',0,'1989-05-27','','',1),
('ALBERTO','GARCÍA',15,'ALBERTO.GARCIA@EMPRESA.COM','','E-152345678',1,'1978-03-19','','',1),
('NATALIA','MORALES',16,'NATALIA.MORALES@EMPRESA.COM','','E-162345678',0,'1993-09-21','','',1);

INSERT INTO articulo (articulo_codigo, clasificacion_id, articulo_nombre, articulo_modelo, marca_id, articulo_descripcion, articulo_estado, articulo_imagen) VALUES
('ART001',9,'LAPTOP HP','ELITEBOOK 840',1,'EQUIPO PORTÁTIL PARA USO ADMINISTRATIVO',1,'img/icons/articulo.png'),
('ART002',9,'LAPTOP DELL','LATITUDE 5500',2,'EQUIPO PORTÁTIL PARA USO GERENCIAL',1,'img/icons/articulo.png'),
('ART003',10,'MONITOR SAMSUNG','SYNCMASTER 24',5,'PANTALLA DE ALTA DEFINICIÓN PARA OFICINA',1,'img/icons/articulo.png'),
('ART004',10,'MONITOR LG','ULTRAWIDE 29',7,'PANTALLA ULTRA ANCHA PARA MULTITAREA',1,'img/icons/articulo.png'),
('ART005',11,'IMPRESORA HP','LASERJET PRO',1,'IMPRESORA MULTIFUNCIONAL PARA DOCUMENTOS',1,'img/icons/articulo.png'),
('ART006',11,'IMPRESORA EPSON','WORKFORCE WF',15,'IMPRESORA DE ALTO RENDIMIENTO',1,'img/icons/articulo.png'),
('ART007',12,'TABLET APPLE','IPAD AIR',4,'DISPOSITIVO MÓVIL PARA INSPECCIONES',1,'img/icons/articulo.png'),
('ART008',12,'TABLET SAMSUNG','GALAXY TAB',5,'DISPOSITIVO MÓVIL PARA USO CORPORATIVO',1,'img/icons/articulo.png'),
('ART009',13,'TECLADO LOGITECH','K120',6,'TECLADO ESTÁNDAR PARA OFICINA',1,'img/icons/articulo.png'),
('ART010',13,'MOUSE HP','M100',1,'DISPOSITIVO DE ENTRADA PARA COMPUTADORAS',1,'img/icons/articulo.png'),

-- MOBILIARIO (marca_id = NULL)
('ART011',1,'ESCRITORIO EJECUTIVO','',NULL,'MOBILIARIO DE OFICINA PARA GERENCIA',1,'img/icons/articulo.png'),
('ART012',1,'SILLA ERGONÓMICA','',NULL,'MOBILIARIO DE OFICINA PARA PERSONAL ADMINISTRATIVO',1,'img/icons/articulo.png'),
('ART013',1,'ARCHIVADOR METÁLICO','',NULL,'MOBILIARIO PARA ORGANIZACIÓN DE DOCUMENTOS',1,'img/icons/articulo.png'),
('ART014',1,'MESA DE REUNIÓN','',NULL,'MOBILIARIO PARA SALAS DE REUNIÓN',1,'img/icons/articulo.png'),
('ART015',1,'ESTANTERÍA DE MADERA','',NULL,'MOBILIARIO PARA ALMACENAMIENTO DE LIBROS Y ARCHIVOS',1,'img/icons/articulo.png'),
('ART016',1,'DIVISOR DE OFICINA','',NULL,'MOBILIARIO PARA SEPARACIÓN DE ESPACIOS DE TRABAJO',1,'img/icons/articulo.png'),
('ART017',1,'PIZARRA ACRÍLICA','',NULL,'MOBILIARIO PARA PRESENTACIONES Y CAPACITACIONES',1,'img/icons/articulo.png'),
('ART018',1,'RECEPCIÓN','',NULL,'MOBILIARIO PARA ÁREA DE ATENCIÓN AL CLIENTE',1,'img/icons/articulo.png'),
('ART019',1,'ILUMINACIÓN LED','',NULL,'MOBILIARIO DE APOYO EN ILUMINACIÓN DE OFICINA',1,'img/icons/articulo.png'),
('ART020',1,'DECORACIÓN DE PAREDES','',NULL,'MOBILIARIO DECORATIVO PARA AMBIENTES LABORALES',1,'img/icons/articulo.png'),

-- ELECTRODOMÉSTICOS
('ART021',14,'REFRIGERADOR LG','GR-B202SQBB',7,'ELECTRODOMÉSTICO PARA ÁREA DE SERVICIO',1,'img/icons/articulo.png'),
('ART022',14,'MICROONDAS SAMSUNG','ME9114ST',5,'ELECTRODOMÉSTICO DE APOYO EN ÁREA DE COMEDOR',1,'img/icons/articulo.png'),
('ART023',14,'CAFETERA OSTER','BVSTDC',14,'ELECTRODOMÉSTICO PARA PREPARACIÓN DE BEBIDAS',1,'img/icons/articulo.png'),
('ART024',14,'LICUADORA PHILIPS','HR2056',15,'ELECTRODOMÉSTICO PARA ÁREA DE COCINA',1,'img/icons/articulo.png'),
('ART025',14,'VENTILADOR ACER','VF100',9,'ELECTRODOMÉSTICO DE APOYO EN ÁREA DE SERVICIO',1,'img/icons/articulo.png'),

-- COMUNICACIONES
('ART026',18,'TELÉFONO FIJO PANASONIC','KX-TS880',14,'DISPOSITIVO DE COMUNICACIÓN CORPORATIVA',1,'img/icons/articulo.png'),
('ART027',18,'RADIO MOTOROLA','XT420',13,'DISPOSITIVO DE COMUNICACIÓN EN ÁREAS DE SERVICIO',1,'img/icons/articulo.png'),
('ART028',18,'TELÉFONO IP CISCO','SPA504G',18,'DISPOSITIVO DE COMUNICACIÓN PARA REDES CORPORATIVAS',1,'img/icons/articulo.png'),
('ART029',18,'INTERCOMUNICADOR HUAWEI','IC200',11,'DISPOSITIVO DE COMUNICACIÓN INTERNA',1,'img/icons/articulo.png'),
('ART030',18,'TELÉFONO ANALÓGICO NOKIA','T100',13,'DISPOSITIVO DE COMUNICACIÓN BÁSICA',1,'img/icons/articulo.png'),

-- AUDIOVISUAL
('ART031',16,'PROYECTOR EPSON','EB-X41',15,'EQUIPO AUDIOVISUAL PARA PRESENTACIONES',1,'img/icons/articulo.png'),
('ART032',16,'PROYECTOR BENQ','MS550',10,'EQUIPO AUDIOVISUAL PARA CAPACITACIONES',1,'img/icons/articulo.png'),
('ART033',16,'PANTALLA SONY','BRAVIA 55',6,'EQUIPO AUDIOVISUAL PARA SALAS DE REUNIÓN',1,'img/icons/articulo.png'),
('ART034',16,'SISTEMA DE SONIDO LG','XBOOM',7,'EQUIPO AUDIOVISUAL PARA EVENTOS CORPORATIVOS',1,'img/icons/articulo.png'),
('ART035',16,'MICRÓFONO SHURE','SM58',6,'EQUIPO AUDIOVISUAL PARA PRESENTACIONES',1,'img/icons/articulo.png'),

-- SEGURIDAD
('ART036',17,'CÁMARA DE SEGURIDAD HIKVISION','DS-2CD2143G0',19,'DISPOSITIVO DE SEGURIDAD PARA MONITOREO',1,'img/icons/articulo.png'),
('ART037',17,'SENSOR DE MOVIMIENTO PHILIPS','SM100',15,'DISPOSITIVO DE SEGURIDAD PARA DETECCIÓN',1,'img/icons/articulo.png'),
('ART038',17,'ALARMA DE INCENDIO','AI100',15,'DISPOSITIVO DE SEGURIDAD PARA PREVENCIÓN DE RIESGOS',1,'img/icons/articulo.png'),
('ART039',17,'CONTROL DE ACCESO ZKTECO','F18',18,'DISPOSITIVO DE SEGURIDAD PARA INGRESO DE PERSONAL',1,'img/icons/articulo.png'),
('ART040',17,'CERRADURA DIGITAL SAMSUNG','DL100',5,'DISPOSITIVO DE SEGURIDAD PARA ÁREAS DE OFICINA',1,'img/icons/articulo.png');

-- Schema recomendado para "Casa Bonsái"
-- Ejecutar en la base de datos `casabonsai` (por ejemplo: mysql -u root -p casabonsai < schema.sql)

SET FOREIGN_KEY_CHECKS = 0;

-- Tabla usuarios (si ya existe, mantenla; aquí se propone una definición compatible)
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Nombres` VARCHAR(100) NOT NULL,
  `Apellidos` VARCHAR(150) DEFAULT NULL,
  `rol_id` INT UNSIGNED DEFAULT NULL,
  `Telefono` VARCHAR(30) DEFAULT NULL,
  `Correo` VARCHAR(150) DEFAULT NULL,
  `Direccion` TEXT DEFAULT NULL,
  `Usuario` VARCHAR(80) NOT NULL UNIQUE,
  `Contraseña` VARCHAR(255) NOT NULL,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  INDEX (`rol_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla servicios (cuidado, terapias, asistencia)
CREATE TABLE IF NOT EXISTS `servicio` (
  `id_servicio` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(120) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `precio` DECIMAL(10,2) DEFAULT NULL,
  `duracion_min` SMALLINT UNSIGNED DEFAULT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla productos (catálogo, venta)
CREATE TABLE IF NOT EXISTS `producto` (
  `id_producto` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock` INT DEFAULT 0,
  `sku` VARCHAR(64) DEFAULT NULL,
  `imagen` LONGTEXT DEFAULT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `creado_por` INT UNSIGNED DEFAULT NULL,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_producto`),
  INDEX (`nombre`),
  INDEX (`creado_por`),
  CONSTRAINT `fk_producto_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuario`(`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla categorías de producto (opcional)
CREATE TABLE IF NOT EXISTS `categoria_producto` (
  `id_categoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Relación producto <-> categoria (muchos a muchos)
CREATE TABLE IF NOT EXISTS `producto_categoria` (
  `producto_id` INT UNSIGNED NOT NULL,
  `categoria_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`producto_id`,`categoria_id`),
  INDEX (`categoria_id`),
  CONSTRAINT `fk_prodcat_producto` FOREIGN KEY (`producto_id`) REFERENCES `producto`(`id_producto`) ON DELETE CASCADE,
  CONSTRAINT `fk_prodcat_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria_producto`(`id_categoria`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla contactos/solicitudes desde el sitio (formulario de contacto)
CREATE TABLE IF NOT EXISTS `contacto` (
  `id_contacto` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NOT NULL,
  `correo` VARCHAR(150) DEFAULT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `mensaje` TEXT DEFAULT NULL,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contacto`),
  INDEX (`correo`),
  INDEX (`usuario_id`),
  CONSTRAINT `fk_contacto_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario`(`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla testimonios
CREATE TABLE IF NOT EXISTS `testimonio` (
  `id_testimonio` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) DEFAULT 'Anónimo',
  `mensaje` TEXT NOT NULL,
  `usuario_id` INT UNSIGNED DEFAULT NULL,
  `publicado` TINYINT(1) NOT NULL DEFAULT 0,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_testimonio`),
  INDEX (`usuario_id`),
  CONSTRAINT `fk_testimonio_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario`(`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla residentes (personas de la tercera edad / familiares)
-- Esta tabla contiene datos médicos/basicos y referencia opcional al usuario que la registra
CREATE TABLE IF NOT EXISTS `residente` (
  `id_residente` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED DEFAULT NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `apellidos` VARCHAR(150) DEFAULT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  `genero` VARCHAR(16) DEFAULT NULL,
  `enfermedades` TEXT DEFAULT NULL,
  `discapacidades` TEXT DEFAULT NULL,
  `medicacion` TEXT DEFAULT NULL,
  `alergias` TEXT DEFAULT NULL,
  `observaciones` TEXT DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `contacto_emergencia_nombre` VARCHAR(150) DEFAULT NULL,
  `contacto_emergencia_telefono` VARCHAR(50) DEFAULT NULL,
  `contacto_emergencia_relacion` VARCHAR(100) DEFAULT NULL,
  `creado_por` INT UNSIGNED DEFAULT NULL,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_residente`),
  INDEX (`id_usuario`),
  INDEX (`creado_por`),
  CONSTRAINT `fk_residente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_residente_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuario`(`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

<<<<<<< HEAD
-- Tabla proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id_proveedor` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `contacto` VARCHAR(255) DEFAULT NULL,
  `telefono` VARCHAR(50) DEFAULT NULL,
  `correo` VARCHAR(150) DEFAULT NULL,
  `direccion` TEXT DEFAULT NULL,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Movimientos de inventario (entradas / salidas)
CREATE TABLE IF NOT EXISTS `inventario_movimientos` (
  `id_movimiento` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_producto` INT UNSIGNED NOT NULL,
  `id_proveedor` INT UNSIGNED DEFAULT NULL,
  `cantidad` INT NOT NULL,
  `tipo` ENUM('entrada','salida') NOT NULL,
  `referencia` VARCHAR(255) DEFAULT NULL,
  `notas` TEXT DEFAULT NULL,
  `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimiento`),
  INDEX (`id_producto`),
  INDEX (`id_proveedor`),
  CONSTRAINT `fk_im_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto`(`id_producto`) ON DELETE CASCADE,
  CONSTRAINT `fk_im_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores`(`id_proveedor`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

=======
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
-- Rehabilitar comprobación de claves foráneas (se asumió creación previa de tablas)
SET FOREIGN_KEY_CHECKS = 1;

-- Roles y asignaciones de rol a usuario
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(60) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `usuario_rol` (
  `usuario_id` INT UNSIGNED NOT NULL,
  `rol_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`usuario_id`,`rol_id`),
  INDEX (`rol_id`),
  CONSTRAINT `fk_usuariorol_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuariorol_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol`(`id_rol`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Si quieres un campo de rol directo en `usuario` (un solo rol por usuario), añadimos la constraint:
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol_single` FOREIGN KEY (`rol_id`) REFERENCES `rol`(`id_rol`) ON DELETE SET NULL;

-- Añadir campos de auditoría (actualizado_at) en tablas clave si no existen
-- Nota: MySQL no soporta ADD COLUMN IF NOT EXISTS en versiones antiguas; si tu versión no lo soporta, ejecuta un ALTER manualmente o actualiza la sintaxis.
ALTER TABLE `producto` 
  ADD COLUMN `actualizado_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `servicio`
  ADD COLUMN `actualizado_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;


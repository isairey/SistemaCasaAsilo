
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

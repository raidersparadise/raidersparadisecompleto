-- =====================================================================
--  Raiders Paradise - Base de datos MySQL / MariaDB
--  Generado a partir de las migraciones de Laravel (database/migrations)
--  Fecha: 2026-10-28 00:51
--  Compatible con MySQL 8+ y MariaDB 10.4+ (XAMPP, Laragon, Docker)
--
--  Importar: phpMyAdmin > Importar > seleccionar este archivo > Continuar
--  o en consola:  mysql -u root -p < este_archivo.sql
--
--  ¡ATENCIÓN! Borra y vuelve a crear las tablas de la base `raidersparadise`.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '-05:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `raidersparadise` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `raidersparadise`;

-- Limpieza (permite volver a importar el archivo las veces que quieras)
DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `factura`;
DROP TABLE IF EXISTS `detalle_pedido`;
DROP TABLE IF EXISTS `pedido`;
DROP TABLE IF EXISTS `inventario`;
DROP TABLE IF EXISTS `detalle_carrito`;
DROP TABLE IF EXISTS `carrito`;
DROP TABLE IF EXISTS `producto`;
DROP TABLE IF EXISTS `proveedor`;
DROP TABLE IF EXISTS `marca`;
DROP TABLE IF EXISTS `categoria`;
DROP TABLE IF EXISTS `pqr`;
DROP TABLE IF EXISTS `cliente`;
DROP TABLE IF EXISTS `reporte`;
DROP TABLE IF EXISTS `usuario`;
DROP TABLE IF EXISTS `rol`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;

-- =====================================================================
--  ESTRUCTURA (tablas, llaves primarias, foráneas e índices)
-- =====================================================================

-- Control de migraciones de Laravel (le indica a `php artisan migrate` que todo ya está creado)
create table `migrations` (`id` int unsigned not null auto_increment primary key, `migration` varchar(255) not null, `batch` int not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

-- Migración: 00_00_laravel_base.php
create table `password_reset_tokens` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null, primary key (`email`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `sessions` (`id` varchar(255) not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` text null, `payload` longtext not null, `last_activity` int not null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `sessions` add index `sessions_user_id_index`(`user_id`);
alter table `sessions` add index `sessions_last_activity_index`(`last_activity`);
create table `cache` (`key` varchar(255) not null, `value` mediumtext not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `cache_locks` (`key` varchar(255) not null, `owner` varchar(255) not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(255) not null, `payload` longtext not null, `attempts` tinyint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `jobs` add index `jobs_queue_index`(`queue`);
create table `job_batches` (`id` varchar(255) not null, `name` varchar(255) not null, `total_jobs` int not null, `pending_jobs` int not null, `failed_jobs` int not null, `failed_job_ids` longtext not null, `options` mediumtext null, `cancelled_at` int null, `created_at` int not null, `finished_at` int null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` text not null, `queue` text not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`);

-- Migración: 01_rol.php
create table `rol` (`id_rol` bigint unsigned not null auto_increment primary key, `nombre_rol` varchar(40) not null, `descripcion` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `rol` add unique `rol_nombre_rol_unique`(`nombre_rol`);

-- Migración: 02_usuario.php
create table `usuario` (`id_usuario` bigint unsigned not null auto_increment primary key, `id_rol` bigint unsigned not null, `nombre_usuario` varchar(40) not null, `apellido_usuario` varchar(40) not null, `email` varchar(100) not null, `password` varchar(255) not null, `telefono` varchar(20) null, `activo` tinyint(1) not null default '1', `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `usuario` add constraint `usuario_id_rol_foreign` foreign key (`id_rol`) references `rol` (`id_rol`);
alter table `usuario` add unique `usuario_email_unique`(`email`);

-- Migración: 03_reporte.php
create table `reporte` (`id_reporte` bigint unsigned not null auto_increment primary key, `id_usuario` bigint unsigned not null, `tipo_reporte` varchar(100) not null, `formato` varchar(20) not null default 'pantalla', `fecha_desde` date null, `fecha_hasta` date null, `fecha_generacion` datetime not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `reporte` add constraint `reporte_id_usuario_foreign` foreign key (`id_usuario`) references `usuario` (`id_usuario`) on delete cascade;

-- Migración: 04_cliente.php
create table `cliente` (`id_cliente` bigint unsigned not null auto_increment primary key, `id_usuario` bigint unsigned null, `documento` varchar(20) null, `nombre_cliente` varchar(40) not null, `apellido_cliente` varchar(40) not null, `email` varchar(150) not null, `telefono` varchar(20) null, `direccion` varchar(100) null, `ciudad` varchar(60) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `cliente` add constraint `cliente_id_usuario_foreign` foreign key (`id_usuario`) references `usuario` (`id_usuario`) on delete set null;
alter table `cliente` add unique `cliente_id_usuario_unique`(`id_usuario`);
alter table `cliente` add unique `cliente_email_unique`(`email`);

-- Migración: 05_pqr.php
create table `pqr` (`id_pqr` bigint unsigned not null auto_increment primary key, `id_usuario` bigint unsigned null, `id_cliente` bigint unsigned not null, `tipo` enum('peticion', 'queja', 'reclamo', 'sugerencia') not null default 'peticion', `asunto` varchar(120) not null, `descripcion_pqr` varchar(1000) not null, `estado` enum('recibida', 'asignada', 'en proceso', 'requerido', 'cerrada', 'rechazada') not null default 'recibida', `respuesta` text null, `fecha` date not null, `fecha_cierre` datetime null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `pqr` add constraint `pqr_id_usuario_foreign` foreign key (`id_usuario`) references `usuario` (`id_usuario`) on delete set null;
alter table `pqr` add constraint `pqr_id_cliente_foreign` foreign key (`id_cliente`) references `cliente` (`id_cliente`) on delete cascade;

-- Migración: 06_categoria.php
create table `categoria` (`id_categoria` bigint unsigned not null auto_increment primary key, `nombre_categoria` varchar(40) not null, `descripcion_categoria` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `categoria` add unique `categoria_nombre_categoria_unique`(`nombre_categoria`);

-- Migración: 07_marca.php
create table `marca` (`id_marca` bigint unsigned not null auto_increment primary key, `nombre_marca` varchar(40) not null, `descripcion_marca` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `marca` add unique `marca_nombre_marca_unique`(`nombre_marca`);

-- Migración: 08_proveedor.php
create table `proveedor` (`id_proveedor` bigint unsigned not null auto_increment primary key, `nit` varchar(20) null, `nombre_proveedor` varchar(80) not null, `telefono_proveedor` varchar(20) null, `direccion_proveedor` varchar(100) null, `email_proveedor` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

-- Migración: 09_producto.php
create table `producto` (`id_producto` bigint unsigned not null auto_increment primary key, `id_categoria` bigint unsigned not null, `id_marca` bigint unsigned not null, `id_proveedor` bigint unsigned not null, `sku` varchar(30) not null, `nombre_producto` varchar(120) not null, `descripcion_producto` varchar(1000) null, `precio_producto` decimal(12, 2) not null, `estado_producto` varchar(20) not null default 'activo', `destacado` tinyint(1) not null default '0', `imagen_producto` varchar(255) null, `comentario_producto` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `producto` add constraint `producto_id_categoria_foreign` foreign key (`id_categoria`) references `categoria` (`id_categoria`);
alter table `producto` add constraint `producto_id_marca_foreign` foreign key (`id_marca`) references `marca` (`id_marca`);
alter table `producto` add constraint `producto_id_proveedor_foreign` foreign key (`id_proveedor`) references `proveedor` (`id_proveedor`);
alter table `producto` add unique `producto_sku_unique`(`sku`);

-- Migración: 10_carrito.php
create table `carrito` (`id_carrito` bigint unsigned not null auto_increment primary key, `id_cliente` bigint unsigned not null, `fecha_agregado` date not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `carrito` add constraint `carrito_id_cliente_foreign` foreign key (`id_cliente`) references `cliente` (`id_cliente`) on delete cascade;
alter table `carrito` add unique `carrito_id_cliente_unique`(`id_cliente`);

-- Migración: 11_detalle_carrito.php
create table `detalle_carrito` (`id_detalle_carrito` bigint unsigned not null auto_increment primary key, `id_carrito` bigint unsigned not null, `id_producto` bigint unsigned not null, `cantidad` int unsigned not null, `precio_unitario` decimal(12, 2) not null, `subtotal` decimal(12, 2) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `detalle_carrito` add unique `detalle_carrito_id_carrito_id_producto_unique`(`id_carrito`, `id_producto`);
alter table `detalle_carrito` add constraint `detalle_carrito_id_carrito_foreign` foreign key (`id_carrito`) references `carrito` (`id_carrito`) on delete cascade;
alter table `detalle_carrito` add constraint `detalle_carrito_id_producto_foreign` foreign key (`id_producto`) references `producto` (`id_producto`) on delete cascade;

-- Migración: 12_inventario.php
create table `inventario` (`id_inventario` bigint unsigned not null auto_increment primary key, `id_producto` bigint unsigned not null, `cantidad_disponible` int unsigned not null default '0', `cantidad_minima` int unsigned not null default '3', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `inventario` add constraint `inventario_id_producto_foreign` foreign key (`id_producto`) references `producto` (`id_producto`) on delete cascade;
alter table `inventario` add unique `inventario_id_producto_unique`(`id_producto`);

-- Migración: 13_pedido.php
create table `pedido` (`id_pedido` bigint unsigned not null auto_increment primary key, `id_cliente` bigint unsigned not null, `fecha` datetime not null, `estado` enum('pendiente', 'programado', 'en_curso', 'entregado', 'cancelado') not null default 'pendiente', `total` decimal(12, 2) not null, `direccion_envio` varchar(150) null, `ciudad_envio` varchar(60) null, `notas` varchar(255) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `pedido` add index `pedido_fecha_index`(`fecha`);
alter table `pedido` add constraint `pedido_id_cliente_foreign` foreign key (`id_cliente`) references `cliente` (`id_cliente`) on delete cascade;

-- Migración: 14_detalle_pedido.php
create table `detalle_pedido` (`id_detalle_pedido` bigint unsigned not null auto_increment primary key, `id_pedido` bigint unsigned not null, `id_producto` bigint unsigned not null, `cantidad` int unsigned not null, `precio_unitario` decimal(12, 2) not null, `sub_total` decimal(12, 2) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `detalle_pedido` add constraint `detalle_pedido_id_pedido_foreign` foreign key (`id_pedido`) references `pedido` (`id_pedido`) on delete cascade;
alter table `detalle_pedido` add constraint `detalle_pedido_id_producto_foreign` foreign key (`id_producto`) references `producto` (`id_producto`) on delete restrict;

-- Migración: 15_factura.php
create table `factura` (`id_factura` bigint unsigned not null auto_increment primary key, `id_pedido` bigint unsigned not null, `numero_factura` varchar(20) not null, `fecha_factura` datetime not null, `subtotal` decimal(12, 2) not null, `impuesto` decimal(12, 2) not null, `total_factura` decimal(12, 2) not null, `estado_factura` varchar(20) not null, `pago` decimal(12, 2) not null, `metodo_pago` varchar(50) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `factura` add constraint `factura_id_pedido_foreign` foreign key (`id_pedido`) references `pedido` (`id_pedido`) on delete cascade;
alter table `factura` add unique `factura_id_pedido_unique`(`id_pedido`);
alter table `factura` add unique `factura_numero_factura_unique`(`numero_factura`);

-- Registro de migraciones ejecutadas
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('00_00_laravel_base', 1),
('01_rol', 1),
('02_usuario', 1),
('03_reporte', 1),
('04_cliente', 1),
('05_pqr', 1),
('06_categoria', 1),
('07_marca', 1),
('08_proveedor', 1),
('09_producto', 1),
('10_carrito', 1),
('11_detalle_carrito', 1),
('12_inventario', 1),
('13_pedido', 1),
('14_detalle_pedido', 1),
('15_factura', 1);

-- =====================================================================
--  DATOS DE PRUEBA
--  5 usuarios (contraseña de todos: Raiders2026*), catálogo, pedidos,
--  facturas y PQR. Las contraseñas están cifradas con bcrypt.
-- =====================================================================

-- Tabla `rol` (3 registros)
INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'Acceso total: catálogo, inventario, pedidos, PQR, usuarios y reportes gerenciales.', '2026-10-27 19:51:18', '2026-10-27 19:51:18'),
(2, 'Vendedor', 'Gestiona catálogo, inventario, pedidos, clientes y PQR. No accede a reportes ni usuarios.', '2026-10-27 19:51:18', '2026-10-27 19:51:18'),
(3, 'Cliente', 'Compra en la tienda, consulta sus pedidos y facturas y radica PQR.', '2026-10-27 19:51:18', '2026-10-27 19:51:18');

-- Tabla `usuario` (5 registros)
INSERT INTO `usuario` (`id_usuario`, `id_rol`, `nombre_usuario`, `apellido_usuario`, `email`, `password`, `telefono`, `activo`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Andrés', 'Rojas', 'admin@raidersparadise.co', '$2y$12$kp/ww.qzGdZ2s8fNderu/OI83ed9NDFoRH08XvII5AePmfz4FVcxm', '3001112233', 1, NULL, '2026-10-27 19:51:18', '2026-10-27 19:51:18'),
(2, 2, 'Camila', 'Herrera', 'ventas@raidersparadise.co', '$2y$12$Fk/hn7xk91RGg5LTYSSCk.YVJ.1Ys1E6aC8bSiYHu7OqBMV8vWYqS', '3002223344', 1, NULL, '2026-10-27 19:51:19', '2026-10-27 19:51:19'),
(3, 3, 'Juan', 'Pérez', 'juan.perez@correo.co', '$2y$12$R1UW66Sc/mGhO3DRDltbA.7UHL/jDFr0sV6eGCre/ctzG8C9LdEeW', '3105556677', 1, NULL, '2026-10-27 19:51:19', '2026-10-27 19:51:19'),
(4, 3, 'María', 'Torres', 'maria.torres@correo.co', '$2y$12$IWW3Opjv/mRHDUD9dMZIZutdQ2Ftd3UkO6F1F.vraG88xgrBBFaXe', '3157778899', 1, NULL, '2026-10-27 19:51:19', '2026-10-27 19:51:19'),
(5, 3, 'Santiago', 'López', 'santiago.lopez@correo.co', '$2y$12$ZMHx.UUF3gOSe5E5zcbtFeK.NrV7/cy3XEUcrIazdMh.gspfGCfay', '3204445566', 1, NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `reporte`: sin registros

-- Tabla `cliente` (8 registros)
INSERT INTO `cliente` (`id_cliente`, `id_usuario`, `documento`, `nombre_cliente`, `apellido_cliente`, `email`, `telefono`, `direccion`, `ciudad`, `created_at`, `updated_at`) VALUES
(1, 3, '1019876543', 'Juan', 'Pérez', 'juan.perez@correo.co', '3105556677', 'Cra. 15 # 93-60, Apto 402', 'Bogotá', '2026-10-27 19:51:19', '2026-10-27 19:51:19'),
(2, 4, '52345678', 'María', 'Torres', 'maria.torres@correo.co', '3157778899', 'Calle 10 # 43-21', 'Medellín', '2026-10-27 19:51:19', '2026-10-27 19:51:19'),
(3, 5, '80123456', 'Santiago', 'López', 'santiago.lopez@correo.co', '3204445566', 'Av. 6N # 23-45', 'Cali', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, NULL, NULL, 'Laura', 'Gómez', 'laura.gomez@correo.co', '3228273336', 'Calle 121 # 64-78', 'Bogotá', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, NULL, NULL, 'Felipe', 'Castro', 'felipe.castro@correo.co', '3136691060', 'Calle 112 # 63-67', 'Bucaramanga', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, NULL, NULL, 'Valentina', 'Ruiz', 'valentina.ruiz@correo.co', '3170856133', 'Calle 16 # 89-47', 'Barranquilla', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(7, NULL, NULL, 'Daniel', 'Moreno', 'daniel.moreno@correo.co', '3147170998', 'Calle 46 # 29-22', 'Bogotá', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(8, NULL, NULL, 'Natalia', 'Vargas', 'natalia.vargas@correo.co', '3170464249', 'Calle 64 # 18-88', 'Pereira', '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `pqr` (7 registros)
INSERT INTO `pqr` (`id_pqr`, `id_usuario`, `id_cliente`, `tipo`, `asunto`, `descripcion_pqr`, `estado`, `respuesta`, `fecha`, `fecha_cierre`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'peticion', '¿Tienen el AGV K6 S en talla XS?', 'Quisiera saber si pueden conseguir el casco AGV K6 S en talla XS y cuánto tardaría.', 'cerrada', 'Hola, sí lo podemos pedir. Llega en 8 días hábiles. Ya te enviamos la cotización al correo.', '2026-10-07 00:00:00', '2026-10-08 19:51:20', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, 2, 2, 'queja', 'Demora en la entrega', 'Mi pedido tardó 6 días en llegar a Medellín y me habían dicho 3.', 'cerrada', 'Lamentamos la demora: la transportadora tuvo novedades en ruta. Te enviamos un bono de envío gratis.', '2026-09-22 00:00:00', '2026-09-27 19:51:20', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, 2, 3, 'reclamo', 'Guante con costura suelta', 'El guante derecho llegó con una costura suelta en el dedo índice. Solicito cambio.', 'en proceso', NULL, '2026-10-23 00:00:00', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, 2, 4, 'sugerencia', 'Más tallas de botas', 'Sería genial que manejaran tallas 46 y 47 en botas de pista.', 'asignada', NULL, '2026-10-25 00:00:00', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, NULL, 5, 'peticion', 'Factura a nombre de empresa', 'Necesito que la factura de mi último pedido salga a nombre de mi empresa con NIT.', 'recibida', NULL, '2026-10-26 00:00:00', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, 2, 6, 'reclamo', 'Cobro duplicado PSE', 'Me debitaron dos veces el valor del pedido por PSE.', 'cerrada', 'Verificamos con el banco y el segundo cobro fue reversado. Se refleja en 3 días hábiles.', '2026-09-07 00:00:00', '2026-09-12 19:51:20', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(7, 2, 7, 'queja', 'No responden el WhatsApp', 'Escribí al WhatsApp y nadie respondió en todo el día.', 'rechazada', 'Nuestro canal oficial de atención es este formulario de PQR y el correo. El WhatsApp es solo para ventas.', '2026-10-12 00:00:00', '2026-10-17 19:51:20', '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `categoria` (6 registros)
INSERT INTO `categoria` (`id_categoria`, `nombre_categoria`, `descripcion_categoria`, `created_at`, `updated_at`) VALUES
(1, 'Cascos', 'Cascos integrales, modulares y deportivos con certificación ECE 22.06 / DOT.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, 'Guantes', 'Guantes de pista, turismo y urbanos con protecciones y materiales técnicos.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, 'Chaquetas', 'Chaquetas de cuero y textiles con protecciones CE nivel 2.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, 'Impermeables', 'Trajes y prendas impermeables para rodar bajo la lluvia.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, 'Botas', 'Botas de pista, touring y urbanas con refuerzos de tobillo.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, 'Accesorios', 'Intercomunicadores, baúles, visores y complementos premium.', '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `marca` (11 registros)
INSERT INTO `marca` (`id_marca`, `nombre_marca`, `descripcion_marca`, `created_at`, `updated_at`) VALUES
(1, 'Shoei', 'Cascos japoneses de gama alta.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, 'AGV', 'Cascos italianos de competición.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, 'Arai', 'Cascos artesanales japoneses.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, 'LS2', 'Cascos con excelente relación calidad-precio.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, 'Shark', 'Cascos franceses de diseño deportivo.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, 'Alpinestars', 'Indumentaria técnica de competición.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(7, 'Dainese', 'Protección italiana para motociclistas.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(8, 'Rev''It', 'Equipamiento holandés touring y urbano.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(9, 'Givi', 'Baúles, maletas y accesorios de viaje.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(10, 'Sena', 'Intercomunicadores Bluetooth.', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(11, 'Pinlock', 'Sistemas antiempañante para visores.', '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `proveedor` (3 registros)
INSERT INTO `proveedor` (`id_proveedor`, `nit`, `nombre_proveedor`, `telefono_proveedor`, `direccion_proveedor`, `email_proveedor`, `created_at`, `updated_at`) VALUES
(1, '900123456-1', 'MotoImport Colombia S.A.S.', '6017451200', 'Av. Boyacá # 64-20, Bogotá', 'compras@motoimport.co', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, '901987654-3', 'Racing Gear Andina S.A.S.', '6044448899', 'Cra. 48 # 10-45, Medellín', 'ventas@racinggear.co', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, '900555777-2', 'Distribuciones Ruta Premium Ltda.', '6023337766', 'Calle 5 # 38-12, Cali', 'pedidos@rutapremium.co', '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `producto` (24 registros)
INSERT INTO `producto` (`id_producto`, `id_categoria`, `id_marca`, `id_proveedor`, `sku`, `nombre_producto`, `descripcion_producto`, `precio_producto`, `estado_producto`, `destacado`, `imagen_producto`, `comentario_producto`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'CAS-SHO-001', 'Shoei GT-Air 3 Negro Mate', 'Casco integral touring con visera solar interna, sistema Pinlock y preinstalación para intercomunicador. Certificación ECE 22.06.', 3890000, 'activo', 1, 'images/productos/casco-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, 1, 2, 2, 'CAS-AGV-002', 'AGV K6 S Carbon', 'Casco deportivo ultraliviano (1.255 g) en fibra de carbono y aramida. Ideal para pista y carretera.', 3250000, 'activo', 1, 'images/productos/casco-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, 1, 3, 3, 'CAS-ARA-003', 'Arai Quantic Frost', 'Fabricado a mano en Japón. Calota redonda R75 que desvía impactos y máxima ventilación.', 4600000, 'activo', 1, 'images/productos/casco-naranja.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, 1, 4, 1, 'CAS-LS2-004', 'LS2 Valiant II Modular', 'Modular con mentonera abatible 180°, doble homologación P/J y visor solar.', 1290000, 'activo', 0, 'images/productos/casco-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, 1, 5, 2, 'CAS-SHA-005', 'Shark Spartan RS Carbon', 'Integral deportivo con calota de carbono, visor antirrayas y forro desmontable lavable.', 2150000, 'activo', 0, 'images/productos/casco-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, 2, 6, 3, 'GUA-ALP-006', 'Alpinestars GP Pro R4', 'Guante de competición en cuero de canguro con protección de nudillos en carbono.', 890000, 'activo', 1, 'images/productos/guantes-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(7, 2, 7, 1, 'GUA-DAI-007', 'Dainese Carbon 4 Long', 'Guante largo de cuero de cabra con insertos de carbono y refuerzos en palma.', 780000, 'activo', 0, 'images/productos/guantes-naranja.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(8, 2, 8, 2, 'GUA-REV-008', 'Rev''It Sand 4 H2O', 'Guante touring impermeable con membrana Hydratex y compatibilidad táctil.', 420000, 'activo', 0, 'images/productos/guantes-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(9, 2, 6, 3, 'GUA-ALP-009', 'Alpinestars SMX-1 Air v2', 'Guante urbano ventilado en malla y cuero sintético, ideal para clima cálido.', 260000, 'activo', 0, 'images/productos/guantes-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(10, 3, 7, 1, 'CHA-DAI-010', 'Dainese Super Speed 4 Cuero', 'Chaqueta de cuero bovino con protecciones Pro-Shape 2.0 en hombros y codos.', 3900000, 'activo', 1, 'images/productos/chaqueta-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(11, 3, 6, 2, 'CHA-ALP-011', 'Alpinestars T-GP Plus R v4 Air', 'Chaqueta textil ventilada con protecciones CE nivel 1 y bolsillo para espaldar.', 1150000, 'activo', 0, 'images/productos/chaqueta-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(12, 3, 8, 3, 'CHA-REV-012', 'Rev''It Tornado 4 H2O', 'Chaqueta adventure 3 en 1 con membrana impermeable y forro térmico desmontable.', 1980000, 'activo', 1, 'images/productos/chaqueta-naranja.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(13, 3, 7, 1, 'CHA-DAI-013', 'Dainese Avro 5 Textil', 'Chaqueta deportiva textil con elásticos S1 y preparación para airbag.', 2450000, 'activo', 0, 'images/productos/chaqueta-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(14, 4, 9, 2, 'IMP-GIV-014', 'Givi Traje Impermeable CRS01', 'Conjunto chaqueta y pantalón 100% impermeable con cintas reflectivas.', 320000, 'activo', 0, 'images/productos/impermeable-naranja.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(15, 4, 6, 3, 'IMP-ALP-015', 'Alpinestars Hurricane Rain', 'Chaqueta impermeable compacta para llevar sobre la chaqueta de protección.', 450000, 'activo', 1, 'images/productos/impermeable-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(16, 4, 8, 1, 'IMP-REV-016', 'Rev''It Acid 4 H2O Pantalón', 'Pantalón impermeable con cremalleras laterales completas para ponerlo con botas.', 520000, 'activo', 0, 'images/productos/impermeable-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(17, 4, 7, 2, 'IMP-DAI-017', 'Dainese Storm 2 Rain Suit', 'Traje enterizo impermeable con bolsa de transporte y paneles de alta visibilidad.', 690000, 'activo', 0, 'images/productos/impermeable-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(18, 5, 6, 3, 'BOT-ALP-018', 'Alpinestars SMX-6 v3', 'Bota deportiva con sistema de soporte de tobillo y puntera reemplazable.', 1250000, 'activo', 1, 'images/productos/botas-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(19, 5, 7, 1, 'BOT-DAI-019', 'Dainese Torque 3 Out', 'Bota de pista con sistema D-Axial y deslizadores de magnesio.', 1690000, 'activo', 0, 'images/productos/botas-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(20, 5, 8, 2, 'BOT-REV-020', 'Rev''It Discovery H2O', 'Bota adventure impermeable con suela Vibram y cierres de aluminio.', 1150000, 'activo', 0, 'images/productos/botas-naranja.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(21, 6, 10, 3, 'ACC-SEN-021', 'Sena 50S Intercomunicador', 'Intercomunicador Mesh 2.0 + Bluetooth 5, sonido Harman Kardon y control por voz.', 1450000, 'activo', 1, 'images/productos/intercom-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(22, 6, 9, 1, 'ACC-GIV-022', 'Givi Trekker Outback 42L', 'Baúl de aluminio de 42 litros, resistente al agua, con sistema Monokey.', 1890000, 'activo', 0, 'images/productos/baul-negro.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(23, 6, 11, 2, 'ACC-PIN-023', 'Pinlock 120 Max Vision', 'Lente antiempañante para visor. Compatible con cascos con pines Pinlock.', 190000, 'activo', 0, 'images/productos/visor-indigo.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(24, 6, 1, 3, 'ACC-SHO-024', 'Shoei Visor CWR-F2 Iridium', 'Visor espejado Iridium con tratamiento antirrayas para cascos Shoei.', 480000, 'activo', 0, 'images/productos/visor-naranja.svg', NULL, '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `carrito`: sin registros

-- Tabla `detalle_carrito`: sin registros

-- Tabla `inventario` (24 registros)
INSERT INTO `inventario` (`id_inventario`, `id_producto`, `cantidad_disponible`, `cantidad_minima`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, 2, 4, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, 3, 2, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, 4, 12, 3, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, 5, 5, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, 6, 10, 3, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(7, 7, 7, 3, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(8, 8, 0, 3, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(9, 9, 15, 4, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(10, 10, 3, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(11, 11, 8, 3, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(12, 12, 5, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(13, 13, 2, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(14, 14, 20, 5, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(15, 15, 9, 4, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(16, 16, 4, 4, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(17, 17, 6, 3, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(18, 18, 6, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(19, 19, 1, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(20, 20, 4, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(21, 21, 8, 3, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(22, 22, 3, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(23, 23, 25, 6, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(24, 24, 0, 2, '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `pedido` (85 registros)
INSERT INTO `pedido` (`id_pedido`, `id_cliente`, `fecha`, `estado`, `total`, `direccion_envio`, `ciudad_envio`, `notas`, `created_at`, `updated_at`) VALUES
(1, 8, '2026-10-07 18:39:00', 'entregado', 2900000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-10-07 18:39:00', '2026-10-27 19:51:20'),
(2, 1, '2026-10-22 12:43:00', 'pendiente', 10380000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-10-22 12:43:00', '2026-10-27 19:51:20'),
(3, 2, '2026-07-13 21:24:00', 'entregado', 6890000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-07-13 21:24:00', '2026-10-27 19:51:20'),
(4, 4, '2026-10-18 18:27:00', 'entregado', 3230000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-10-18 18:27:00', '2026-10-27 19:51:20'),
(5, 4, '2026-09-21 13:14:00', 'entregado', 4810000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-09-21 13:14:00', '2026-10-27 19:51:20'),
(6, 6, '2026-09-12 14:29:00', 'entregado', 3490000, 'Calle 16 # 89-47', 'Barranquilla', NULL, '2026-09-12 14:29:00', '2026-10-27 19:51:20'),
(7, 7, '2026-07-11 20:51:00', 'entregado', 1040000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-07-11 20:51:00', '2026-10-27 19:51:20'),
(8, 1, '2026-07-04 09:28:00', 'entregado', 3960000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-07-04 09:28:00', '2026-10-27 19:51:20'),
(9, 2, '2026-09-12 08:35:00', 'entregado', 5600000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-09-12 08:35:00', '2026-10-27 19:51:20'),
(10, 4, '2026-09-25 12:36:00', 'entregado', 6300000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-09-25 12:36:00', '2026-10-27 19:51:20'),
(11, 2, '2026-06-30 13:16:00', 'entregado', 5340000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-06-30 13:16:00', '2026-10-27 19:51:20'),
(12, 3, '2026-09-06 09:45:00', 'entregado', 3900000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-09-06 09:45:00', '2026-10-27 19:51:20'),
(13, 4, '2026-10-22 19:04:00', 'programado', 9210000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-10-22 19:04:00', '2026-10-27 19:51:20'),
(14, 1, '2026-09-30 16:54:00', 'entregado', 1730000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-09-30 16:54:00', '2026-10-27 19:51:20'),
(15, 7, '2026-07-29 18:33:00', 'entregado', 4090000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-07-29 18:33:00', '2026-10-27 19:51:20'),
(16, 3, '2026-08-08 11:24:00', 'entregado', 480000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-08-08 11:24:00', '2026-10-27 19:51:20'),
(17, 3, '2026-07-21 21:11:00', 'cancelado', 5600000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-07-21 21:11:00', '2026-10-27 19:51:20'),
(18, 8, '2026-08-26 08:03:00', 'entregado', 3250000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-08-26 08:03:00', '2026-10-27 19:51:20'),
(19, 3, '2026-08-16 20:35:00', 'entregado', 260000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-08-16 20:35:00', '2026-10-27 19:51:20'),
(20, 2, '2026-07-15 08:07:00', 'entregado', 3370000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-07-15 08:07:00', '2026-10-27 19:51:20'),
(21, 6, '2026-09-10 12:07:00', 'entregado', 1450000, 'Calle 16 # 89-47', 'Barranquilla', NULL, '2026-09-10 12:07:00', '2026-10-27 19:51:20'),
(22, 7, '2026-10-22 09:25:00', 'programado', 1890000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-10-22 09:25:00', '2026-10-27 19:51:20'),
(23, 5, '2026-10-22 12:44:00', 'pendiente', 190000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-10-22 12:44:00', '2026-10-27 19:51:20'),
(24, 1, '2026-07-13 15:21:00', 'entregado', 3290000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-07-13 15:21:00', '2026-10-27 19:51:20'),
(25, 2, '2026-09-16 16:46:00', 'entregado', 3110000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-09-16 16:46:00', '2026-10-27 19:51:20'),
(26, 4, '2026-09-03 13:47:00', 'entregado', 6140000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-09-03 13:47:00', '2026-10-27 19:51:20'),
(27, 7, '2026-08-09 09:55:00', 'entregado', 1150000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-08-09 09:55:00', '2026-10-27 19:51:20'),
(28, 4, '2026-09-13 15:16:00', 'entregado', 3970000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-09-13 15:16:00', '2026-10-27 19:51:20'),
(29, 5, '2026-10-18 17:36:00', 'entregado', 710000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-10-18 17:36:00', '2026-10-27 19:51:20'),
(30, 1, '2026-08-05 16:26:00', 'entregado', 1450000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-08-05 16:26:00', '2026-10-27 19:51:20'),
(31, 3, '2026-09-22 14:06:00', 'entregado', 1150000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-09-22 14:06:00', '2026-10-27 19:51:20'),
(32, 1, '2026-08-29 19:13:00', 'entregado', 3920000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-08-29 19:13:00', '2026-10-27 19:51:20'),
(33, 6, '2026-09-01 17:23:00', 'entregado', 3800000, 'Calle 16 # 89-47', 'Barranquilla', NULL, '2026-09-01 17:23:00', '2026-10-27 19:51:20'),
(34, 8, '2026-08-03 12:09:00', 'entregado', 3380000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-08-03 12:09:00', '2026-10-27 19:51:20'),
(35, 7, '2026-08-17 17:41:00', 'entregado', 7170000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-08-17 17:41:00', '2026-10-27 19:51:20'),
(36, 8, '2026-08-12 12:45:00', 'cancelado', 260000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-08-12 12:45:00', '2026-10-27 19:51:20'),
(37, 3, '2026-09-14 17:21:00', 'entregado', 690000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-09-14 17:21:00', '2026-10-27 19:51:20'),
(38, 6, '2026-10-10 20:05:00', 'entregado', 6270000, 'Calle 16 # 89-47', 'Barranquilla', NULL, '2026-10-10 20:05:00', '2026-10-27 19:51:20'),
(39, 1, '2026-06-30 10:50:00', 'entregado', 3140000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-06-30 10:50:00', '2026-10-27 19:51:20'),
(40, 5, '2026-08-23 08:18:00', 'entregado', 2030000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-08-23 08:18:00', '2026-10-27 19:51:20'),
(41, 7, '2026-08-06 16:16:00', 'entregado', 1150000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-08-06 16:16:00', '2026-10-27 19:51:20'),
(42, 6, '2026-10-08 16:37:00', 'entregado', 1870000, 'Calle 16 # 89-47', 'Barranquilla', NULL, '2026-10-08 16:37:00', '2026-10-27 19:51:20'),
(43, 4, '2026-10-25 14:52:00', 'programado', 2350000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-10-25 14:52:00', '2026-10-27 19:51:20'),
(44, 7, '2026-10-03 13:14:00', 'entregado', 4160000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-10-03 13:14:00', '2026-10-27 19:51:20'),
(45, 2, '2026-07-31 13:26:00', 'entregado', 5110000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-07-31 13:26:00', '2026-10-27 19:51:20'),
(46, 8, '2026-08-22 20:37:00', 'cancelado', 14570000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-08-22 20:37:00', '2026-10-27 19:51:20'),
(47, 6, '2026-09-01 08:03:00', 'entregado', 4510000, 'Calle 16 # 89-47', 'Barranquilla', NULL, '2026-09-01 08:03:00', '2026-10-27 19:51:20'),
(48, 8, '2026-09-02 16:51:00', 'entregado', 5340000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-09-02 16:51:00', '2026-10-27 19:51:20'),
(49, 5, '2026-09-28 14:19:00', 'cancelado', 1150000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-09-28 14:19:00', '2026-10-27 19:51:20'),
(50, 1, '2026-08-12 15:24:00', 'entregado', 2710000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-08-12 15:24:00', '2026-10-27 19:51:20');
INSERT INTO `pedido` (`id_pedido`, `id_cliente`, `fecha`, `estado`, `total`, `direccion_envio`, `ciudad_envio`, `notas`, `created_at`, `updated_at`) VALUES
(51, 4, '2026-10-22 10:48:00', 'en_curso', 480000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-10-22 10:48:00', '2026-10-27 19:51:20'),
(52, 1, '2026-09-03 19:21:00', 'entregado', 6600000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-09-03 19:21:00', '2026-10-27 19:51:20'),
(53, 8, '2026-08-02 11:59:00', 'entregado', 5710000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-08-02 11:59:00', '2026-10-27 19:51:20'),
(54, 3, '2026-09-11 17:35:00', 'entregado', 1450000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-09-11 17:35:00', '2026-10-27 19:51:20'),
(55, 8, '2026-09-22 15:20:00', 'entregado', 6450000, 'Calle 64 # 18-88', 'Pereira', NULL, '2026-09-22 15:20:00', '2026-10-27 19:51:20'),
(56, 7, '2026-08-07 16:38:00', 'entregado', 4920000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-08-07 16:38:00', '2026-10-27 19:51:20'),
(57, 7, '2026-08-25 08:55:00', 'entregado', 3640000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-08-25 08:55:00', '2026-10-27 19:51:20'),
(58, 7, '2026-07-27 11:32:00', 'entregado', 3030000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-07-27 11:32:00', '2026-10-27 19:51:20'),
(59, 7, '2026-09-12 14:44:00', 'cancelado', 5580000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-09-12 14:44:00', '2026-10-27 19:51:20'),
(60, 5, '2026-07-23 15:20:00', 'entregado', 4900000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-07-23 15:20:00', '2026-10-27 19:51:20'),
(61, 3, '2026-09-06 11:38:00', 'entregado', 3440000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-09-06 11:38:00', '2026-10-27 19:51:20'),
(62, 2, '2026-07-24 09:23:00', 'entregado', 5850000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-07-24 09:23:00', '2026-10-27 19:51:20'),
(63, 4, '2026-08-15 21:40:00', 'entregado', 3380000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-08-15 21:40:00', '2026-10-27 19:51:20'),
(64, 1, '2026-07-29 13:13:00', 'entregado', 7140000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-07-29 13:13:00', '2026-10-27 19:51:20'),
(65, 2, '2026-08-01 09:02:00', 'entregado', 5090000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-08-01 09:02:00', '2026-10-27 19:51:20'),
(66, 2, '2026-09-09 20:15:00', 'entregado', 780000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-09-09 20:15:00', '2026-10-27 19:51:20'),
(67, 4, '2026-10-09 13:18:00', 'entregado', 480000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-10-09 13:18:00', '2026-10-27 19:51:20'),
(68, 1, '2026-09-06 14:54:00', 'entregado', 2270000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-09-06 14:54:00', '2026-10-27 19:51:20'),
(69, 7, '2026-09-17 12:47:00', 'entregado', 1840000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-09-17 12:47:00', '2026-10-27 19:51:20'),
(70, 4, '2026-08-22 17:36:00', 'entregado', 3900000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-08-22 17:36:00', '2026-10-27 19:51:20'),
(71, 5, '2026-08-03 15:32:00', 'entregado', 690000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-08-03 15:32:00', '2026-10-27 19:51:20'),
(72, 5, '2026-08-11 13:05:00', 'entregado', 4400000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-08-11 13:05:00', '2026-10-27 19:51:20'),
(73, 2, '2026-08-15 09:42:00', 'cancelado', 2750000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-08-15 09:42:00', '2026-10-27 19:51:20'),
(74, 7, '2026-07-28 16:20:00', 'entregado', 1980000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-07-28 16:20:00', '2026-10-27 19:51:20'),
(75, 7, '2026-07-08 08:12:00', 'entregado', 10350000, 'Calle 46 # 29-22', 'Bogotá', NULL, '2026-07-08 08:12:00', '2026-10-27 19:51:20'),
(76, 4, '2026-07-29 13:46:00', 'entregado', 690000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-07-29 13:46:00', '2026-10-27 19:51:20'),
(77, 2, '2026-08-27 10:30:00', 'entregado', 6500000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-08-27 10:30:00', '2026-10-27 19:51:20'),
(78, 4, '2026-07-27 10:15:00', 'entregado', 1150000, 'Calle 121 # 64-78', 'Bogotá', NULL, '2026-07-27 10:15:00', '2026-10-27 19:51:20'),
(79, 6, '2026-09-04 13:17:00', 'entregado', 1890000, 'Calle 16 # 89-47', 'Barranquilla', NULL, '2026-09-04 13:17:00', '2026-10-27 19:51:20'),
(80, 5, '2026-09-01 21:41:00', 'entregado', 4790000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-09-01 21:41:00', '2026-10-27 19:51:20'),
(81, 3, '2026-08-07 20:16:00', 'entregado', 3820000, 'Av. 6N # 23-45', 'Cali', NULL, '2026-08-07 20:16:00', '2026-10-27 19:51:20'),
(82, 2, '2026-10-10 12:36:00', 'cancelado', 890000, 'Calle 10 # 43-21', 'Medellín', NULL, '2026-10-10 12:36:00', '2026-10-27 19:51:20'),
(83, 1, '2026-10-04 14:56:00', 'entregado', 4730000, 'Cra. 15 # 93-60, Apto 402', 'Bogotá', NULL, '2026-10-04 14:56:00', '2026-10-27 19:51:20'),
(84, 5, '2026-09-03 21:29:00', 'entregado', 10760000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-09-03 21:29:00', '2026-10-27 19:51:20'),
(85, 5, '2026-07-07 10:33:00', 'entregado', 13050000, 'Calle 112 # 63-67', 'Bucaramanga', NULL, '2026-07-07 10:33:00', '2026-10-27 19:51:20');

-- Tabla `detalle_pedido` (172 registros)
INSERT INTO `detalle_pedido` (`id_detalle_pedido`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `sub_total`, `created_at`, `updated_at`) VALUES
(1, 1, 21, 2, 1450000, 2900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, 2, 1, 2, 3890000, 7780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, 2, 5, 1, 2150000, 2150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, 2, 15, 1, 450000, 450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, 3, 6, 2, 890000, 1780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, 3, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(7, 3, 12, 2, 1980000, 3960000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(8, 4, 7, 1, 780000, 780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(9, 4, 13, 1, 2450000, 2450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(10, 5, 2, 1, 3250000, 3250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(11, 5, 7, 2, 780000, 1560000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(12, 6, 5, 1, 2150000, 2150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(13, 6, 20, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(14, 6, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(15, 7, 16, 2, 520000, 1040000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(16, 8, 12, 2, 1980000, 3960000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(17, 9, 1, 1, 3890000, 3890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(18, 9, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(19, 9, 21, 1, 1450000, 1450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(20, 10, 3, 1, 4600000, 4600000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(21, 10, 15, 1, 450000, 450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(22, 10, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(23, 11, 4, 1, 1290000, 1290000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(24, 11, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(25, 11, 21, 2, 1450000, 2900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(26, 12, 10, 1, 3900000, 3900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(27, 13, 6, 1, 890000, 890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(28, 13, 10, 2, 3900000, 7800000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(29, 13, 16, 1, 520000, 520000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(30, 14, 6, 1, 890000, 890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(31, 14, 14, 1, 320000, 320000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(32, 14, 16, 1, 520000, 520000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(33, 15, 16, 1, 520000, 520000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(34, 15, 19, 2, 1690000, 3380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(35, 15, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(36, 16, 24, 1, 480000, 480000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(37, 17, 1, 1, 3890000, 3890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(38, 17, 4, 1, 1290000, 1290000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(39, 17, 8, 1, 420000, 420000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(40, 18, 2, 1, 3250000, 3250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(41, 19, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(42, 20, 4, 1, 1290000, 1290000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(43, 20, 22, 1, 1890000, 1890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(44, 20, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(45, 21, 21, 1, 1450000, 1450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(46, 22, 22, 1, 1890000, 1890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(47, 23, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(48, 24, 6, 1, 890000, 890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(49, 24, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(50, 24, 20, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20');
INSERT INTO `detalle_pedido` (`id_detalle_pedido`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `sub_total`, `created_at`, `updated_at`) VALUES
(51, 25, 4, 1, 1290000, 1290000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(52, 25, 7, 1, 780000, 780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(53, 25, 16, 2, 520000, 1040000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(54, 26, 5, 2, 2150000, 4300000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(55, 26, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(56, 26, 17, 1, 690000, 690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(57, 27, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(58, 28, 22, 2, 1890000, 3780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(59, 28, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(60, 29, 16, 1, 520000, 520000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(61, 29, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(62, 30, 21, 1, 1450000, 1450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(63, 31, 20, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(64, 32, 5, 1, 2150000, 2150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(65, 32, 9, 2, 260000, 520000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(66, 32, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(67, 33, 4, 2, 1290000, 2580000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(68, 33, 8, 2, 420000, 840000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(69, 33, 23, 2, 190000, 380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(70, 34, 19, 2, 1690000, 3380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(71, 35, 5, 2, 2150000, 4300000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(72, 35, 8, 1, 420000, 420000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(73, 35, 13, 1, 2450000, 2450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(74, 36, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(75, 37, 17, 1, 690000, 690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(76, 38, 10, 1, 3900000, 3900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(77, 38, 22, 1, 1890000, 1890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(78, 38, 24, 1, 480000, 480000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(79, 39, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(80, 39, 22, 1, 1890000, 1890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(81, 40, 7, 1, 780000, 780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(82, 40, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(83, 41, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(84, 42, 4, 1, 1290000, 1290000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(85, 42, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(86, 42, 14, 1, 320000, 320000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(87, 43, 8, 1, 420000, 420000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(88, 43, 21, 1, 1450000, 1450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(89, 43, 24, 1, 480000, 480000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(90, 44, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(91, 44, 10, 1, 3900000, 3900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(92, 45, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(93, 45, 19, 2, 1690000, 3380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(94, 45, 24, 1, 480000, 480000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(95, 46, 1, 2, 3890000, 7780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(96, 46, 13, 2, 2450000, 4900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(97, 46, 22, 1, 1890000, 1890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(98, 47, 4, 2, 1290000, 2580000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(99, 47, 7, 1, 780000, 780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(100, 47, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20');
INSERT INTO `detalle_pedido` (`id_detalle_pedido`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `sub_total`, `created_at`, `updated_at`) VALUES
(101, 48, 1, 1, 3890000, 3890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(102, 48, 21, 1, 1450000, 1450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(103, 49, 20, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(104, 50, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(105, 50, 13, 1, 2450000, 2450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(106, 51, 24, 1, 480000, 480000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(107, 52, 5, 2, 2150000, 4300000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(108, 52, 11, 2, 1150000, 2300000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(109, 53, 4, 1, 1290000, 1290000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(110, 53, 10, 1, 3900000, 3900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(111, 53, 16, 1, 520000, 520000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(112, 54, 21, 1, 1450000, 1450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(113, 55, 1, 1, 3890000, 3890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(114, 55, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(115, 55, 20, 2, 1150000, 2300000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(116, 56, 3, 1, 4600000, 4600000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(117, 56, 14, 1, 320000, 320000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(118, 57, 9, 1, 260000, 260000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(119, 57, 19, 2, 1690000, 3380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(120, 58, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(121, 58, 19, 1, 1690000, 1690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(122, 58, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(123, 59, 1, 1, 3890000, 3890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(124, 59, 19, 1, 1690000, 1690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(125, 60, 13, 2, 2450000, 4900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(126, 61, 8, 1, 420000, 420000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(127, 61, 16, 1, 520000, 520000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(128, 61, 18, 2, 1250000, 2500000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(129, 62, 3, 1, 4600000, 4600000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(130, 62, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(131, 63, 19, 2, 1690000, 3380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(132, 64, 2, 2, 3250000, 6500000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(133, 64, 15, 1, 450000, 450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(134, 64, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(135, 65, 2, 1, 3250000, 3250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(136, 65, 17, 1, 690000, 690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(137, 65, 20, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(138, 66, 7, 1, 780000, 780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(139, 67, 24, 1, 480000, 480000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(140, 68, 22, 1, 1890000, 1890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(141, 68, 23, 2, 190000, 380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(142, 69, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(143, 69, 17, 1, 690000, 690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(144, 70, 10, 1, 3900000, 3900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(145, 71, 17, 1, 690000, 690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(146, 72, 2, 1, 3250000, 3250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(147, 72, 20, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(148, 73, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(149, 73, 15, 1, 450000, 450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(150, 73, 20, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20');
INSERT INTO `detalle_pedido` (`id_detalle_pedido`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `sub_total`, `created_at`, `updated_at`) VALUES
(151, 74, 12, 1, 1980000, 1980000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(152, 75, 3, 2, 4600000, 9200000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(153, 75, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(154, 76, 17, 1, 690000, 690000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(155, 77, 2, 2, 3250000, 6500000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(156, 78, 11, 1, 1150000, 1150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(157, 79, 15, 1, 450000, 450000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(158, 79, 18, 1, 1250000, 1250000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(159, 79, 23, 1, 190000, 190000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(160, 80, 6, 1, 890000, 890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(161, 80, 10, 1, 3900000, 3900000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(162, 81, 5, 1, 2150000, 2150000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(163, 81, 6, 1, 890000, 890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(164, 81, 7, 1, 780000, 780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(165, 82, 6, 1, 890000, 890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(166, 83, 1, 1, 3890000, 3890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(167, 83, 8, 2, 420000, 840000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(168, 84, 3, 2, 4600000, 9200000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(169, 84, 7, 2, 780000, 1560000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(170, 85, 1, 2, 3890000, 7780000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(171, 85, 19, 2, 1690000, 3380000, '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(172, 85, 22, 1, 1890000, 1890000, '2026-10-27 19:51:20', '2026-10-27 19:51:20');

-- Tabla `factura` (85 registros)
INSERT INTO `factura` (`id_factura`, `id_pedido`, `numero_factura`, `fecha_factura`, `subtotal`, `impuesto`, `total_factura`, `estado_factura`, `pago`, `metodo_pago`, `created_at`, `updated_at`) VALUES
(1, 1, 'FE-000001', '2026-10-07 18:39:00', 2436974.79, 463025.21, 2900000, 'pagada', 2900000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(2, 2, 'FE-000002', '2026-10-22 12:43:00', 8722689.08, 1657310.92, 10380000, 'pendiente', 0, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(3, 3, 'FE-000003', '2026-07-13 21:24:00', 5789915.97, 1100084.03, 6890000, 'pagada', 6890000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(4, 4, 'FE-000004', '2026-10-18 18:27:00', 2714285.71, 515714.29, 3230000, 'pagada', 3230000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(5, 5, 'FE-000005', '2026-09-21 13:14:00', 4042016.81, 767983.19, 4810000, 'pagada', 4810000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(6, 6, 'FE-000006', '2026-09-12 14:29:00', 2932773.11, 557226.89, 3490000, 'pagada', 3490000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(7, 7, 'FE-000007', '2026-07-11 20:51:00', 873949.58, 166050.42, 1040000, 'pagada', 1040000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(8, 8, 'FE-000008', '2026-07-04 09:28:00', 3327731.09, 632268.91, 3960000, 'pagada', 3960000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(9, 9, 'FE-000009', '2026-09-12 08:35:00', 4705882.35, 894117.65, 5600000, 'pagada', 5600000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(10, 10, 'FE-000010', '2026-09-25 12:36:00', 5294117.65, 1005882.35, 6300000, 'pagada', 6300000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(11, 11, 'FE-000011', '2026-06-30 13:16:00', 4487394.96, 852605.04, 5340000, 'pagada', 5340000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(12, 12, 'FE-000012', '2026-09-06 09:45:00', 3277310.92, 622689.08, 3900000, 'pagada', 3900000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(13, 13, 'FE-000013', '2026-10-22 19:04:00', 7739495.8, 1470504.2, 9210000, 'pagada', 9210000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(14, 14, 'FE-000014', '2026-09-30 16:54:00', 1453781.51, 276218.49, 1730000, 'pagada', 1730000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(15, 15, 'FE-000015', '2026-07-29 18:33:00', 3436974.79, 653025.21, 4090000, 'pagada', 4090000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(16, 16, 'FE-000016', '2026-08-08 11:24:00', 403361.34, 76638.66, 480000, 'pagada', 480000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(17, 17, 'FE-000017', '2026-07-21 21:11:00', 4705882.35, 894117.65, 5600000, 'anulada', 5600000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(18, 18, 'FE-000018', '2026-08-26 08:03:00', 2731092.44, 518907.56, 3250000, 'pagada', 3250000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(19, 19, 'FE-000019', '2026-08-16 20:35:00', 218487.39, 41512.61, 260000, 'pagada', 260000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(20, 20, 'FE-000020', '2026-07-15 08:07:00', 2831932.77, 538067.23, 3370000, 'pagada', 3370000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(21, 21, 'FE-000021', '2026-09-10 12:07:00', 1218487.39, 231512.61, 1450000, 'pagada', 1450000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(22, 22, 'FE-000022', '2026-10-22 09:25:00', 1588235.29, 301764.71, 1890000, 'pagada', 1890000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(23, 23, 'FE-000023', '2026-10-22 12:44:00', 159663.87, 30336.13, 190000, 'pagada', 190000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(24, 24, 'FE-000024', '2026-07-13 15:21:00', 2764705.88, 525294.12, 3290000, 'pagada', 3290000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(25, 25, 'FE-000025', '2026-09-16 16:46:00', 2613445.38, 496554.62, 3110000, 'pagada', 3110000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(26, 26, 'FE-000026', '2026-09-03 13:47:00', 5159663.87, 980336.13, 6140000, 'pagada', 6140000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(27, 27, 'FE-000027', '2026-08-09 09:55:00', 966386.55, 183613.45, 1150000, 'pagada', 1150000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(28, 28, 'FE-000028', '2026-09-13 15:16:00', 3336134.45, 633865.55, 3970000, 'pagada', 3970000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(29, 29, 'FE-000029', '2026-10-18 17:36:00', 596638.66, 113361.34, 710000, 'pagada', 710000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(30, 30, 'FE-000030', '2026-08-05 16:26:00', 1218487.39, 231512.61, 1450000, 'pagada', 1450000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(31, 31, 'FE-000031', '2026-09-22 14:06:00', 966386.55, 183613.45, 1150000, 'pagada', 1150000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(32, 32, 'FE-000032', '2026-08-29 19:13:00', 3294117.65, 625882.35, 3920000, 'pagada', 3920000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(33, 33, 'FE-000033', '2026-09-01 17:23:00', 3193277.31, 606722.69, 3800000, 'pagada', 3800000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(34, 34, 'FE-000034', '2026-08-03 12:09:00', 2840336.13, 539663.87, 3380000, 'pagada', 3380000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(35, 35, 'FE-000035', '2026-08-17 17:41:00', 6025210.08, 1144789.92, 7170000, 'pagada', 7170000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(36, 36, 'FE-000036', '2026-08-12 12:45:00', 218487.39, 41512.61, 260000, 'anulada', 260000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(37, 37, 'FE-000037', '2026-09-14 17:21:00', 579831.93, 110168.07, 690000, 'pagada', 690000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(38, 38, 'FE-000038', '2026-10-10 20:05:00', 5268907.56, 1001092.44, 6270000, 'pagada', 6270000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(39, 39, 'FE-000039', '2026-06-30 10:50:00', 2638655.46, 501344.54, 3140000, 'pagada', 3140000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(40, 40, 'FE-000040', '2026-08-23 08:18:00', 1705882.35, 324117.65, 2030000, 'pagada', 2030000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(41, 41, 'FE-000041', '2026-08-06 16:16:00', 966386.55, 183613.45, 1150000, 'pagada', 1150000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(42, 42, 'FE-000042', '2026-10-08 16:37:00', 1571428.57, 298571.43, 1870000, 'pagada', 1870000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(43, 43, 'FE-000043', '2026-10-25 14:52:00', 1974789.92, 375210.08, 2350000, 'pagada', 2350000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(44, 44, 'FE-000044', '2026-10-03 13:14:00', 3495798.32, 664201.68, 4160000, 'pagada', 4160000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(45, 45, 'FE-000045', '2026-07-31 13:26:00', 4294117.65, 815882.35, 5110000, 'pagada', 5110000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(46, 46, 'FE-000046', '2026-08-22 20:37:00', 12243697.48, 2326302.52, 14570000, 'anulada', 14570000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(47, 47, 'FE-000047', '2026-09-01 08:03:00', 3789915.97, 720084.03, 4510000, 'pagada', 4510000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(48, 48, 'FE-000048', '2026-09-02 16:51:00', 4487394.96, 852605.04, 5340000, 'pagada', 5340000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(49, 49, 'FE-000049', '2026-09-28 14:19:00', 966386.55, 183613.45, 1150000, 'anulada', 0, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(50, 50, 'FE-000050', '2026-08-12 15:24:00', 2277310.92, 432689.08, 2710000, 'pagada', 2710000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20');
INSERT INTO `factura` (`id_factura`, `id_pedido`, `numero_factura`, `fecha_factura`, `subtotal`, `impuesto`, `total_factura`, `estado_factura`, `pago`, `metodo_pago`, `created_at`, `updated_at`) VALUES
(51, 51, 'FE-000051', '2026-10-22 10:48:00', 403361.34, 76638.66, 480000, 'pagada', 480000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(52, 52, 'FE-000052', '2026-09-03 19:21:00', 5546218.49, 1053781.51, 6600000, 'pagada', 6600000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(53, 53, 'FE-000053', '2026-08-02 11:59:00', 4798319.33, 911680.67, 5710000, 'pagada', 5710000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(54, 54, 'FE-000054', '2026-09-11 17:35:00', 1218487.39, 231512.61, 1450000, 'pagada', 1450000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(55, 55, 'FE-000055', '2026-09-22 15:20:00', 5420168.07, 1029831.93, 6450000, 'pagada', 6450000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(56, 56, 'FE-000056', '2026-08-07 16:38:00', 4134453.78, 785546.22, 4920000, 'pagada', 4920000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(57, 57, 'FE-000057', '2026-08-25 08:55:00', 3058823.53, 581176.47, 3640000, 'pagada', 3640000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(58, 58, 'FE-000058', '2026-07-27 11:32:00', 2546218.49, 483781.51, 3030000, 'pagada', 3030000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(59, 59, 'FE-000059', '2026-09-12 14:44:00', 4689075.63, 890924.37, 5580000, 'anulada', 5580000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(60, 60, 'FE-000060', '2026-07-23 15:20:00', 4117647.06, 782352.94, 4900000, 'pagada', 4900000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(61, 61, 'FE-000061', '2026-09-06 11:38:00', 2890756.3, 549243.7, 3440000, 'pagada', 3440000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(62, 62, 'FE-000062', '2026-07-24 09:23:00', 4915966.39, 934033.61, 5850000, 'pagada', 5850000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(63, 63, 'FE-000063', '2026-08-15 21:40:00', 2840336.13, 539663.87, 3380000, 'pagada', 3380000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(64, 64, 'FE-000064', '2026-07-29 13:13:00', 6000000, 1140000, 7140000, 'pagada', 7140000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(65, 65, 'FE-000065', '2026-08-01 09:02:00', 4277310.92, 812689.08, 5090000, 'pagada', 5090000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(66, 66, 'FE-000066', '2026-09-09 20:15:00', 655462.18, 124537.82, 780000, 'pagada', 780000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(67, 67, 'FE-000067', '2026-10-09 13:18:00', 403361.34, 76638.66, 480000, 'pagada', 480000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(68, 68, 'FE-000068', '2026-09-06 14:54:00', 1907563.03, 362436.97, 2270000, 'pagada', 2270000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(69, 69, 'FE-000069', '2026-09-17 12:47:00', 1546218.49, 293781.51, 1840000, 'pagada', 1840000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(70, 70, 'FE-000070', '2026-08-22 17:36:00', 3277310.92, 622689.08, 3900000, 'pagada', 3900000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(71, 71, 'FE-000071', '2026-08-03 15:32:00', 579831.93, 110168.07, 690000, 'pagada', 690000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(72, 72, 'FE-000072', '2026-08-11 13:05:00', 3697478.99, 702521.01, 4400000, 'pagada', 4400000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(73, 73, 'FE-000073', '2026-08-15 09:42:00', 2310924.37, 439075.63, 2750000, 'anulada', 2750000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(74, 74, 'FE-000074', '2026-07-28 16:20:00', 1663865.55, 316134.45, 1980000, 'pagada', 1980000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(75, 75, 'FE-000075', '2026-07-08 08:12:00', 8697478.99, 1652521.01, 10350000, 'pagada', 10350000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(76, 76, 'FE-000076', '2026-07-29 13:46:00', 579831.93, 110168.07, 690000, 'pagada', 690000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(77, 77, 'FE-000077', '2026-08-27 10:30:00', 5462184.87, 1037815.13, 6500000, 'pagada', 6500000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(78, 78, 'FE-000078', '2026-07-27 10:15:00', 966386.55, 183613.45, 1150000, 'pagada', 1150000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(79, 79, 'FE-000079', '2026-09-04 13:17:00', 1588235.29, 301764.71, 1890000, 'pagada', 1890000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(80, 80, 'FE-000080', '2026-09-01 21:41:00', 4025210.08, 764789.92, 4790000, 'pagada', 4790000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(81, 81, 'FE-000081', '2026-08-07 20:16:00', 3210084.03, 609915.97, 3820000, 'pagada', 3820000, 'pse', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(82, 82, 'FE-000082', '2026-10-10 12:36:00', 747899.16, 142100.84, 890000, 'anulada', 0, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(83, 83, 'FE-000083', '2026-10-04 14:56:00', 3974789.92, 755210.08, 4730000, 'pagada', 4730000, 'nequi', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(84, 84, 'FE-000084', '2026-09-03 21:29:00', 9042016.81, 1717983.19, 10760000, 'pagada', 10760000, 'tarjeta', '2026-10-27 19:51:20', '2026-10-27 19:51:20'),
(85, 85, 'FE-000085', '2026-07-07 10:33:00', 10966386.55, 2083613.45, 13050000, 'pagada', 13050000, 'contraentrega', '2026-10-27 19:51:20', '2026-10-27 19:51:20');

SET FOREIGN_KEY_CHECKS = 1;

-- Fin del script. Usuario administrador: admin@raidersparadise.co / Raiders2026*

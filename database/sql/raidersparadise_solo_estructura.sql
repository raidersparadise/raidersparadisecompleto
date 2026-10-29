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

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--  Raiders Paradise - Consultas para validar los datos registrados
--  Úsalas en phpMyAdmin (pestaña SQL) con la base `raidersparadise`.
-- =====================================================================
USE `raidersparadise`;

-- 1. Usuarios y su rol
SELECT u.id_usuario, u.nombre_usuario, u.apellido_usuario, u.email, r.nombre_rol, u.activo
FROM usuario u JOIN rol r ON r.id_rol = u.id_rol
ORDER BY r.id_rol, u.nombre_usuario;

-- 2. Últimos 10 pedidos con cliente, estado y factura
SELECT p.id_pedido, p.fecha, CONCAT(c.nombre_cliente, ' ', c.apellido_cliente) AS cliente,
       p.estado, p.total, f.numero_factura, f.metodo_pago, f.estado_factura
FROM pedido p
JOIN cliente c ON c.id_cliente = p.id_cliente
LEFT JOIN factura f ON f.id_pedido = p.id_pedido
ORDER BY p.fecha DESC
LIMIT 10;

-- 3. Detalle de un pedido (cambia el 1 por el número que quieras revisar)
SELECT d.id_pedido, pr.sku, pr.nombre_producto, d.cantidad, d.precio_unitario, d.sub_total
FROM detalle_pedido d JOIN producto pr ON pr.id_producto = d.id_producto
WHERE d.id_pedido = 1;

-- 4. Inventario: productos en stock bajo o agotados
SELECT pr.sku, pr.nombre_producto, i.cantidad_disponible, i.cantidad_minima,
       CASE WHEN i.cantidad_disponible = 0 THEN 'Agotado'
            WHEN i.cantidad_disponible <= i.cantidad_minima THEN 'Stock bajo'
            ELSE 'Normal' END AS estado
FROM inventario i JOIN producto pr ON pr.id_producto = i.id_producto
ORDER BY i.cantidad_disponible;

-- 5. Ventas por mes (sin pedidos cancelados)
SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COUNT(*) AS pedidos, SUM(total) AS ventas
FROM pedido WHERE estado <> 'cancelado'
GROUP BY DATE_FORMAT(fecha, '%Y-%m') ORDER BY mes;

-- 6. Comprobación de facturas: subtotal + IVA debe ser igual al total (debe devolver 0 filas)
SELECT numero_factura, subtotal, impuesto, total_factura
FROM factura WHERE ABS(subtotal + impuesto - total_factura) > 0.01;

-- 7. PQR con su responsable
SELECT q.id_pqr, q.fecha, q.tipo, q.asunto, q.estado,
       CONCAT(c.nombre_cliente, ' ', c.apellido_cliente) AS cliente,
       COALESCE(CONCAT(u.nombre_usuario, ' ', u.apellido_usuario), 'Sin asignar') AS responsable
FROM pqr q JOIN cliente c ON c.id_cliente = q.id_cliente
LEFT JOIN usuario u ON u.id_usuario = q.id_usuario
ORDER BY q.fecha DESC;

-- 8. Reportes generados por el administrador (se llena al usar el módulo de reportes)
SELECT r.id_reporte, r.tipo_reporte, r.formato, r.fecha_desde, r.fecha_hasta, r.fecha_generacion, u.email
FROM reporte r JOIN usuario u ON u.id_usuario = r.id_usuario
ORDER BY r.fecha_generacion DESC;

-- 9. Conteo general de registros por tabla
SELECT 'usuario' tabla, COUNT(*) total FROM usuario UNION ALL
SELECT 'cliente', COUNT(*) FROM cliente UNION ALL
SELECT 'producto', COUNT(*) FROM producto UNION ALL
SELECT 'pedido', COUNT(*) FROM pedido UNION ALL
SELECT 'detalle_pedido', COUNT(*) FROM detalle_pedido UNION ALL
SELECT 'factura', COUNT(*) FROM factura UNION ALL
SELECT 'pqr', COUNT(*) FROM pqr UNION ALL
SELECT 'reporte', COUNT(*) FROM reporte;

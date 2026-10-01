-- Datos de ejemplo, tomados de docs/plan-del-mes-2.pdf.
-- Ejecutar después de schema.sql (opcional: solo si quieres partir con estos datos).
-- Crea el plan del mes actual con el ingreso, ingresos extra, pagos, compras,
-- gastos semanales/diarios y deudas que ya tenías cargados en el standalone.

INSERT INTO planes (nombre, anio, mes, ingreso_principal)
VALUES ('Mi plan del mes', YEAR(CURDATE()), MONTH(CURDATE()), 2670.00);

SET @plan_id = LAST_INSERT_ID();

INSERT INTO ingresos_extra (plan_id, nombre, monto) VALUES
(@plan_id, 'Prima', 150.00),
(@plan_id, 'Otro ingreso', 2000.00);

INSERT INTO items (plan_id, categoria, nombre, dia, monto, es_necesario) VALUES
-- Pagos fijos con fecha
(@plan_id, 'fijo', 'Movilidad', 8, 170.00, 1),
(@plan_id, 'fijo', 'Colegio Arely', 10, 570.00, 1),
(@plan_id, 'fijo', 'Facturación', 1, 120.00, 1),
(@plan_id, 'fijo', 'Host', 18, 40.00, 1),
(@plan_id, 'fijo', 'IA Claude', 1, 80.00, 1),
(@plan_id, 'fijo', 'Colegio Aracely', 1, 550.00, 1),
(@plan_id, 'fijo', 'Cuota colegios', 1, 40.00, 1),
(@plan_id, 'fijo', 'Otras cuotas', 1, 200.00, 1),

-- Compras o gastos del mes
(@plan_id, 'compra', 'Arroz', 1, 30.00, 1),
(@plan_id, 'compra', 'Azúcar', 1, 15.00, 1),
(@plan_id, 'compra', 'Avena', 1, 8.00, 1),
(@plan_id, 'compra', 'Fideos', 1, 24.00, 1),
(@plan_id, 'compra', 'Manzanilla', 1, 10.00, 1),
(@plan_id, 'compra', 'Pañales', 1, 75.00, 1),
(@plan_id, 'compra', 'Taponeras', 1, 72.00, 1),
(@plan_id, 'compra', 'Papel higiénico', 1, 40.00, 1),
(@plan_id, 'compra', 'Paños húmedos', 1, 36.00, 1),
(@plan_id, 'compra', 'Leche Aracelly', 1, 45.00, 0),

-- Gastos semanales
(@plan_id, 'semanal', 'Fruta', NULL, 30.00, 1),
(@plan_id, 'semanal', 'Verduras', NULL, 20.00, 1),
(@plan_id, 'semanal', 'Pollo', NULL, 60.00, 1),
(@plan_id, 'semanal', 'Huevos', NULL, 15.00, 1),
(@plan_id, 'semanal', 'Combustible', NULL, 40.00, 1),
(@plan_id, 'semanal', 'Salida a comer', NULL, 50.00, 0),

-- Gastos diarios
(@plan_id, 'diario', 'Pan', NULL, 3.00, 1),

-- Deudas y préstamos pendientes
(@plan_id, 'deuda', 'BBVA', 1, 560.00, 1),
(@plan_id, 'deuda', 'Deuda Carlos', 1, 800.00, 1),
(@plan_id, 'deuda', 'Movilidad atrasada', 1, 170.00, 1),
(@plan_id, 'deuda', 'Préstamo Laura', 1, 100.00, 1),
(@plan_id, 'deuda', 'Préstamo Nelson', 1, 20.00, 1),
(@plan_id, 'deuda', 'Prima devolución', 1, 200.00, 1),
(@plan_id, 'deuda', 'Préstamo muebles', 1, 360.00, 1),
(@plan_id, 'deuda', 'Préstamo Leydi', 1, 420.00, 1),
(@plan_id, 'deuda', 'Caja Metropolitana', 1, 110.00, 1);

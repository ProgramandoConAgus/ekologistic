-- SQL script to add columns for remaining box data in dispatch table
-- Adds columns with the same names as the originals but with a '_restante' suffix

ALTER TABLE dispatch
    ADD COLUMN valor_unitario_restante DOUBLE NULL,
    ADD COLUMN valor_restante DOUBLE NULL,
    ADD COLUMN unidad_restante VARCHAR(50) DEFAULT NULL,
    ADD COLUMN longitud_in_restante DOUBLE NULL,
    ADD COLUMN ancho_in_restante DOUBLE NULL,
    ADD COLUMN altura_in_restante DOUBLE NULL,
    ADD COLUMN peso_lb_restante DOUBLE NULL;

-- SQL script to store pallet and box counts for remaining products
ALTER TABLE dispatch
    ADD COLUMN palets_restante INT DEFAULT 0,
    ADD COLUMN cantidad_restante INT DEFAULT 0;

-- SQL script to add columns for remaining pallet and box counts
ALTER TABLE dispatch
    ADD COLUMN palets_restante INT DEFAULT 0,
    ADD COLUMN cantidad_restante INT DEFAULT 0;

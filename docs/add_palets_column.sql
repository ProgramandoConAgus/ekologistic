-- SQL script to add a palets column to store pallet counts per dispatch
ALTER TABLE dispatch
    ADD COLUMN palets INT DEFAULT 0 AFTER modelo;

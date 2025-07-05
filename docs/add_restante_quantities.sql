-- Adds palets_restante and cantidad_restante to track leftover pallet and box counts
ALTER TABLE dispatch
    ADD COLUMN palets_restante INT DEFAULT 0,
    ADD COLUMN cantidad_restante INT DEFAULT 0;

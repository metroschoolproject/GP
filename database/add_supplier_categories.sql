CREATE TABLE IF NOT EXISTS supplier_categories (
  id BIGINT NOT NULL AUTO_INCREMENT,
  supplier_id BIGINT NOT NULL,
  category_id BIGINT NOT NULL,
  source ENUM('ai','manual','admin') NOT NULL DEFAULT 'manual',
  confidence DECIMAL(5,4) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_supplier_category (supplier_id, category_id),
  KEY idx_supplier_categories_supplier (supplier_id),
  KEY idx_supplier_categories_category (category_id),
  CONSTRAINT supplier_categories_supplier_fk
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT supplier_categories_category_fk
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

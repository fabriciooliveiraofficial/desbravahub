CREATE TABLE IF NOT EXISTS `learning_programs` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `tenant_id` INTEGER NOT NULL,
    `category_id` INTEGER,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `image_url` VARCHAR(255),
    `type` VARCHAR(50) DEFAULT 'specialty',
    `status` VARCHAR(50) DEFAULT 'draft',
    `sort_order` INTEGER DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- We are just creating a migration file to add sort_order
ALTER TABLE learning_programs ADD COLUMN sort_order INT DEFAULT 0 AFTER status;

-- Clearance Module Schema
-- Migration: 004_clearances_schema.sql
USE hris_db1;

CREATE TABLE IF NOT EXISTS hris_clearances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,

    clearance_type ENUM(
        'Resignation','Retirement','End of Contract','Transfer','Others'
    ) NOT NULL DEFAULT 'Others',

    purpose TEXT DEFAULT NULL,
    request_date DATE NOT NULL,

    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    remarks TEXT DEFAULT NULL,

    processed_by INT UNSIGNED DEFAULT NULL,
    processed_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES hris_employees(id) ON DELETE CASCADE,
    INDEX idx_clr_employee (employee_id),
    INDEX idx_clr_status (status),
    INDEX idx_clr_request_date (request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hris_clearance_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clearance_id INT UNSIGNED NOT NULL,

    office_name VARCHAR(100) NOT NULL DEFAULT '',
    responsible_person VARCHAR(100) DEFAULT NULL,

    status ENUM('Pending','Cleared','Not Applicable') NOT NULL DEFAULT 'Pending',
    cleared_at DATETIME DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,

    sort_order INT NOT NULL DEFAULT 0,

    FOREIGN KEY (clearance_id) REFERENCES hris_clearances(id) ON DELETE CASCADE,
    INDEX idx_clr_item_clearance (clearance_id),
    INDEX idx_clr_item_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

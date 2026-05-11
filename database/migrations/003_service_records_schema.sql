-- Service Record Module Schema
-- Migration: 003_service_records_schema.sql

CREATE TABLE IF NOT EXISTS hris_service_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,

    position_title VARCHAR(150) NOT NULL DEFAULT '',
    item_number VARCHAR(50) DEFAULT NULL,
    salary_grade VARCHAR(10) DEFAULT NULL,
    salary_step VARCHAR(5) DEFAULT NULL,
    monthly_salary DECIMAL(12,2) DEFAULT NULL,

    appointment_status ENUM(
        'Permanent','Temporary','Coterminous','COS',
        'Job Order','Casual','Contractual'
    ) DEFAULT NULL,

    appointment_nature ENUM(
        'Original','Promotional','Transfer','Reinstatement',
        'Renewal','Reclassification','Demotion'
    ) DEFAULT NULL,

    office_unit VARCHAR(150) DEFAULT NULL,
    division VARCHAR(150) DEFAULT NULL,

    date_from DATE DEFAULT NULL,
    date_to DATE DEFAULT NULL,          -- NULL means currently serving
    is_current TINYINT(1) NOT NULL DEFAULT 0,

    separation_type ENUM(
        'Resigned','Retired','LWOP','Dismissed',
        'End of Contract','Transfer','Death','Others'
    ) DEFAULT NULL,
    separation_date DATE DEFAULT NULL,

    remarks TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES hris_employees(id) ON DELETE CASCADE,
    INDEX idx_sr_employee (employee_id),
    INDEX idx_sr_current (is_current),
    INDEX idx_sr_date_from (date_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

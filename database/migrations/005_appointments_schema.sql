-- Appointments Module Schema
-- Migration: 005_appointments_schema.sql
USE hris_db1;

CREATE TABLE IF NOT EXISTS hris_appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,

    appointment_type ENUM(
        'Original','Promotional','Transfer','Reinstatement','Renewal','Others'
    ) NOT NULL DEFAULT 'Original',

    position_title VARCHAR(150) NOT NULL DEFAULT '',
    item_number VARCHAR(50) DEFAULT NULL,

    salary_grade VARCHAR(10) DEFAULT NULL,
    salary_step VARCHAR(5) DEFAULT NULL,
    monthly_salary DECIMAL(12,2) DEFAULT NULL,

    employment_status ENUM(
        'Permanent','Temporary','Coterminous','COS','Job Order','Casual'
    ) DEFAULT NULL,

    office_unit VARCHAR(150) DEFAULT NULL,
    division VARCHAR(150) DEFAULT NULL,

    effectivity_date DATE DEFAULT NULL,
    oath_date DATE DEFAULT NULL,
    report_date DATE DEFAULT NULL,

    is_current TINYINT(1) NOT NULL DEFAULT 0,
    remarks TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES hris_employees(id) ON DELETE CASCADE,
    INDEX idx_apt_employee (employee_id),
    INDEX idx_apt_current (is_current),
    INDEX idx_apt_effectivity (effectivity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

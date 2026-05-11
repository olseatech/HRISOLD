-- 201 Documents Module Schema
-- Migration: 006_documents_201_schema.sql
USE hris_db1;

CREATE TABLE IF NOT EXISTS hris_documents_201 (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,

    doc_category ENUM(
        'PDS','Appointment','Service Record','Clearance',
        'Certificate','ID','Others'
    ) NOT NULL DEFAULT 'Others',

    title VARCHAR(200) NOT NULL DEFAULT '',
    description TEXT DEFAULT NULL,

    original_filename VARCHAR(255) NOT NULL DEFAULT '',
    stored_filename VARCHAR(255) NOT NULL DEFAULT '',
    file_size INT UNSIGNED DEFAULT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,

    uploaded_by INT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES hris_employees(id) ON DELETE CASCADE,
    INDEX idx_doc_employee (employee_id),
    INDEX idx_doc_category (doc_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

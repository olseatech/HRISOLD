-- Leave Enhancement: Holidays, Attachments, and Status Expansion
USE hris_db1;

-- ── Holidays ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS hris_holidays (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    holiday_date DATE NOT NULL,
    holiday_type ENUM('Regular','Special Non-Working','Special Working') NOT NULL DEFAULT 'Regular',
    is_recurring TINYINT(1) NOT NULL DEFAULT 0,
    remarks      VARCHAR(255) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_holiday_date (holiday_date)
);

-- ── Leave Attachments ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS hris_leave_attachments (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leave_request_id INT UNSIGNED NOT NULL,
    original_filename VARCHAR(255) NOT NULL DEFAULT '',
    stored_filename  VARCHAR(255) NOT NULL DEFAULT '',
    file_size        INT UNSIGNED DEFAULT NULL,
    mime_type        VARCHAR(100) DEFAULT NULL,
    uploaded_by      INT UNSIGNED DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leave_request_id) REFERENCES hris_leave_requests(id) ON DELETE CASCADE,
    INDEX idx_attach_leave (leave_request_id)
);

-- ── Expand status enum + add submitted_at ─────────────────────────────────────
ALTER TABLE hris_leave_requests
    MODIFY COLUMN status ENUM('Draft','Pending','Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
    ADD COLUMN IF NOT EXISTS submitted_at DATETIME DEFAULT NULL AFTER status;

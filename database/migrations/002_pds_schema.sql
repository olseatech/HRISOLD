-- PDS (Personal Data Sheet) Module Schema
-- Migration: 002_pds_schema.sql

CREATE TABLE IF NOT EXISTS hris_pds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    status ENUM('Draft','Complete') NOT NULL DEFAULT 'Draft',

    -- Personal Information
    surname VARCHAR(80) NOT NULL DEFAULT '',
    first_name VARCHAR(80) NOT NULL DEFAULT '',
    middle_name VARCHAR(80) DEFAULT NULL,
    name_extension VARCHAR(10) DEFAULT NULL,
    birthdate DATE DEFAULT NULL,
    birthplace VARCHAR(150) DEFAULT NULL,
    sex ENUM('Male','Female') DEFAULT NULL,
    civil_status ENUM('Single','Married','Widowed','Separated') DEFAULT NULL,
    height DECIMAL(5,2) DEFAULT NULL,
    weight DECIMAL(5,2) DEFAULT NULL,
    blood_type VARCHAR(5) DEFAULT NULL,
    dual_citizenship TINYINT(1) NOT NULL DEFAULT 0,
    citizenship_by ENUM('birth','naturalization') DEFAULT NULL,
    citizenship_country VARCHAR(80) DEFAULT NULL,

    -- Residential Address
    res_house VARCHAR(30) DEFAULT NULL,
    res_street VARCHAR(100) DEFAULT NULL,
    res_subdivision VARCHAR(100) DEFAULT NULL,
    res_barangay VARCHAR(100) DEFAULT NULL,
    res_city VARCHAR(100) DEFAULT NULL,
    res_province VARCHAR(100) DEFAULT NULL,
    res_zip VARCHAR(10) DEFAULT NULL,

    -- Permanent Address
    per_house VARCHAR(30) DEFAULT NULL,
    per_street VARCHAR(100) DEFAULT NULL,
    per_subdivision VARCHAR(100) DEFAULT NULL,
    per_barangay VARCHAR(100) DEFAULT NULL,
    per_city VARCHAR(100) DEFAULT NULL,
    per_province VARCHAR(100) DEFAULT NULL,
    per_zip VARCHAR(10) DEFAULT NULL,

    -- Contact Details
    telephone VARCHAR(30) DEFAULT NULL,
    mobile VARCHAR(30) DEFAULT NULL,
    personal_email VARCHAR(150) DEFAULT NULL,

    -- Government IDs
    gsis_id VARCHAR(30) DEFAULT NULL,
    pagibig_id VARCHAR(30) DEFAULT NULL,
    philhealth_id VARCHAR(30) DEFAULT NULL,
    sss_no VARCHAR(30) DEFAULT NULL,
    tin_no VARCHAR(30) DEFAULT NULL,
    agency_employee_no VARCHAR(30) DEFAULT NULL,

    -- Family Background: Spouse
    spouse_surname VARCHAR(80) DEFAULT NULL,
    spouse_firstname VARCHAR(80) DEFAULT NULL,
    spouse_middlename VARCHAR(80) DEFAULT NULL,
    spouse_extension VARCHAR(10) DEFAULT NULL,
    spouse_occupation VARCHAR(100) DEFAULT NULL,
    spouse_employer VARCHAR(150) DEFAULT NULL,
    spouse_business_address VARCHAR(255) DEFAULT NULL,
    spouse_telephone VARCHAR(30) DEFAULT NULL,

    -- Family Background: Father
    father_surname VARCHAR(80) DEFAULT NULL,
    father_firstname VARCHAR(80) DEFAULT NULL,
    father_middlename VARCHAR(80) DEFAULT NULL,
    father_extension VARCHAR(10) DEFAULT NULL,

    -- Family Background: Mother (maiden name)
    mother_surname VARCHAR(80) DEFAULT NULL,
    mother_firstname VARCHAR(80) DEFAULT NULL,
    mother_middlename VARCHAR(80) DEFAULT NULL,

    -- Security / Clearance Questions
    q1_answer ENUM('Yes','No') DEFAULT NULL,
    q1_details TEXT DEFAULT NULL,
    q2_answer ENUM('Yes','No') DEFAULT NULL,
    q2_details TEXT DEFAULT NULL,
    q3_answer ENUM('Yes','No') DEFAULT NULL,
    q3_details TEXT DEFAULT NULL,
    q4_answer ENUM('Yes','No') DEFAULT NULL,
    q4_details TEXT DEFAULT NULL,
    q5_answer ENUM('Yes','No') DEFAULT NULL,
    q5_details TEXT DEFAULT NULL,
    q6_answer ENUM('Yes','No') DEFAULT NULL,
    q6_details TEXT DEFAULT NULL,
    q7_answer ENUM('Yes','No') DEFAULT NULL,
    q7_details TEXT DEFAULT NULL,
    q8_answer ENUM('Yes','No') DEFAULT NULL,
    q8_details TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_pds_employee (employee_id),
    FOREIGN KEY (employee_id) REFERENCES hris_employees(id) ON DELETE CASCADE,
    INDEX idx_pds_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Children
CREATE TABLE IF NOT EXISTS hris_pds_children (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    child_name VARCHAR(150) NOT NULL DEFAULT '',
    child_dob DATE DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_children_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Educational Background
CREATE TABLE IF NOT EXISTS hris_pds_education (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    level ENUM('Elementary','Secondary','Vocational','College','Graduate') NOT NULL,
    school_name VARCHAR(200) NOT NULL DEFAULT '',
    degree_course VARCHAR(150) DEFAULT NULL,
    period_from VARCHAR(10) DEFAULT NULL,
    period_to VARCHAR(10) DEFAULT NULL,
    units_earned VARCHAR(20) DEFAULT NULL,
    year_graduated VARCHAR(4) DEFAULT NULL,
    scholarship_honors VARCHAR(200) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_edu_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Civil Service Eligibility
CREATE TABLE IF NOT EXISTS hris_pds_civil_service (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    career_service VARCHAR(200) NOT NULL DEFAULT '',
    rating VARCHAR(20) DEFAULT NULL,
    exam_date DATE DEFAULT NULL,
    exam_place VARCHAR(200) DEFAULT NULL,
    license_number VARCHAR(60) DEFAULT NULL,
    license_validity DATE DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_cs_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Work Experience
CREATE TABLE IF NOT EXISTS hris_pds_work_experience (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    date_from DATE DEFAULT NULL,
    date_to DATE DEFAULT NULL,
    position_title VARCHAR(150) NOT NULL DEFAULT '',
    department_agency VARCHAR(200) DEFAULT NULL,
    monthly_salary DECIMAL(12,2) DEFAULT NULL,
    salary_grade VARCHAR(10) DEFAULT NULL,
    appointment_status VARCHAR(60) DEFAULT NULL,
    is_govt_service TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_we_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Voluntary Work
CREATE TABLE IF NOT EXISTS hris_pds_voluntary_work (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    organization VARCHAR(200) NOT NULL DEFAULT '',
    date_from DATE DEFAULT NULL,
    date_to DATE DEFAULT NULL,
    hours_no INT DEFAULT NULL,
    nature_of_work VARCHAR(200) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_vw_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Learning and Development
CREATE TABLE IF NOT EXISTS hris_pds_learning_development (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    title VARCHAR(250) NOT NULL DEFAULT '',
    date_from DATE DEFAULT NULL,
    date_to DATE DEFAULT NULL,
    hours_no INT DEFAULT NULL,
    ld_type ENUM('Managerial','Supervisory','Technical','Foundation') DEFAULT NULL,
    conducted_by VARCHAR(200) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_ld_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Other Information (skills, recognitions, memberships)
CREATE TABLE IF NOT EXISTS hris_pds_other_info (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    info_type ENUM('skill','recognition','membership') NOT NULL,
    value VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_oi_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- References
CREATE TABLE IF NOT EXISTS hris_pds_references (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pds_id INT UNSIGNED NOT NULL,
    ref_name VARCHAR(100) NOT NULL DEFAULT '',
    ref_address VARCHAR(255) DEFAULT NULL,
    ref_telephone VARCHAR(30) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pds_id) REFERENCES hris_pds(id) ON DELETE CASCADE,
    INDEX idx_pds_ref_pds (pds_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

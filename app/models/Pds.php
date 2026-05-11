<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Pds extends Model
{
    // -------------------------------------------------------------------------
    // Main PDS record
    // -------------------------------------------------------------------------

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT p.*, e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_pds p
             JOIN hris_employees e ON e.id = p.employee_id
             WHERE p.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByEmployee(int $employeeId): ?array
    {
        return $this->fetchOne(
            'SELECT p.*, e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_pds p
             JOIN hris_employees e ON e.id = p.employee_id
             WHERE p.employee_id = :eid LIMIT 1',
            ['eid' => $employeeId]
        );
    }

    public function search(string $query, int $page, int $perPage): array
    {
        $params = [];
        $where  = $this->searchWhere($query, $params);
        $offset = ($page - 1) * $perPage;
        return $this->fetchAll(
            "SELECT p.id, p.status, p.updated_at,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last,
                    p.surname, p.first_name
             FROM hris_pds p
             JOIN hris_employees e ON e.id = p.employee_id
             {$where}
             ORDER BY e.last_name ASC, e.first_name ASC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );
    }

    public function countSearch(string $query): int
    {
        $params = [];
        $where  = $this->searchWhere($query, $params);
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM hris_pds p
             JOIN hris_employees e ON e.id = p.employee_id
             {$where}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    private function searchWhere(string $query, array &$params): string
    {
        if ($query === '') {
            return '';
        }
        $params['q1'] = '%' . $query . '%';
        $params['q2'] = '%' . $query . '%';
        $params['q3'] = '%' . $query . '%';
        $params['q4'] = '%' . $query . '%';
        return 'WHERE (e.employee_code LIKE :q1 OR e.first_name LIKE :q2 OR e.last_name LIKE :q3 OR p.surname LIKE :q4)';
    }

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO hris_pds (
                employee_id, status,
                surname, first_name, middle_name, name_extension,
                birthdate, birthplace, sex, civil_status,
                height, weight, blood_type,
                dual_citizenship, citizenship_by, citizenship_country,
                res_house, res_street, res_subdivision, res_barangay, res_city, res_province, res_zip,
                per_house, per_street, per_subdivision, per_barangay, per_city, per_province, per_zip,
                telephone, mobile, personal_email,
                gsis_id, pagibig_id, philhealth_id, sss_no, tin_no, agency_employee_no,
                spouse_surname, spouse_firstname, spouse_middlename, spouse_extension,
                spouse_occupation, spouse_employer, spouse_business_address, spouse_telephone,
                father_surname, father_firstname, father_middlename, father_extension,
                mother_surname, mother_firstname, mother_middlename,
                q1_answer, q1_details, q2_answer, q2_details, q3_answer, q3_details,
                q4_answer, q4_details, q5_answer, q5_details, q6_answer, q6_details,
                q7_answer, q7_details, q8_answer, q8_details
            ) VALUES (
                :employee_id, :status,
                :surname, :first_name, :middle_name, :name_extension,
                :birthdate, :birthplace, :sex, :civil_status,
                :height, :weight, :blood_type,
                :dual_citizenship, :citizenship_by, :citizenship_country,
                :res_house, :res_street, :res_subdivision, :res_barangay, :res_city, :res_province, :res_zip,
                :per_house, :per_street, :per_subdivision, :per_barangay, :per_city, :per_province, :per_zip,
                :telephone, :mobile, :personal_email,
                :gsis_id, :pagibig_id, :philhealth_id, :sss_no, :tin_no, :agency_employee_no,
                :spouse_surname, :spouse_firstname, :spouse_middlename, :spouse_extension,
                :spouse_occupation, :spouse_employer, :spouse_business_address, :spouse_telephone,
                :father_surname, :father_firstname, :father_middlename, :father_extension,
                :mother_surname, :mother_firstname, :mother_middlename,
                :q1_answer, :q1_details, :q2_answer, :q2_details, :q3_answer, :q3_details,
                :q4_answer, :q4_details, :q5_answer, :q5_details, :q6_answer, :q6_details,
                :q7_answer, :q7_details, :q8_answer, :q8_details
            )',
            $this->mainParams($d)
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateById(int $id, array $d): bool
    {
        return $this->execute(
            'UPDATE hris_pds SET
                status = :status,
                surname = :surname, first_name = :first_name, middle_name = :middle_name,
                name_extension = :name_extension,
                birthdate = :birthdate, birthplace = :birthplace, sex = :sex,
                civil_status = :civil_status,
                height = :height, weight = :weight, blood_type = :blood_type,
                dual_citizenship = :dual_citizenship, citizenship_by = :citizenship_by,
                citizenship_country = :citizenship_country,
                res_house = :res_house, res_street = :res_street,
                res_subdivision = :res_subdivision, res_barangay = :res_barangay,
                res_city = :res_city, res_province = :res_province, res_zip = :res_zip,
                per_house = :per_house, per_street = :per_street,
                per_subdivision = :per_subdivision, per_barangay = :per_barangay,
                per_city = :per_city, per_province = :per_province, per_zip = :per_zip,
                telephone = :telephone, mobile = :mobile, personal_email = :personal_email,
                gsis_id = :gsis_id, pagibig_id = :pagibig_id, philhealth_id = :philhealth_id,
                sss_no = :sss_no, tin_no = :tin_no, agency_employee_no = :agency_employee_no,
                spouse_surname = :spouse_surname, spouse_firstname = :spouse_firstname,
                spouse_middlename = :spouse_middlename, spouse_extension = :spouse_extension,
                spouse_occupation = :spouse_occupation, spouse_employer = :spouse_employer,
                spouse_business_address = :spouse_business_address,
                spouse_telephone = :spouse_telephone,
                father_surname = :father_surname, father_firstname = :father_firstname,
                father_middlename = :father_middlename, father_extension = :father_extension,
                mother_surname = :mother_surname, mother_firstname = :mother_firstname,
                mother_middlename = :mother_middlename,
                q1_answer = :q1_answer, q1_details = :q1_details,
                q2_answer = :q2_answer, q2_details = :q2_details,
                q3_answer = :q3_answer, q3_details = :q3_details,
                q4_answer = :q4_answer, q4_details = :q4_details,
                q5_answer = :q5_answer, q5_details = :q5_details,
                q6_answer = :q6_answer, q6_details = :q6_details,
                q7_answer = :q7_answer, q7_details = :q7_details,
                q8_answer = :q8_answer, q8_details = :q8_details
             WHERE id = :id',
            array_merge($this->mainParams($d), ['id' => $id])
        );
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM hris_pds WHERE id = :id', ['id' => $id]);
    }

    public function employeeOptions(): array
    {
        return $this->fetchAll(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name
             FROM hris_employees e
             WHERE e.employment_status NOT IN ('Resigned','Terminated')
             ORDER BY e.last_name ASC, e.first_name ASC"
        );
    }

    // -------------------------------------------------------------------------
    // Children
    // -------------------------------------------------------------------------

    public function getChildren(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_children WHERE pds_id = :pid ORDER BY sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteChildren(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_children WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertChildren(int $pdsId, array $rows): void
    {
        foreach ($rows as $i => $row) {
            $name = trim((string) ($row['child_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $dob = trim((string) ($row['child_dob'] ?? ''));
            $this->execute(
                'INSERT INTO hris_pds_children (pds_id, child_name, child_dob, sort_order)
                 VALUES (:pid, :name, :dob, :sort)',
                ['pid' => $pdsId, 'name' => $name, 'dob' => ($dob !== '' ? $dob : null), 'sort' => $i]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Education
    // -------------------------------------------------------------------------

    public function getEducation(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_education WHERE pds_id = :pid ORDER BY sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteEducation(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_education WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertEducation(int $pdsId, array $rows): void
    {
        $allowed = ['Elementary','Secondary','Vocational','College','Graduate'];
        foreach ($rows as $i => $row) {
            $school = trim((string) ($row['school_name'] ?? ''));
            $level  = trim((string) ($row['level'] ?? ''));
            if ($school === '' || !in_array($level, $allowed, true)) {
                continue;
            }
            $this->execute(
                'INSERT INTO hris_pds_education
                    (pds_id, level, school_name, degree_course, period_from, period_to,
                     units_earned, year_graduated, scholarship_honors, sort_order)
                 VALUES
                    (:pid, :level, :school, :degree, :pfrom, :pto,
                     :units, :ygrad, :honors, :sort)',
                [
                    'pid'    => $pdsId,
                    'level'  => $level,
                    'school' => $school,
                    'degree' => trim((string) ($row['degree_course'] ?? '')) ?: null,
                    'pfrom'  => trim((string) ($row['period_from'] ?? '')) ?: null,
                    'pto'    => trim((string) ($row['period_to'] ?? '')) ?: null,
                    'units'  => trim((string) ($row['units_earned'] ?? '')) ?: null,
                    'ygrad'  => trim((string) ($row['year_graduated'] ?? '')) ?: null,
                    'honors' => trim((string) ($row['scholarship_honors'] ?? '')) ?: null,
                    'sort'   => $i,
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Civil Service Eligibility
    // -------------------------------------------------------------------------

    public function getCivilService(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_civil_service WHERE pds_id = :pid ORDER BY sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteCivilService(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_civil_service WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertCivilService(int $pdsId, array $rows): void
    {
        foreach ($rows as $i => $row) {
            $cs = trim((string) ($row['career_service'] ?? ''));
            if ($cs === '') {
                continue;
            }
            $examDate    = trim((string) ($row['exam_date'] ?? ''));
            $licValidity = trim((string) ($row['license_validity'] ?? ''));
            $this->execute(
                'INSERT INTO hris_pds_civil_service
                    (pds_id, career_service, rating, exam_date, exam_place,
                     license_number, license_validity, sort_order)
                 VALUES
                    (:pid, :cs, :rating, :edate, :eplace, :lnum, :lval, :sort)',
                [
                    'pid'    => $pdsId,
                    'cs'     => $cs,
                    'rating' => trim((string) ($row['rating'] ?? '')) ?: null,
                    'edate'  => ($examDate !== '' ? $examDate : null),
                    'eplace' => trim((string) ($row['exam_place'] ?? '')) ?: null,
                    'lnum'   => trim((string) ($row['license_number'] ?? '')) ?: null,
                    'lval'   => ($licValidity !== '' ? $licValidity : null),
                    'sort'   => $i,
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Work Experience
    // -------------------------------------------------------------------------

    public function getWorkExperience(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_work_experience WHERE pds_id = :pid ORDER BY sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteWorkExperience(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_work_experience WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertWorkExperience(int $pdsId, array $rows): void
    {
        foreach ($rows as $i => $row) {
            $title = trim((string) ($row['position_title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $dateFrom = trim((string) ($row['date_from'] ?? ''));
            $dateTo   = trim((string) ($row['date_to'] ?? ''));
            $salary   = trim((string) ($row['monthly_salary'] ?? ''));
            $this->execute(
                'INSERT INTO hris_pds_work_experience
                    (pds_id, date_from, date_to, position_title, department_agency,
                     monthly_salary, salary_grade, appointment_status, is_govt_service, sort_order)
                 VALUES
                    (:pid, :dfrom, :dto, :title, :dept,
                     :salary, :sg, :appt, :govt, :sort)',
                [
                    'pid'    => $pdsId,
                    'dfrom'  => ($dateFrom !== '' ? $dateFrom : null),
                    'dto'    => ($dateTo !== '' ? $dateTo : null),
                    'title'  => $title,
                    'dept'   => trim((string) ($row['department_agency'] ?? '')) ?: null,
                    'salary' => ($salary !== '' && is_numeric($salary) ? (float) $salary : null),
                    'sg'     => trim((string) ($row['salary_grade'] ?? '')) ?: null,
                    'appt'   => trim((string) ($row['appointment_status'] ?? '')) ?: null,
                    'govt'   => isset($row['is_govt_service']) && $row['is_govt_service'] ? 1 : 0,
                    'sort'   => $i,
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Voluntary Work
    // -------------------------------------------------------------------------

    public function getVoluntaryWork(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_voluntary_work WHERE pds_id = :pid ORDER BY sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteVoluntaryWork(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_voluntary_work WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertVoluntaryWork(int $pdsId, array $rows): void
    {
        foreach ($rows as $i => $row) {
            $org = trim((string) ($row['organization'] ?? ''));
            if ($org === '') {
                continue;
            }
            $dateFrom = trim((string) ($row['date_from'] ?? ''));
            $dateTo   = trim((string) ($row['date_to'] ?? ''));
            $hours    = trim((string) ($row['hours_no'] ?? ''));
            $this->execute(
                'INSERT INTO hris_pds_voluntary_work
                    (pds_id, organization, date_from, date_to, hours_no, nature_of_work, sort_order)
                 VALUES (:pid, :org, :dfrom, :dto, :hours, :nature, :sort)',
                [
                    'pid'    => $pdsId,
                    'org'    => $org,
                    'dfrom'  => ($dateFrom !== '' ? $dateFrom : null),
                    'dto'    => ($dateTo !== '' ? $dateTo : null),
                    'hours'  => ($hours !== '' && is_numeric($hours) ? (int) $hours : null),
                    'nature' => trim((string) ($row['nature_of_work'] ?? '')) ?: null,
                    'sort'   => $i,
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Learning and Development
    // -------------------------------------------------------------------------

    public function getLearningDevelopment(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_learning_development WHERE pds_id = :pid ORDER BY sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteLearningDevelopment(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_learning_development WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertLearningDevelopment(int $pdsId, array $rows): void
    {
        $allowedTypes = ['Managerial','Supervisory','Technical','Foundation'];
        foreach ($rows as $i => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $dateFrom = trim((string) ($row['date_from'] ?? ''));
            $dateTo   = trim((string) ($row['date_to'] ?? ''));
            $hours    = trim((string) ($row['hours_no'] ?? ''));
            $type     = trim((string) ($row['ld_type'] ?? ''));
            $this->execute(
                'INSERT INTO hris_pds_learning_development
                    (pds_id, title, date_from, date_to, hours_no, ld_type, conducted_by, sort_order)
                 VALUES (:pid, :title, :dfrom, :dto, :hours, :ltype, :cby, :sort)',
                [
                    'pid'   => $pdsId,
                    'title' => $title,
                    'dfrom' => ($dateFrom !== '' ? $dateFrom : null),
                    'dto'   => ($dateTo !== '' ? $dateTo : null),
                    'hours' => ($hours !== '' && is_numeric($hours) ? (int) $hours : null),
                    'ltype' => (in_array($type, $allowedTypes, true) ? $type : null),
                    'cby'   => trim((string) ($row['conducted_by'] ?? '')) ?: null,
                    'sort'  => $i,
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Other Information
    // -------------------------------------------------------------------------

    public function getOtherInfo(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_other_info WHERE pds_id = :pid ORDER BY info_type ASC, sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteOtherInfo(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_other_info WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertOtherInfo(int $pdsId, array $rows): void
    {
        $allowed = ['skill','recognition','membership'];
        foreach ($rows as $i => $row) {
            $value = trim((string) ($row['value'] ?? ''));
            $type  = trim((string) ($row['info_type'] ?? ''));
            if ($value === '' || !in_array($type, $allowed, true)) {
                continue;
            }
            $this->execute(
                'INSERT INTO hris_pds_other_info (pds_id, info_type, value, sort_order)
                 VALUES (:pid, :type, :val, :sort)',
                ['pid' => $pdsId, 'type' => $type, 'val' => $value, 'sort' => $i]
            );
        }
    }

    // -------------------------------------------------------------------------
    // References
    // -------------------------------------------------------------------------

    public function getReferences(int $pdsId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_pds_references WHERE pds_id = :pid ORDER BY sort_order ASC',
            ['pid' => $pdsId]
        );
    }

    public function deleteReferences(int $pdsId): void
    {
        $this->execute('DELETE FROM hris_pds_references WHERE pds_id = :pid', ['pid' => $pdsId]);
    }

    public function insertReferences(int $pdsId, array $rows): void
    {
        foreach ($rows as $i => $row) {
            $name = trim((string) ($row['ref_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $this->execute(
                'INSERT INTO hris_pds_references (pds_id, ref_name, ref_address, ref_telephone, sort_order)
                 VALUES (:pid, :name, :addr, :tel, :sort)',
                [
                    'pid'  => $pdsId,
                    'name' => $name,
                    'addr' => trim((string) ($row['ref_address'] ?? '')) ?: null,
                    'tel'  => trim((string) ($row['ref_telephone'] ?? '')) ?: null,
                    'sort' => $i,
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function n(?string $v): ?string
    {
        $v = trim((string) $v);
        return $v !== '' ? $v : null;
    }

    private function mainParams(array $d): array
    {
        return [
            'employee_id'           => (int) ($d['employee_id'] ?? 0),
            'status'                => in_array($d['status'] ?? '', ['Draft','Complete'], true) ? $d['status'] : 'Draft',
            'surname'               => trim((string) ($d['surname'] ?? '')),
            'first_name'            => trim((string) ($d['first_name'] ?? '')),
            'middle_name'           => $this->n($d['middle_name'] ?? null),
            'name_extension'        => $this->n($d['name_extension'] ?? null),
            'birthdate'             => $this->n($d['birthdate'] ?? null),
            'birthplace'            => $this->n($d['birthplace'] ?? null),
            'sex'                   => in_array($d['sex'] ?? '', ['Male','Female'], true) ? $d['sex'] : null,
            'civil_status'          => in_array($d['civil_status'] ?? '', ['Single','Married','Widowed','Separated'], true) ? $d['civil_status'] : null,
            'height'                => is_numeric($d['height'] ?? '') ? (float) $d['height'] : null,
            'weight'                => is_numeric($d['weight'] ?? '') ? (float) $d['weight'] : null,
            'blood_type'            => $this->n($d['blood_type'] ?? null),
            'dual_citizenship'      => isset($d['dual_citizenship']) && $d['dual_citizenship'] ? 1 : 0,
            'citizenship_by'        => in_array($d['citizenship_by'] ?? '', ['birth','naturalization'], true) ? $d['citizenship_by'] : null,
            'citizenship_country'   => $this->n($d['citizenship_country'] ?? null),
            'res_house'             => $this->n($d['res_house'] ?? null),
            'res_street'            => $this->n($d['res_street'] ?? null),
            'res_subdivision'       => $this->n($d['res_subdivision'] ?? null),
            'res_barangay'          => $this->n($d['res_barangay'] ?? null),
            'res_city'              => $this->n($d['res_city'] ?? null),
            'res_province'          => $this->n($d['res_province'] ?? null),
            'res_zip'               => $this->n($d['res_zip'] ?? null),
            'per_house'             => $this->n($d['per_house'] ?? null),
            'per_street'            => $this->n($d['per_street'] ?? null),
            'per_subdivision'       => $this->n($d['per_subdivision'] ?? null),
            'per_barangay'          => $this->n($d['per_barangay'] ?? null),
            'per_city'              => $this->n($d['per_city'] ?? null),
            'per_province'          => $this->n($d['per_province'] ?? null),
            'per_zip'               => $this->n($d['per_zip'] ?? null),
            'telephone'             => $this->n($d['telephone'] ?? null),
            'mobile'                => $this->n($d['mobile'] ?? null),
            'personal_email'        => $this->n($d['personal_email'] ?? null),
            'gsis_id'               => $this->n($d['gsis_id'] ?? null),
            'pagibig_id'            => $this->n($d['pagibig_id'] ?? null),
            'philhealth_id'         => $this->n($d['philhealth_id'] ?? null),
            'sss_no'                => $this->n($d['sss_no'] ?? null),
            'tin_no'                => $this->n($d['tin_no'] ?? null),
            'agency_employee_no'    => $this->n($d['agency_employee_no'] ?? null),
            'spouse_surname'        => $this->n($d['spouse_surname'] ?? null),
            'spouse_firstname'      => $this->n($d['spouse_firstname'] ?? null),
            'spouse_middlename'     => $this->n($d['spouse_middlename'] ?? null),
            'spouse_extension'      => $this->n($d['spouse_extension'] ?? null),
            'spouse_occupation'     => $this->n($d['spouse_occupation'] ?? null),
            'spouse_employer'       => $this->n($d['spouse_employer'] ?? null),
            'spouse_business_address' => $this->n($d['spouse_business_address'] ?? null),
            'spouse_telephone'      => $this->n($d['spouse_telephone'] ?? null),
            'father_surname'        => $this->n($d['father_surname'] ?? null),
            'father_firstname'      => $this->n($d['father_firstname'] ?? null),
            'father_middlename'     => $this->n($d['father_middlename'] ?? null),
            'father_extension'      => $this->n($d['father_extension'] ?? null),
            'mother_surname'        => $this->n($d['mother_surname'] ?? null),
            'mother_firstname'      => $this->n($d['mother_firstname'] ?? null),
            'mother_middlename'     => $this->n($d['mother_middlename'] ?? null),
            'q1_answer'             => in_array($d['q1_answer'] ?? '', ['Yes','No'], true) ? $d['q1_answer'] : null,
            'q1_details'            => $this->n($d['q1_details'] ?? null),
            'q2_answer'             => in_array($d['q2_answer'] ?? '', ['Yes','No'], true) ? $d['q2_answer'] : null,
            'q2_details'            => $this->n($d['q2_details'] ?? null),
            'q3_answer'             => in_array($d['q3_answer'] ?? '', ['Yes','No'], true) ? $d['q3_answer'] : null,
            'q3_details'            => $this->n($d['q3_details'] ?? null),
            'q4_answer'             => in_array($d['q4_answer'] ?? '', ['Yes','No'], true) ? $d['q4_answer'] : null,
            'q4_details'            => $this->n($d['q4_details'] ?? null),
            'q5_answer'             => in_array($d['q5_answer'] ?? '', ['Yes','No'], true) ? $d['q5_answer'] : null,
            'q5_details'            => $this->n($d['q5_details'] ?? null),
            'q6_answer'             => in_array($d['q6_answer'] ?? '', ['Yes','No'], true) ? $d['q6_answer'] : null,
            'q6_details'            => $this->n($d['q6_details'] ?? null),
            'q7_answer'             => in_array($d['q7_answer'] ?? '', ['Yes','No'], true) ? $d['q7_answer'] : null,
            'q7_details'            => $this->n($d['q7_details'] ?? null),
            'q8_answer'             => in_array($d['q8_answer'] ?? '', ['Yes','No'], true) ? $d['q8_answer'] : null,
            'q8_details'            => $this->n($d['q8_details'] ?? null),
        ];
    }
}

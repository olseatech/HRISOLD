<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Pds;

final class PdsController extends Controller
{
    private const PER_PAGE = 15;

    private Pds $pds;

    public function __construct()
    {
        $this->pds = new Pds();
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $page  = max(1, (int) ($_GET['page'] ?? 1));

        $total      = $this->pds->countSearch($query);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('pds/index', [
            'title'      => 'Personal Data Sheet',
            'records'    => $this->pds->search($query, $page, self::PER_PAGE),
            'query'      => $query,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
            'success'    => Session::pullFlash('success'),
            'error'      => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $this->view('pds/create', [
            'title'     => 'Create PDS',
            'csrf'      => CSRF::token(),
            'employees' => $this->pds->employeeOptions(),
            'errors'    => Session::pullFlash('errors', []),
            'old'       => Session::pullFlash('old', []),
        ]);
    }

    public function store(): void
    {
        $data     = $this->payload();
        $children = $this->payloadChildren();
        $errors   = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors and try again.');
            $this->redirect('/pds/create');
        }

        try {
            $id      = $this->pds->create($data);
            $created = $this->pds->find($id);
            $this->saveChildren($id, $children);
            Audit::log('pds', 'CREATE', $id, null, $created);

            Session::flash('success', 'PDS created successfully.');
            $this->redirect('/pds/' . $id);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to save PDS. Please try again.');
            $this->redirect('/pds/create');
        }
    }

    // -------------------------------------------------------------------------
    // Show (read-only)
    // -------------------------------------------------------------------------

    public function show(string $id): void
    {
        $pds = $this->pds->find((int) $id);

        if (!$pds) {
            http_response_code(404);
            echo 'PDS record not found.';
            return;
        }

        $this->view('pds/show', [
            'title'    => 'Personal Data Sheet',
            'pds'      => $pds,
            'sections' => $this->loadAllSections((int) $id),
            'success'  => Session::pullFlash('success'),
            'error'    => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function edit(string $id): void
    {
        $pds = $this->pds->find((int) $id);

        if (!$pds) {
            http_response_code(404);
            echo 'PDS record not found.';
            return;
        }

        $this->view('pds/edit', [
            'title'     => 'Edit PDS',
            'pds'       => $pds,
            'sections'  => $this->loadAllSections((int) $id),
            'csrf'      => CSRF::token(),
            'employees' => $this->pds->employeeOptions(),
            'errors'    => Session::pullFlash('errors', []),
            'old'       => Session::pullFlash('old', []),
        ]);
    }

    public function update(string $id): void
    {
        $pdsId  = (int) $id;
        $before = $this->pds->find($pdsId);

        if (!$before) {
            http_response_code(404);
            echo 'PDS record not found.';
            return;
        }

        $data     = $this->payload();
        $children = $this->payloadChildren();
        $errors   = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors and try again.');
            $this->redirect('/pds/' . $pdsId . '/edit');
        }

        try {
            $this->pds->updateById($pdsId, $data);
            $this->deleteChildren($pdsId);
            $this->saveChildren($pdsId, $children);
            $after = $this->pds->find($pdsId);
            Audit::log('pds', 'UPDATE', $pdsId, $before, $after);

            Session::flash('success', 'PDS updated successfully.');
            $this->redirect('/pds/' . $pdsId);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to update PDS. Please try again.');
            $this->redirect('/pds/' . $pdsId . '/edit');
        }
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function destroy(string $id): void
    {
        $pdsId  = (int) $id;
        $before = $this->pds->find($pdsId);

        if (!$before) {
            Session::flash('error', 'PDS record not found.');
            $this->redirect('/pds');
        }

        try {
            $this->pds->deleteById($pdsId);
            Audit::log('pds', 'DELETE', $pdsId, $before, null);
            Session::flash('success', 'PDS deleted successfully.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to delete PDS. Related records may exist.');
        }

        $this->redirect('/pds');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function payload(): array
    {
        $p = static function (string $key): string {
            return trim((string) ($_POST[$key] ?? ''));
        };
        return [
            'employee_id'             => $p('employee_id'),
            'status'                  => $p('status'),
            'surname'                 => $p('surname'),
            'first_name'              => $p('first_name'),
            'middle_name'             => $p('middle_name'),
            'name_extension'          => $p('name_extension'),
            'birthdate'               => $p('birthdate'),
            'birthplace'              => $p('birthplace'),
            'sex'                     => $p('sex'),
            'civil_status'            => $p('civil_status'),
            'height'                  => $p('height'),
            'weight'                  => $p('weight'),
            'blood_type'              => $p('blood_type'),
            'dual_citizenship'        => $p('dual_citizenship'),
            'citizenship_by'          => $p('citizenship_by'),
            'citizenship_country'     => $p('citizenship_country'),
            'res_house'               => $p('res_house'),
            'res_street'              => $p('res_street'),
            'res_subdivision'         => $p('res_subdivision'),
            'res_barangay'            => $p('res_barangay'),
            'res_city'                => $p('res_city'),
            'res_province'            => $p('res_province'),
            'res_zip'                 => $p('res_zip'),
            'per_house'               => $p('per_house'),
            'per_street'              => $p('per_street'),
            'per_subdivision'         => $p('per_subdivision'),
            'per_barangay'            => $p('per_barangay'),
            'per_city'                => $p('per_city'),
            'per_province'            => $p('per_province'),
            'per_zip'                 => $p('per_zip'),
            'telephone'               => $p('telephone'),
            'mobile'                  => $p('mobile'),
            'personal_email'          => $p('personal_email'),
            'gsis_id'                 => $p('gsis_id'),
            'pagibig_id'              => $p('pagibig_id'),
            'philhealth_id'           => $p('philhealth_id'),
            'sss_no'                  => $p('sss_no'),
            'tin_no'                  => $p('tin_no'),
            'agency_employee_no'      => $p('agency_employee_no'),
            'spouse_surname'          => $p('spouse_surname'),
            'spouse_firstname'        => $p('spouse_firstname'),
            'spouse_middlename'       => $p('spouse_middlename'),
            'spouse_extension'        => $p('spouse_extension'),
            'spouse_occupation'       => $p('spouse_occupation'),
            'spouse_employer'         => $p('spouse_employer'),
            'spouse_business_address' => $p('spouse_business_address'),
            'spouse_telephone'        => $p('spouse_telephone'),
            'father_surname'          => $p('father_surname'),
            'father_firstname'        => $p('father_firstname'),
            'father_middlename'       => $p('father_middlename'),
            'father_extension'        => $p('father_extension'),
            'mother_surname'          => $p('mother_surname'),
            'mother_firstname'        => $p('mother_firstname'),
            'mother_middlename'       => $p('mother_middlename'),
            'q1_answer'               => $p('q1_answer'),
            'q1_details'              => $p('q1_details'),
            'q2_answer'               => $p('q2_answer'),
            'q2_details'              => $p('q2_details'),
            'q3_answer'               => $p('q3_answer'),
            'q3_details'              => $p('q3_details'),
            'q4_answer'               => $p('q4_answer'),
            'q4_details'              => $p('q4_details'),
            'q5_answer'               => $p('q5_answer'),
            'q5_details'              => $p('q5_details'),
            'q6_answer'               => $p('q6_answer'),
            'q6_details'              => $p('q6_details'),
            'q7_answer'               => $p('q7_answer'),
            'q7_details'              => $p('q7_details'),
            'q8_answer'               => $p('q8_answer'),
            'q8_details'              => $p('q8_details'),
        ];
    }

    private function payloadChildren(): array
    {
        $a = static function (string $key): array {
            $val = $_POST[$key] ?? [];
            return is_array($val) ? $val : [];
        };
        return [
            'children'  => $a('children'),
            'education' => $a('education'),
            'civil_service' => $a('civil_service'),
            'work_experience' => $a('work_experience'),
            'voluntary_work' => $a('voluntary_work'),
            'learning_development' => $a('learning_development'),
            'other_info' => $a('other_info'),
            'references' => $a('references'),
        ];
    }

    private function validate(array $data): array
    {
        $errors = array_merge(
            Validator::required($data, ['employee_id', 'surname', 'first_name', 'birthdate', 'sex', 'civil_status']),
            Validator::validDate($data, 'birthdate'),
            Validator::inSet($data, 'sex', ['Male', 'Female']),
            Validator::inSet($data, 'civil_status', ['Single', 'Married', 'Widowed', 'Separated']),
            Validator::email($data, 'personal_email')
        );
        return $errors;
    }

    private function loadAllSections(int $pdsId): array
    {
        return [
            'children'             => $this->pds->getChildren($pdsId),
            'education'            => $this->pds->getEducation($pdsId),
            'civil_service'        => $this->pds->getCivilService($pdsId),
            'work_experience'      => $this->pds->getWorkExperience($pdsId),
            'voluntary_work'       => $this->pds->getVoluntaryWork($pdsId),
            'learning_development' => $this->pds->getLearningDevelopment($pdsId),
            'other_info'           => $this->pds->getOtherInfo($pdsId),
            'references'           => $this->pds->getReferences($pdsId),
        ];
    }

    private function saveChildren(int $pdsId, array $children): void
    {
        $this->pds->insertChildren($pdsId, $children['children'] ?? []);
        $this->pds->insertEducation($pdsId, $children['education'] ?? []);
        $this->pds->insertCivilService($pdsId, $children['civil_service'] ?? []);
        $this->pds->insertWorkExperience($pdsId, $children['work_experience'] ?? []);
        $this->pds->insertVoluntaryWork($pdsId, $children['voluntary_work'] ?? []);
        $this->pds->insertLearningDevelopment($pdsId, $children['learning_development'] ?? []);
        $this->pds->insertOtherInfo($pdsId, $children['other_info'] ?? []);
        $this->pds->insertReferences($pdsId, $children['references'] ?? []);
    }

    private function deleteChildren(int $pdsId): void
    {
        $this->pds->deleteChildren($pdsId);
        $this->pds->deleteEducation($pdsId);
        $this->pds->deleteCivilService($pdsId);
        $this->pds->deleteWorkExperience($pdsId);
        $this->pds->deleteVoluntaryWork($pdsId);
        $this->pds->deleteLearningDevelopment($pdsId);
        $this->pds->deleteOtherInfo($pdsId);
        $this->pds->deleteReferences($pdsId);
    }
}

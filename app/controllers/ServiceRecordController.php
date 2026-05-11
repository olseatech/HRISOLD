<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ServiceRecord;

final class ServiceRecordController extends Controller
{
    private const PER_PAGE = 15;

    private ServiceRecord $records;

    public function __construct()
    {
        $this->records = new ServiceRecord();
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $query  = trim((string) ($_GET['q'] ?? ''));
        $empId  = trim((string) ($_GET['employee_id'] ?? ''));
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        $total      = $this->records->countSearch($query, $empId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('service-records/index', [
            'title'        => 'Service Records',
            'rows'         => $this->records->search($query, $empId, $page, self::PER_PAGE),
            'query'        => $query,
            'empId'        => $empId,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
            'totalCurrent' => $this->records->countCurrentAppointments(),
            'totalEmp'     => $this->records->countEmployeesWithRecords(),
            'employees'    => $this->records->employeeOptions(),
            'success'      => Session::pullFlash('success'),
            'error'        => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $this->view('service-records/create', [
            'title'     => 'Add Service Record',
            'csrf'      => CSRF::token(),
            'employees' => $this->records->employeeOptions(),
            'statuses'  => $this->records->appointmentStatuses(),
            'natures'   => $this->records->appointmentNatures(),
            'sepTypes'  => $this->records->separationTypes(),
            'errors'    => Session::pullFlash('errors', []),
            'old'       => Session::pullFlash('old', []),
            'preEmpId'  => trim((string) ($_GET['employee_id'] ?? '')),
        ]);
    }

    public function store(): void
    {
        $data   = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors and try again.');
            $this->redirect('/service-records/create');
        }

        try {
            $id      = $this->records->create($data);
            $created = $this->records->find($id);
            Audit::log('service_records', 'CREATE', $id, null, $created);

            Session::flash('success', 'Service record created successfully.');
            $this->redirect('/service-records/' . $id);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to save the service record. Please try again.');
            $this->redirect('/service-records/create');
        }
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(string $id): void
    {
        $record = $this->records->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Service record not found.';
            return;
        }

        $this->view('service-records/show', [
            'title'   => 'Service Record',
            'record'  => $record,
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function edit(string $id): void
    {
        $record = $this->records->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Service record not found.';
            return;
        }

        $this->view('service-records/edit', [
            'title'     => 'Edit Service Record',
            'record'    => $record,
            'csrf'      => CSRF::token(),
            'employees' => $this->records->employeeOptions(),
            'statuses'  => $this->records->appointmentStatuses(),
            'natures'   => $this->records->appointmentNatures(),
            'sepTypes'  => $this->records->separationTypes(),
            'errors'    => Session::pullFlash('errors', []),
            'old'       => Session::pullFlash('old', []),
        ]);
    }

    public function update(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->records->find($recId);

        if (!$before) {
            http_response_code(404);
            echo 'Service record not found.';
            return;
        }

        $data   = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors and try again.');
            $this->redirect('/service-records/' . $recId . '/edit');
        }

        try {
            $this->records->updateById($recId, $data);
            $after = $this->records->find($recId);
            Audit::log('service_records', 'UPDATE', $recId, $before, $after);

            Session::flash('success', 'Service record updated successfully.');
            $this->redirect('/service-records/' . $recId);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to update the service record. Please try again.');
            $this->redirect('/service-records/' . $recId . '/edit');
        }
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function destroy(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->records->find($recId);

        if (!$before) {
            Session::flash('error', 'Service record not found.');
            $this->redirect('/service-records');
        }

        try {
            $this->records->deleteById($recId);
            Audit::log('service_records', 'DELETE', $recId, $before, null);
            Session::flash('success', 'Service record deleted successfully.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to delete this service record.');
        }

        $this->redirect('/service-records');
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
            'employee_id'        => $p('employee_id'),
            'position_title'     => $p('position_title'),
            'item_number'        => $p('item_number'),
            'salary_grade'       => $p('salary_grade'),
            'salary_step'        => $p('salary_step'),
            'monthly_salary'     => $p('monthly_salary'),
            'appointment_status' => $p('appointment_status'),
            'appointment_nature' => $p('appointment_nature'),
            'office_unit'        => $p('office_unit'),
            'division'           => $p('division'),
            'date_from'          => $p('date_from'),
            'date_to'            => $p('date_to'),
            'is_current'         => $p('is_current'),
            'separation_type'    => $p('separation_type'),
            'separation_date'    => $p('separation_date'),
            'remarks'            => $p('remarks'),
        ];
    }

    private function validate(array $data): array
    {
        $errors = array_merge(
            Validator::required($data, ['employee_id', 'position_title', 'date_from']),
            Validator::validDate($data, 'date_from'),
            Validator::validDate($data, 'date_to'),
            Validator::validDate($data, 'separation_date'),
            Validator::inSet($data, 'appointment_status', array_merge([''], $this->records->appointmentStatuses())),
            Validator::inSet($data, 'appointment_nature', array_merge([''], $this->records->appointmentNatures())),
            Validator::inSet($data, 'separation_type',    array_merge([''], $this->records->separationTypes()))
        );

        // Date order: date_from must be before date_to
        if (($data['date_to'] ?? '') !== '') {
            $errors = array_merge($errors, Validator::dateOrder($data, 'date_from', 'date_to'));
        }

        // Separation date must be on or after date_from
        if (($data['separation_date'] ?? '') !== '' && ($data['date_from'] ?? '') !== '') {
            $errors = array_merge($errors, Validator::dateOrder($data, 'date_from', 'separation_date'));
        }

        return $errors;
    }
}

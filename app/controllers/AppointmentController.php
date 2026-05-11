<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Appointment;

final class AppointmentController extends Controller
{
    private const PER_PAGE = 15;

    private Appointment $appointments;

    public function __construct()
    {
        $this->appointments = new Appointment();
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $query  = trim((string) ($_GET['q'] ?? ''));
        $empId  = trim((string) ($_GET['employee_id'] ?? ''));
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        $total      = $this->appointments->countSearch($query, $empId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('appointments/index', [
            'title'        => 'Appointments',
            'rows'         => $this->appointments->search($query, $empId, $page, self::PER_PAGE),
            'query'        => $query,
            'empId'        => $empId,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
            'totalCurrent' => $this->appointments->countCurrent(),
            'totalEmp'     => $this->appointments->countEmployeesWithAppointments(),
            'employees'    => $this->appointments->employeeOptions(),
            'success'      => Session::pullFlash('success'),
            'error'        => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $this->view('appointments/create', [
            'title'     => 'Add Appointment',
            'csrf'      => CSRF::token(),
            'employees' => $this->appointments->employeeOptions(),
            'types'     => $this->appointments->appointmentTypes(),
            'statuses'  => $this->appointments->employmentStatuses(),
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
            $this->redirect('/appointments/create');
        }

        try {
            $id      = $this->appointments->create($data);
            $created = $this->appointments->find($id);
            Audit::log('appointments', 'CREATE', $id, null, $created);

            Session::flash('success', 'Appointment record created successfully.');
            $this->redirect('/appointments/' . $id);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to save the appointment. Please try again.');
            $this->redirect('/appointments/create');
        }
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(string $id): void
    {
        $record = $this->appointments->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        $this->view('appointments/show', [
            'title'   => 'Appointment',
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
        $record = $this->appointments->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        $this->view('appointments/edit', [
            'title'     => 'Edit Appointment',
            'record'    => $record,
            'csrf'      => CSRF::token(),
            'employees' => $this->appointments->employeeOptions(),
            'types'     => $this->appointments->appointmentTypes(),
            'statuses'  => $this->appointments->employmentStatuses(),
            'errors'    => Session::pullFlash('errors', []),
            'old'       => Session::pullFlash('old', []),
        ]);
    }

    public function update(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->appointments->find($recId);

        if (!$before) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        $data   = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors and try again.');
            $this->redirect('/appointments/' . $recId . '/edit');
        }

        try {
            $this->appointments->updateById($recId, $data);
            $after = $this->appointments->find($recId);
            Audit::log('appointments', 'UPDATE', $recId, $before, $after);

            Session::flash('success', 'Appointment updated successfully.');
            $this->redirect('/appointments/' . $recId);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to update the appointment. Please try again.');
            $this->redirect('/appointments/' . $recId . '/edit');
        }
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function destroy(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->appointments->find($recId);

        if (!$before) {
            Session::flash('error', 'Appointment not found.');
            $this->redirect('/appointments');
        }

        try {
            $this->appointments->deleteById($recId);
            Audit::log('appointments', 'DELETE', $recId, $before, null);
            Session::flash('success', 'Appointment deleted successfully.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to delete this appointment.');
        }

        $this->redirect('/appointments');
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
            'employee_id'      => $p('employee_id'),
            'appointment_type' => $p('appointment_type'),
            'position_title'   => $p('position_title'),
            'item_number'      => $p('item_number'),
            'salary_grade'     => $p('salary_grade'),
            'salary_step'      => $p('salary_step'),
            'monthly_salary'   => $p('monthly_salary'),
            'employment_status'=> $p('employment_status'),
            'office_unit'      => $p('office_unit'),
            'division'         => $p('division'),
            'effectivity_date' => $p('effectivity_date'),
            'oath_date'        => $p('oath_date'),
            'report_date'      => $p('report_date'),
            'is_current'       => $p('is_current'),
            'remarks'          => $p('remarks'),
        ];
    }

    private function validate(array $data): array
    {
        return array_merge(
            Validator::required($data, ['employee_id', 'appointment_type', 'position_title', 'effectivity_date']),
            Validator::validDate($data, 'effectivity_date'),
            Validator::validDate($data, 'oath_date'),
            Validator::validDate($data, 'report_date'),
            Validator::inSet($data, 'appointment_type', $this->appointments->appointmentTypes()),
            Validator::inSet($data, 'employment_status', array_merge([''], $this->appointments->employmentStatuses()))
        );
    }
}

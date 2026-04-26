<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Attendance;
use App\Models\Employee;

final class AttendanceController extends Controller
{
    private const PER_PAGE = 10;

    private Attendance $attendance;
    private Employee $employees;

    public function __construct()
    {
        $this->attendance = new Attendance();
        $this->employees = new Employee();
    }

    public function index(): void
    {
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $query = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $employeeScopeId = $this->employeeScopeId();
        $scopeFilterId = $employeeScopeId;
        $employeeOptions = [];

        if ($employeeScopeId !== null) {
            $query = '';

            if ($employeeScopeId <= 0) {
                $scopeFilterId = -1;
            }
        } else {
            $employeeOptions = $this->employees->listSimple();
        }

        $total = $this->attendance->countFiltered($date, $query, $status, $scopeFilterId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('attendance/index', [
            'title' => 'Attendance',
            'csrf' => CSRF::token(),
            'date' => $date,
            'query' => $query,
            'status' => $status,
            'statusOptions' => $this->attendance->statuses(),
            'employees' => $employeeOptions,
            'records' => $this->attendance->listFiltered($date, $query, $status, $page, self::PER_PAGE, $scopeFilterId),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
            'errors' => Session::pullFlash('errors', []),
            'old' => Session::pullFlash('old', []),
        ]);
    }

    public function store(): void
    {
        $data = [
            'employee_id' => trim((string) ($_POST['employee_id'] ?? '')),
            'date' => trim((string) ($_POST['date'] ?? date('Y-m-d'))),
            'clock_in' => $this->normalizeDateTimeLocal(trim((string) ($_POST['clock_in'] ?? ''))),
            'clock_out' => $this->normalizeDateTimeLocal(trim((string) ($_POST['clock_out'] ?? ''))),
            'hours_worked' => trim((string) ($_POST['hours_worked'] ?? '')),
            'overtime_hrs' => trim((string) ($_POST['overtime_hrs'] ?? '0')),
            'status' => trim((string) ($_POST['status'] ?? 'Present')),
            'remarks' => trim((string) ($_POST['remarks'] ?? '')),
        ];

        $errors = Validator::required($data, ['employee_id', 'date', 'status']);
        $errors = array_merge($errors, Validator::validDate($data, 'date'));
        $errors = array_merge($errors, Validator::inSet($data, 'status', $this->attendance->statuses()));
        $errors = array_merge($errors, Validator::numericMin($data, 'hours_worked', 0));
        $errors = array_merge($errors, Validator::numericMin($data, 'overtime_hrs', 0));

        if ($data['employee_id'] !== '' && !ctype_digit($data['employee_id'])) {
            $errors['employee_id'] = 'Employee value is invalid.';
        }

        if (!Validator::datetimeOrder($data['clock_in'], $data['clock_out'])) {
            $errors['clock_out'] = 'Clock-out time must be after clock-in time.';
        }

        if ($data['hours_worked'] === '' && $data['clock_in'] !== '' && $data['clock_out'] !== '' && Validator::datetimeOrder($data['clock_in'], $data['clock_out'])) {
            $start = new \DateTimeImmutable($data['clock_in']);
            $end = new \DateTimeImmutable($data['clock_out']);
            $seconds = max(0, $end->getTimestamp() - $start->getTimestamp());
            $data['hours_worked'] = number_format($seconds / 3600, 2, '.', '');
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix attendance form errors.');
            $this->redirect('/attendance?date=' . urlencode($data['date']));
        }

        $oldRecord = $this->attendance->findByEmployeeDate((int) $data['employee_id'], $data['date']);
        $this->attendance->record($data);

        $newRecord = $this->attendance->findByEmployeeDate((int) $data['employee_id'], $data['date']);
        Audit::log('attendance', 'UPSERT', isset($newRecord['id']) ? (int) $newRecord['id'] : null, $oldRecord, $newRecord ?? $data);

        Session::flash('success', 'Attendance saved successfully.');
        $this->redirect('/attendance?date=' . urlencode($data['date']));
    }

    private function normalizeDateTimeLocal(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $normalized = str_replace('T', ' ', $value);
        if (strlen($normalized) === 16) {
            $normalized .= ':00';
        }

        return $normalized;
    }

    private function employeeScopeId(): ?int
    {
        $user = Auth::user();
        if (!is_employee_role($user)) {
            return null;
        }

        return isset($user['employee_id']) ? (int) $user['employee_id'] : 0;
    }
}

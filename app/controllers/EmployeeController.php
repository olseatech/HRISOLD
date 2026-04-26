<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Employee;

final class EmployeeController extends Controller
{
    private const PER_PAGE = 10;

    private Employee $employees;

    public function __construct()
    {
        $this->employees = new Employee();
    }

    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $total = $this->employees->countSearch($query, $status);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('employees/index', [
            'title' => 'Employees',
            'employees' => $this->employees->search($query, $status, $page, self::PER_PAGE),
            'query' => $query,
            'status' => $status,
            'statusOptions' => $this->employees->employmentStatuses(),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);
    }

    public function create(): void
    {
        $this->view('employees/create', [
            'title' => 'Create Employee',
            'csrf' => CSRF::token(),
            'departments' => $this->employees->departmentOptions(),
            'designations' => $this->employees->designationOptions(),
            'supervisors' => $this->employees->listSimple(),
            'errors' => Session::pullFlash('errors', []),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);
    }

    public function store(): void
    {
        $data = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the form errors and try again.');
            $this->redirect('/employees/create');
        }

        try {
            $id = $this->employees->create($data);
            $created = $this->employees->find($id);
            Audit::log('employees', 'CREATE', $id, null, $created);

            Session::flash('success', 'Employee created successfully.');
            $this->redirect('/employees');
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to create employee record.');
            $this->redirect('/employees/create');
        }
    }

    public function show(string $id): void
    {
        $employee = $this->employees->find((int) $id);

        if (!$employee) {
            http_response_code(404);
            echo 'Employee not found';
            return;
        }

        $this->view('employees/show', [
            'title' => 'Employee Profile',
            'employee' => $employee,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);
    }

    public function edit(string $id): void
    {
        $employee = $this->employees->find((int) $id);

        if (!$employee) {
            http_response_code(404);
            echo 'Employee not found';
            return;
        }

        $this->view('employees/edit', [
            'title' => 'Edit Employee',
            'employee' => $employee,
            'csrf' => CSRF::token(),
            'departments' => $this->employees->departmentOptions(),
            'designations' => $this->employees->designationOptions(),
            'supervisors' => $this->employees->listSimple(),
            'errors' => Session::pullFlash('errors', []),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);
    }

    public function update(string $id): void
    {
        $employeeId = (int) $id;
        $before = $this->employees->find($employeeId);

        if (!$before) {
            http_response_code(404);
            echo 'Employee not found';
            return;
        }

        $data = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the form errors and try again.');
            $this->redirect('/employees/' . $employeeId . '/edit');
        }

        try {
            $this->employees->updateById($employeeId, $data);
            $after = $this->employees->find($employeeId);
            Audit::log('employees', 'UPDATE', $employeeId, $before, $after);

            Session::flash('success', 'Employee updated successfully.');
            $this->redirect('/employees/' . $employeeId);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to update employee record.');
            $this->redirect('/employees/' . $employeeId . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        $employeeId = (int) $id;
        $before = $this->employees->find($employeeId);

        if (!$before) {
            Session::flash('error', 'Employee not found.');
            $this->redirect('/employees');
        }

        try {
            $this->employees->deleteById($employeeId);
            Audit::log('employees', 'DELETE', $employeeId, $before, null);

            Session::flash('success', 'Employee deleted successfully.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to delete employee. Related records may exist.');
        }

        $this->redirect('/employees');
    }

    private function payload(): array
    {
        return [
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'middle_name' => trim((string) ($_POST['middle_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'gender' => trim((string) ($_POST['gender'] ?? '')),
            'date_of_birth' => trim((string) ($_POST['date_of_birth'] ?? '')),
            'marital_status' => trim((string) ($_POST['marital_status'] ?? 'Single')),
            'nationality' => trim((string) ($_POST['nationality'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'department_id' => trim((string) ($_POST['department_id'] ?? '')),
            'designation_id' => trim((string) ($_POST['designation_id'] ?? '')),
            'employment_type' => trim((string) ($_POST['employment_type'] ?? 'Full-Time')),
            'employment_status' => trim((string) ($_POST['employment_status'] ?? 'Active')),
            'date_hired' => trim((string) ($_POST['date_hired'] ?? '')),
            'date_regularized' => trim((string) ($_POST['date_regularized'] ?? '')),
            'date_separated' => trim((string) ($_POST['date_separated'] ?? '')),
            'supervisor_id' => trim((string) ($_POST['supervisor_id'] ?? '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = array_merge(Validator::required($data, [
            'first_name',
            'last_name',
            'gender',
            'date_of_birth',
            'department_id',
            'designation_id',
            'date_hired',
        ]), Validator::inSet($data, 'gender', ['Male', 'Female', 'Other']));

        $errors = array_merge($errors, Validator::inSet($data, 'marital_status', ['Single', 'Married', 'Divorced', 'Widowed']));
        $errors = array_merge($errors, Validator::inSet($data, 'employment_type', ['Full-Time', 'Part-Time', 'Contract', 'Intern']));
        $errors = array_merge($errors, Validator::inSet($data, 'employment_status', $this->employees->employmentStatuses()));

        $errors = array_merge($errors, Validator::validDate($data, 'date_of_birth'));
        $errors = array_merge($errors, Validator::validDate($data, 'date_hired'));
        $errors = array_merge($errors, Validator::validDate($data, 'date_regularized'));
        $errors = array_merge($errors, Validator::validDate($data, 'date_separated'));

        $errors = array_merge($errors, Validator::dateOrder($data, 'date_hired', 'date_regularized'));
        $errors = array_merge($errors, Validator::dateOrder($data, 'date_hired', 'date_separated'));

        $errors = array_merge($errors, Validator::email($data, 'email'));

        // ── Date of birth cannot be in the future ─────────────────
        if (!isset($errors['date_of_birth']) && $data['date_of_birth'] !== '') {
            if (strtotime($data['date_of_birth']) > time()) {
                $errors['date_of_birth'] = 'Date of birth cannot be in the future.';
            }
        }

        // ── Input length limits ───────────────────────────────────
        $maxLengths = [
            'first_name'  => 100,
            'middle_name' => 100,
            'last_name'   => 100,
            'phone'       => 30,
            'email'       => 150,
            'address'     => 500,
            'nationality' => 100,
        ];

        foreach ($maxLengths as $field => $max) {
            if (!isset($errors[$field]) && isset($data[$field]) && mb_strlen($data[$field]) > $max) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.";
            }
        }

        if ($data['supervisor_id'] !== '' && !ctype_digit($data['supervisor_id'])) {
            $errors['supervisor_id'] = 'Supervisor value is invalid.';
        }

        if ($data['department_id'] !== '' && !ctype_digit($data['department_id'])) {
            $errors['department_id'] = 'Department value is invalid.';
        }

        if ($data['designation_id'] !== '' && !ctype_digit($data['designation_id'])) {
            $errors['designation_id'] = 'Designation value is invalid.';
        }

        return $errors;
    }
}

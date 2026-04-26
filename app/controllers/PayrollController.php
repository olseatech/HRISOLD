<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Payroll;

final class PayrollController extends Controller
{
    private const PER_PAGE = 10;

    private Payroll $payroll;

    public function __construct()
    {
        $this->payroll = new Payroll();
    }

    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $total = $this->payroll->countEmployeeSalaries($query);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('payroll/index', [
            'title' => 'Payroll',
            'csrf' => CSRF::token(),
            'grades' => $this->payroll->salaryGrades(),
            'employees' => $this->payroll->listEmployeesSimple(),
            'salaries' => $this->payroll->listEmployeeSalaries($query, $page, self::PER_PAGE),
            'query' => $query,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
            'errors' => Session::pullFlash('errors', []),
            'old' => Session::pullFlash('old', []),
        ]);
    }

    public function storeGrade(): void
    {
        $data = [
            'grade_name' => trim((string) ($_POST['grade_name'] ?? '')),
            'min_salary' => trim((string) ($_POST['min_salary'] ?? '')),
            'max_salary' => trim((string) ($_POST['max_salary'] ?? '')),
        ];

        $errors = Validator::required($data, ['grade_name', 'min_salary', 'max_salary']);
        $errors = array_merge($errors, Validator::numericMin($data, 'min_salary', 0));
        $errors = array_merge($errors, Validator::numericMin($data, 'max_salary', 0));

        if ($data['min_salary'] !== '' && $data['max_salary'] !== '' && is_numeric($data['min_salary']) && is_numeric($data['max_salary'])) {
            if ((float) $data['max_salary'] < (float) $data['min_salary']) {
                $errors['max_salary'] = 'Maximum salary must be greater than or equal to minimum salary.';
            }
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix salary grade errors.');
            $this->redirect('/payroll');
        }

        $this->payroll->upsertGrade($data['grade_name'], (float) $data['min_salary'], (float) $data['max_salary']);
        Audit::log('payroll', 'UPSERT', null, null, $data);

        Session::flash('success', 'Salary grade saved successfully.');
        $this->redirect('/payroll');
    }

    public function storeSalary(): void
    {
        $data = [
            'employee_id' => trim((string) ($_POST['employee_id'] ?? '')),
            'salary_grade_id' => trim((string) ($_POST['salary_grade_id'] ?? '')),
            'basic_salary' => trim((string) ($_POST['basic_salary'] ?? '')),
            'effective_date' => trim((string) ($_POST['effective_date'] ?? '')),
            'is_current' => (string) ($_POST['is_current'] ?? '1'),
        ];

        $errors = Validator::required($data, ['employee_id', 'basic_salary', 'effective_date']);
        $errors = array_merge($errors, Validator::numericMin($data, 'basic_salary', 0));
        $errors = array_merge($errors, Validator::validDate($data, 'effective_date'));

        if ($data['employee_id'] !== '' && !ctype_digit($data['employee_id'])) {
            $errors['employee_id'] = 'Employee value is invalid.';
        }

        if ($data['salary_grade_id'] !== '' && !ctype_digit($data['salary_grade_id'])) {
            $errors['salary_grade_id'] = 'Salary grade value is invalid.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix salary assignment errors.');
            $this->redirect('/payroll');
        }

        $salaryId = $this->payroll->createEmployeeSalary(
            (int) $data['employee_id'],
            $data['salary_grade_id'] !== '' ? (int) $data['salary_grade_id'] : null,
            (float) $data['basic_salary'],
            $data['effective_date'],
            $data['is_current'] === '1'
        );

        Audit::log('payroll', 'CREATE', $salaryId, null, $this->payroll->findSalaryById($salaryId));

        Session::flash('success', 'Employee salary saved successfully.');
        $this->redirect('/payroll');
    }
}

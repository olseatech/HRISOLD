<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Holiday;

final class HolidayController extends Controller
{
    private Holiday $holidays;

    public function __construct()
    {
        $this->holidays = new Holiday();
    }

    public function index(): void
    {
        $year = (int) ($_GET['year'] ?? (int) date('Y'));
        if ($year <= 0) {
            $year = (int) date('Y');
        }

        $this->view('holidays/index', [
            'title'         => 'Holidays',
            'rows'          => $this->holidays->all($year),
            'year'          => $year,
            'countYear'     => $this->holidays->countByYear($year),
            'countRecurring'=> $this->holidays->countRecurring(),
            'success'       => Session::pullFlash('success'),
            'error'         => Session::pullFlash('error'),
        ]);
    }

    public function create(): void
    {
        $this->view('holidays/create', [
            'title'  => 'Add Holiday',
            'csrf'   => CSRF::token(),
            'types'  => Holiday::TYPES,
            'errors' => Session::pullFlash('errors', []),
            'old'    => Session::pullFlash('old', []),
        ]);
    }

    public function store(): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Invalid session token. Please try again.');
            $this->redirect('/holidays/create');
        }

        $data   = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors below.');
            $this->redirect('/holidays/create');
        }

        $id      = $this->holidays->create($data);
        $created = $this->holidays->find($id);
        Audit::log('holidays', 'CREATE', $id, null, $created);

        Session::flash('success', 'Holiday added successfully.');
        $this->redirect('/holidays');
    }

    public function edit(string $id): void
    {
        $record = $this->holidays->find((int) $id);

        if (!$record) {
            Session::flash('error', 'Holiday not found.');
            $this->redirect('/holidays');
        }

        $this->view('holidays/edit', [
            'title'  => 'Edit Holiday',
            'csrf'   => CSRF::token(),
            'record' => $record,
            'types'  => Holiday::TYPES,
            'errors' => Session::pullFlash('errors', []),
            'old'    => Session::pullFlash('old', []),
        ]);
    }

    public function update(string $id): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Invalid session token. Please try again.');
            $this->redirect('/holidays/' . $id . '/edit');
        }

        $recId  = (int) $id;
        $before = $this->holidays->find($recId);

        if (!$before) {
            Session::flash('error', 'Holiday not found.');
            $this->redirect('/holidays');
        }

        $data   = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors below.');
            $this->redirect('/holidays/' . $recId . '/edit');
        }

        $this->holidays->updateById($recId, $data);
        $after = $this->holidays->find($recId);
        Audit::log('holidays', 'UPDATE', $recId, $before, $after);

        Session::flash('success', 'Holiday updated.');
        $this->redirect('/holidays');
    }

    public function destroy(string $id): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Invalid session token. Please try again.');
            $this->redirect('/holidays');
        }

        $recId  = (int) $id;
        $before = $this->holidays->find($recId);

        if (!$before) {
            Session::flash('error', 'Holiday not found.');
            $this->redirect('/holidays');
        }

        $this->holidays->deleteById($recId);
        Audit::log('holidays', 'DELETE', $recId, $before, null);

        Session::flash('success', 'Holiday deleted.');
        $this->redirect('/holidays');
    }

    private function payload(): array
    {
        $p = static function (string $key): string {
            return trim((string) ($_POST[$key] ?? ''));
        };
        return [
            'name'         => $p('name'),
            'holiday_date' => $p('holiday_date'),
            'holiday_type' => $p('holiday_type'),
            'is_recurring' => isset($_POST['is_recurring']) ? '1' : '0',
            'remarks'      => $p('remarks'),
        ];
    }

    private function validate(array $data): array
    {
        $errors = Validator::required($data, ['name', 'holiday_date', 'holiday_type']);
        $errors = array_merge($errors, Validator::validDate($data, 'holiday_date'));
        $errors = array_merge($errors, Validator::inSet($data, 'holiday_type', Holiday::TYPES));
        return $errors;
    }
}

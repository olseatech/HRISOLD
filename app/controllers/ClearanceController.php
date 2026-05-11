<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Clearance;

final class ClearanceController extends Controller
{
    private const PER_PAGE = 15;

    private Clearance $clearances;

    public function __construct()
    {
        $this->clearances = new Clearance();
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $query  = trim((string) ($_GET['q'] ?? ''));
        $empId  = trim((string) ($_GET['employee_id'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        $total      = $this->clearances->countSearch($query, $empId, $status);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('clearances/index', [
            'title'         => 'Clearances',
            'rows'          => $this->clearances->search($query, $empId, $status, $page, self::PER_PAGE),
            'query'         => $query,
            'empId'         => $empId,
            'currentStatus' => $status,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'total'         => $total,
            'totalPending'  => $this->clearances->countByStatus('Pending'),
            'totalApproved' => $this->clearances->countByStatus('Approved'),
            'totalRejected' => $this->clearances->countByStatus('Rejected'),
            'employees'     => $this->clearances->employeeOptions(),
            'statuses'      => $this->clearances->statuses(),
            'success'       => Session::pullFlash('success'),
            'error'         => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $this->view('clearances/create', [
            'title'     => 'New Clearance Request',
            'csrf'      => CSRF::token(),
            'employees' => $this->clearances->employeeOptions(),
            'types'     => $this->clearances->clearanceTypes(),
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
            $this->redirect('/clearances/create');
        }

        try {
            $id      = $this->clearances->create($data);
            $created = $this->clearances->find($id);
            Audit::log('clearances', 'CREATE', $id, null, $created);

            Session::flash('success', 'Clearance request created successfully.');
            $this->redirect('/clearances/' . $id);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to save the clearance request. Please try again.');
            $this->redirect('/clearances/create');
        }
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(string $id): void
    {
        $record = $this->clearances->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Clearance request not found.';
            return;
        }

        $this->view('clearances/show', [
            'title'   => 'Clearance Request',
            'record'  => $record,
            'items'   => $this->clearances->getItems((int) $id),
            'csrf'    => CSRF::token(),
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function edit(string $id): void
    {
        $record = $this->clearances->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Clearance request not found.';
            return;
        }

        if ((string) ($record['status'] ?? '') !== 'Pending') {
            Session::flash('error', 'Only pending clearances can be edited.');
            $this->redirect('/clearances/' . $id);
        }

        $this->view('clearances/edit', [
            'title'     => 'Edit Clearance Request',
            'record'    => $record,
            'csrf'      => CSRF::token(),
            'employees' => $this->clearances->employeeOptions(),
            'types'     => $this->clearances->clearanceTypes(),
            'errors'    => Session::pullFlash('errors', []),
            'old'       => Session::pullFlash('old', []),
        ]);
    }

    public function update(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->clearances->find($recId);

        if (!$before) {
            http_response_code(404);
            echo 'Clearance request not found.';
            return;
        }

        if ((string) ($before['status'] ?? '') !== 'Pending') {
            Session::flash('error', 'Only pending clearances can be edited.');
            $this->redirect('/clearances/' . $recId);
        }

        $data   = $this->payload();
        $errors = $this->validate($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors and try again.');
            $this->redirect('/clearances/' . $recId . '/edit');
        }

        try {
            $this->clearances->updateById($recId, $data);
            $after = $this->clearances->find($recId);
            Audit::log('clearances', 'UPDATE', $recId, $before, $after);

            Session::flash('success', 'Clearance request updated successfully.');
            $this->redirect('/clearances/' . $recId);
        } catch (\Throwable) {
            Session::flash('old', $data);
            Session::flash('error', 'Unable to update the clearance request. Please try again.');
            $this->redirect('/clearances/' . $recId . '/edit');
        }
    }

    // -------------------------------------------------------------------------
    // Approve / Reject
    // -------------------------------------------------------------------------

    public function approve(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->clearances->find($recId);

        if (!$before) {
            Session::flash('error', 'Clearance request not found.');
            $this->redirect('/clearances');
        }

        $userId = (int) (Auth::user()['id'] ?? 0);

        try {
            $this->clearances->approve($recId, $userId);
            $after = $this->clearances->find($recId);
            Audit::log('clearances', 'APPROVE', $recId, $before, $after);
            Session::flash('success', 'Clearance request approved.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to approve this clearance request.');
        }

        $this->redirect('/clearances/' . $recId);
    }

    public function reject(string $id): void
    {
        $recId   = (int) $id;
        $before  = $this->clearances->find($recId);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if (!$before) {
            Session::flash('error', 'Clearance request not found.');
            $this->redirect('/clearances');
        }

        $userId = (int) (Auth::user()['id'] ?? 0);

        try {
            $this->clearances->reject($recId, $userId, $remarks !== '' ? $remarks : null);
            $after = $this->clearances->find($recId);
            Audit::log('clearances', 'REJECT', $recId, $before, $after);
            Session::flash('success', 'Clearance request rejected.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to reject this clearance request.');
        }

        $this->redirect('/clearances/' . $recId);
    }

    // -------------------------------------------------------------------------
    // Update item status (sign-off per office)
    // -------------------------------------------------------------------------

    public function updateItem(string $id): void
    {
        $clearanceId = (int) $id;
        $itemId      = (int) ($_POST['item_id'] ?? 0);
        $itemStatus  = trim((string) ($_POST['item_status'] ?? ''));
        $itemRemarks = trim((string) ($_POST['item_remarks'] ?? '')) ?: null;

        if (!in_array($itemStatus, $this->clearances->itemStatuses(), true)) {
            Session::flash('error', 'Invalid item status.');
            $this->redirect('/clearances/' . $clearanceId);
        }

        try {
            $this->clearances->updateItemStatus($itemId, $itemStatus, $itemRemarks);
            Audit::log('clearances', 'ITEM_UPDATE', $itemId, null, ['status' => $itemStatus]);
            Session::flash('success', 'Clearance item updated.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to update item status.');
        }

        $this->redirect('/clearances/' . $clearanceId);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function destroy(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->clearances->find($recId);

        if (!$before) {
            Session::flash('error', 'Clearance request not found.');
            $this->redirect('/clearances');
        }

        try {
            $this->clearances->deleteById($recId);
            Audit::log('clearances', 'DELETE', $recId, $before, null);
            Session::flash('success', 'Clearance request deleted successfully.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to delete this clearance request.');
        }

        $this->redirect('/clearances');
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
            'employee_id'    => $p('employee_id'),
            'clearance_type' => $p('clearance_type'),
            'purpose'        => $p('purpose'),
            'request_date'   => $p('request_date'),
            'remarks'        => $p('remarks'),
        ];
    }

    private function validate(array $data): array
    {
        return array_merge(
            Validator::required($data, ['employee_id', 'clearance_type', 'request_date']),
            Validator::validDate($data, 'request_date'),
            Validator::inSet($data, 'clearance_type', $this->clearances->clearanceTypes())
        );
    }
}

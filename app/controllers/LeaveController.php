<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveAttachment;
use App\Models\LeaveRequest;

final class LeaveController extends Controller
{
    private const PER_PAGE = 10;

    private LeaveRequest $leaveRequests;
    private Employee $employees;

    public function __construct()
    {
        $this->leaveRequests = new LeaveRequest();
        $this->employees     = new Employee();
    }

    // ── Admin list ────────────────────────────────────────────────────────────

    public function index(): void
    {
        $status = trim((string) ($_GET['status'] ?? ''));
        $query  = trim((string) ($_GET['q'] ?? ''));
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        $employeeScopeId = $this->employeeScopeId();
        $scopeFilterId   = $employeeScopeId;
        $employeeOptions = $this->employees->listSimple();
        $currentEmployee = null;
        $isSelfService   = $employeeScopeId !== null;

        if ($isSelfService) {
            $query = '';

            if (($employeeScopeId ?? 0) > 0) {
                $currentEmployee = $this->employees->find((int) $employeeScopeId);
                $employeeOptions = $currentEmployee ? [$currentEmployee] : [];
            } else {
                $scopeFilterId   = -1;
                $employeeOptions = [];
            }
        }

        $total      = $this->leaveRequests->countFiltered($status, $query, $scopeFilterId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('leave/index', [
            'title'           => 'Leave Management',
            'csrf'            => CSRF::token(),
            'requests'        => $this->leaveRequests->listFiltered($status, $query, $page, self::PER_PAGE, $scopeFilterId),
            'leaveTypes'      => $this->leaveRequests->leaveTypes(),
            'statusOptions'   => $this->leaveRequests->statuses(),
            'employees'       => $employeeOptions,
            'currentEmployee' => $currentEmployee,
            'isSelfService'   => $isSelfService,
            'query'           => $query,
            'status'          => $status,
            'page'            => $page,
            'totalPages'      => $totalPages,
            'total'           => $total,
            'success'         => Session::pullFlash('success'),
            'error'           => Session::pullFlash('error'),
            'errors'          => Session::pullFlash('errors', []),
            'old'             => Session::pullFlash('old', []),
        ]);
    }

    // ── My Leave (self-service — all roles) ────────────────────────────────────

    public function myLeave(): void
    {
        $user       = Auth::user();
        $employeeId = isset($user['employee_id']) ? (int) $user['employee_id'] : 0;

        $status = trim((string) ($_GET['status'] ?? ''));
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        if ($employeeId <= 0) {
            $this->view('leave/my-leave', [
                'title'          => 'My Leave',
                'csrf'           => CSRF::token(),
                'requests'       => [],
                'statusOptions'  => $this->leaveRequests->statuses(),
                'status'         => $status,
                'page'           => 1,
                'totalPages'     => 1,
                'total'          => 0,
                'noEmployee'     => true,
                'success'        => Session::pullFlash('success'),
                'error'          => Session::pullFlash('error'),
            ]);
            return;
        }

        $total      = $this->leaveRequests->countFiltered($status, '', $employeeId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('leave/my-leave', [
            'title'         => 'My Leave',
            'csrf'          => CSRF::token(),
            'requests'      => $this->leaveRequests->listFiltered($status, '', $page, self::PER_PAGE, $employeeId),
            'statusOptions' => $this->leaveRequests->statuses(),
            'status'        => $status,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'total'         => $total,
            'noEmployee'    => false,
            'success'       => Session::pullFlash('success'),
            'error'         => Session::pullFlash('error'),
        ]);
    }

    // ── Create form ────────────────────────────────────────────────────────────

    public function create(): void
    {
        $user       = Auth::user();
        $employeeId = isset($user['employee_id']) ? (int) $user['employee_id'] : 0;
        $currentEmployee = $employeeId > 0 ? $this->employees->find($employeeId) : null;

        $this->view('leave/create', [
            'title'           => 'New Leave Request',
            'csrf'            => CSRF::token(),
            'leaveTypes'      => $this->leaveRequests->leaveTypes(),
            'currentEmployee' => $currentEmployee,
            'employeeId'      => $employeeId,
            'errors'          => Session::pullFlash('errors', []),
            'old'             => Session::pullFlash('old', []),
        ]);
    }

    // ── Store (creates Draft) ──────────────────────────────────────────────────

    public function store(): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your session token is invalid. Please try again.');
            $this->redirect('/leave/create');
        }

        $user = Auth::user();
        $employeeId = isset($user['employee_id']) ? (int) $user['employee_id'] : 0;

        if ($employeeId <= 0) {
            Session::flash('error', 'Your account is not linked to an employee profile. Contact an administrator.');
            $this->redirect('/leave/create');
        }

        $data = [
            'employee_id'   => (string) $employeeId,
            'leave_type_id' => trim((string) ($_POST['leave_type_id'] ?? '')),
            'start_date'    => trim((string) ($_POST['start_date'] ?? '')),
            'end_date'      => trim((string) ($_POST['end_date'] ?? '')),
            'total_days'    => trim((string) ($_POST['total_days'] ?? '')),
            'reason'        => mb_substr(trim((string) ($_POST['reason'] ?? '')), 0, 1000),
            'status'        => 'Draft',
        ];

        $errors = Validator::required($data, ['leave_type_id', 'start_date', 'end_date', 'total_days']);
        $errors = array_merge($errors, Validator::validDate($data, 'start_date'));
        $errors = array_merge($errors, Validator::validDate($data, 'end_date'));
        $errors = array_merge($errors, Validator::dateOrder($data, 'start_date', 'end_date'));
        $errors = array_merge($errors, Validator::numericMin($data, 'total_days', 0.5));

        if ($data['leave_type_id'] !== '' && !ctype_digit($data['leave_type_id'])) {
            $errors['leave_type_id'] = 'Leave type value is invalid.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please complete the leave request form.');
            $this->redirect('/leave/create');
        }

        $id = $this->leaveRequests->createRequest($data);

        // Handle optional file attachments
        $this->processAttachments($id, $user['id'] ?? null);

        $created = $this->leaveRequests->find($id);
        Audit::log('leave', 'CREATE', $id, null, $created);

        Session::flash('success', 'Leave request saved as draft.');
        $this->redirect('/leave/' . $id);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(string $id): void
    {
        $record = $this->leaveRequests->findWithJoins((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Leave request not found.';
            return;
        }

        $attachments = (new LeaveAttachment())->findByRequest((int) $id);

        $this->view('leave/show', [
            'title'       => 'Leave Request',
            'csrf'        => CSRF::token(),
            'record'      => $record,
            'attachments' => $attachments,
            'success'     => Session::pullFlash('success'),
            'error'       => Session::pullFlash('error'),
        ]);
    }

    // ── Edit (Draft only) ─────────────────────────────────────────────────────

    public function edit(string $id): void
    {
        $record = $this->leaveRequests->findWithJoins((int) $id);

        if (!$record) {
            Session::flash('error', 'Leave request not found.');
            $this->redirect('/my-leave');
        }

        $user       = Auth::user();
        $employeeId = isset($user['employee_id']) ? (int) $user['employee_id'] : 0;

        if ((int) ($record['employee_id'] ?? 0) !== $employeeId) {
            Session::flash('error', 'Access denied.');
            $this->redirect('/my-leave');
        }

        if (($record['status'] ?? '') !== 'Draft') {
            Session::flash('error', 'Only draft requests can be edited.');
            $this->redirect('/leave/' . $id);
        }

        $this->view('leave/edit', [
            'title'      => 'Edit Leave Request',
            'csrf'       => CSRF::token(),
            'record'     => $record,
            'leaveTypes' => $this->leaveRequests->leaveTypes(),
            'errors'     => Session::pullFlash('errors', []),
            'old'        => Session::pullFlash('old', []),
        ]);
    }

    // ── Update (Draft only) ────────────────────────────────────────────────────

    public function update(string $id): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Invalid session token. Please try again.');
            $this->redirect('/leave/' . $id . '/edit');
        }

        $recId  = (int) $id;
        $user   = Auth::user();
        $empId  = isset($user['employee_id']) ? (int) $user['employee_id'] : 0;
        $before = $this->leaveRequests->find($recId);

        if (!$before || (int) ($before['employee_id'] ?? 0) !== $empId) {
            Session::flash('error', 'Leave request not found or access denied.');
            $this->redirect('/my-leave');
        }

        if (($before['status'] ?? '') !== 'Draft') {
            Session::flash('error', 'Only draft requests can be edited.');
            $this->redirect('/leave/' . $recId);
        }

        $data = [
            'leave_type_id' => trim((string) ($_POST['leave_type_id'] ?? '')),
            'start_date'    => trim((string) ($_POST['start_date'] ?? '')),
            'end_date'      => trim((string) ($_POST['end_date'] ?? '')),
            'total_days'    => trim((string) ($_POST['total_days'] ?? '')),
            'reason'        => mb_substr(trim((string) ($_POST['reason'] ?? '')), 0, 1000),
        ];

        $errors = Validator::required($data, ['leave_type_id', 'start_date', 'end_date', 'total_days']);
        $errors = array_merge($errors, Validator::validDate($data, 'start_date'));
        $errors = array_merge($errors, Validator::validDate($data, 'end_date'));
        $errors = array_merge($errors, Validator::dateOrder($data, 'start_date', 'end_date'));
        $errors = array_merge($errors, Validator::numericMin($data, 'total_days', 0.5));

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors below.');
            $this->redirect('/leave/' . $recId . '/edit');
        }

        $this->leaveRequests->updateDraft($recId, $data, $empId);

        // Handle optional new file attachments
        $this->processAttachments($recId, $user['id'] ?? null);

        $after = $this->leaveRequests->find($recId);
        Audit::log('leave', 'UPDATE', $recId, $before, $after);

        Session::flash('success', 'Leave request updated.');
        $this->redirect('/leave/' . $recId);
    }

    // ── Submit (Draft → Pending) ──────────────────────────────────────────────

    public function submit(string $id): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Invalid session token. Please try again.');
            $this->redirect('/leave/' . $id);
        }

        $recId  = (int) $id;
        $user   = Auth::user();
        $empId  = isset($user['employee_id']) ? (int) $user['employee_id'] : 0;
        $before = $this->leaveRequests->find($recId);

        if (!$before || (int) ($before['employee_id'] ?? 0) !== $empId) {
            Session::flash('error', 'Leave request not found or access denied.');
            $this->redirect('/my-leave');
        }

        if (!$this->leaveRequests->updateStatusFromDraft($recId, $empId)) {
            Session::flash('error', 'Only draft requests can be submitted.');
            $this->redirect('/leave/' . $recId);
        }

        $after = $this->leaveRequests->find($recId);
        Audit::log('leave', 'SUBMIT', $recId, $before, $after);

        Session::flash('success', 'Leave request submitted for approval.');
        $this->redirect('/leave/' . $recId);
    }

    // ── Cancel (Pending → Cancelled) ──────────────────────────────────────────

    public function cancel(string $id): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Invalid session token. Please try again.');
            $this->redirect('/leave/' . $id);
        }

        $recId  = (int) $id;
        $user   = Auth::user();
        $empId  = isset($user['employee_id']) ? (int) $user['employee_id'] : 0;
        $before = $this->leaveRequests->find($recId);

        if (!$before || (int) ($before['employee_id'] ?? 0) !== $empId) {
            Session::flash('error', 'Leave request not found or access denied.');
            $this->redirect('/my-leave');
        }

        if (!$this->leaveRequests->cancelByEmployee($recId, $empId)) {
            Session::flash('error', 'Only pending requests can be cancelled.');
            $this->redirect('/leave/' . $recId);
        }

        $after = $this->leaveRequests->find($recId);
        Audit::log('leave', 'CANCEL', $recId, $before, $after);

        Session::flash('success', 'Leave request cancelled.');
        $this->redirect('/leave/' . $recId);
    }

    // ── Approve ───────────────────────────────────────────────────────────────

    public function approve(string $id): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your session token is invalid. Please try again.');
            $this->redirect('/leave');
        }

        $leaveId = (int) $id;
        $before  = $this->leaveRequests->find($leaveId);

        if (!$before) {
            Session::flash('error', 'Leave request not found.');
            $this->redirect('/leave');
        }

        $currentUser           = Auth::user();
        $currentUserEmployeeId = isset($currentUser['employee_id']) ? (int) $currentUser['employee_id'] : null;
        $requesterEmployeeId   = isset($before['employee_id']) ? (int) $before['employee_id'] : null;

        if ($currentUserEmployeeId !== null && $requesterEmployeeId !== null && $currentUserEmployeeId === $requesterEmployeeId) {
            Session::flash('error', 'You cannot approve your own leave request.');
            $this->redirect('/leave');
        }

        $remarks = mb_substr(trim((string) ($_POST['review_remarks'] ?? 'Approved')), 0, 500);

        if (!$this->leaveRequests->updateStatus($leaveId, 'Approved', Auth::id(), $remarks)) {
            Session::flash('error', 'Only pending requests can be approved.');
            $this->redirect('/leave');
        }

        $after = $this->leaveRequests->find($leaveId);
        Audit::log('leave', 'UPDATE', $leaveId, $before, $after);

        Session::flash('success', 'Leave request approved.');
        $this->redirect(isset($_POST['from_show']) ? '/leave/' . $leaveId : '/leave');
    }

    // ── Reject ────────────────────────────────────────────────────────────────

    public function reject(string $id): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your session token is invalid. Please try again.');
            $this->redirect('/leave');
        }

        $leaveId = (int) $id;
        $before  = $this->leaveRequests->find($leaveId);

        if (!$before) {
            Session::flash('error', 'Leave request not found.');
            $this->redirect('/leave');
        }

        $currentUser           = Auth::user();
        $currentUserEmployeeId = isset($currentUser['employee_id']) ? (int) $currentUser['employee_id'] : null;
        $requesterEmployeeId   = isset($before['employee_id']) ? (int) $before['employee_id'] : null;

        if ($currentUserEmployeeId !== null && $requesterEmployeeId !== null && $currentUserEmployeeId === $requesterEmployeeId) {
            Session::flash('error', 'You cannot reject your own leave request.');
            $this->redirect('/leave');
        }

        $remarks = mb_substr(trim((string) ($_POST['review_remarks'] ?? 'Rejected')), 0, 500);

        if (!$this->leaveRequests->updateStatus($leaveId, 'Rejected', Auth::id(), $remarks)) {
            Session::flash('error', 'Only pending requests can be rejected.');
            $this->redirect('/leave');
        }

        $after = $this->leaveRequests->find($leaveId);
        Audit::log('leave', 'UPDATE', $leaveId, $before, $after);

        Session::flash('success', 'Leave request rejected.');
        $this->redirect(isset($_POST['from_show']) ? '/leave/' . $leaveId : '/leave');
    }

    // ── Download attachment ───────────────────────────────────────────────────

    public function downloadAttachment(string $id, string $attId): void
    {
        $attId = (int) $attId;
        $att   = (new LeaveAttachment())->find($attId);

        if (!$att) {
            http_response_code(404);
            echo 'Attachment not found.';
            return;
        }

        $reqId      = (int) ($att['leave_request_id'] ?? 0);
        $storedName = (string) ($att['stored_filename'] ?? '');
        $filePath   = LeaveAttachment::storagePath($reqId, $storedName);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            http_response_code(404);
            echo 'File not found on server.';
            return;
        }

        $originalName = (string) ($att['original_filename'] ?? 'download');
        $mimeType     = (string) ($att['mime_type'] ?? 'application/octet-stream');
        $fileSize     = (int) ($att['file_size'] ?? filesize($filePath));

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . addslashes($originalName) . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: private, no-cache');
        header('Pragma: no-cache');

        readfile($filePath);
        exit;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function employeeScopeId(): ?int
    {
        $user = Auth::user();
        if (!is_employee_role($user)) {
            return null;
        }

        return isset($user['employee_id']) ? (int) $user['employee_id'] : 0;
    }

    private function processAttachments(int $leaveRequestId, mixed $uploadedBy): void
    {
        $files = $_FILES['attachments'] ?? null;

        if (!$files || !is_array($files['name'] ?? null)) {
            return;
        }

        $attachmentModel = new LeaveAttachment();
        $storageDir      = LeaveAttachment::storageDir($leaveRequestId);

        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $userId = $uploadedBy !== null ? (int) $uploadedBy : null;
        $count  = count((array) $files['name']);

        for ($i = 0; $i < $count; $i++) {
            $name  = (string) ($files['name'][$i]   ?? '');
            $tmp   = (string) ($files['tmp_name'][$i] ?? '');
            $size  = (int)   ($files['size'][$i]    ?? 0);
            $error = (int)   ($files['error'][$i]   ?? UPLOAD_ERR_NO_FILE);

            if ($error === UPLOAD_ERR_NO_FILE || $tmp === '' || $size === 0) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($size > LeaveAttachment::MAX_FILE_SIZE) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, LeaveAttachment::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($tmp);
            if ($detectedMime === false || !in_array($detectedMime, LeaveAttachment::ALLOWED_MIME_TYPES, true)) {
                continue;
            }

            $storedName = uniqid('lv_', true) . '.' . $ext;
            $destPath   = $storageDir . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($tmp, $destPath)) {
                continue;
            }

            try {
                $attachmentModel->create([
                    'leave_request_id' => $leaveRequestId,
                    'original_filename' => $name,
                    'stored_filename'   => $storedName,
                    'file_size'         => $size,
                    'mime_type'         => $detectedMime,
                    'uploaded_by'       => $userId,
                ]);
            } catch (\Throwable) {
                if (file_exists($destPath)) {
                    unlink($destPath);
                }
            }
        }
    }
}

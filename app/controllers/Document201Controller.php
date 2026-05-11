<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Document201;

final class Document201Controller extends Controller
{
    private const PER_PAGE = 15;

    private Document201 $docs;

    public function __construct()
    {
        $this->docs = new Document201();
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $query    = trim((string) ($_GET['q'] ?? ''));
        $empId    = trim((string) ($_GET['employee_id'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));
        $page     = max(1, (int) ($_GET['page'] ?? 1));

        $total      = $this->docs->countSearch($query, $empId, $category);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('documents/index', [
            'title'           => '201 Documents',
            'rows'            => $this->docs->search($query, $empId, $category, $page, self::PER_PAGE),
            'query'           => $query,
            'empId'           => $empId,
            'currentCategory' => $category,
            'page'            => $page,
            'totalPages'      => $totalPages,
            'total'           => $total,
            'totalDocs'       => $this->docs->countTotal(),
            'totalEmp'        => $this->docs->countEmployeesWithDocuments(),
            'employees'       => $this->docs->employeeOptions(),
            'categories'      => $this->docs->categories(),
            'success'         => Session::pullFlash('success'),
            'error'           => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Upload form
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $this->view('documents/create', [
            'title'      => 'Upload Document',
            'csrf'       => CSRF::token(),
            'employees'  => $this->docs->employeeOptions(),
            'categories' => $this->docs->categories(),
            'errors'     => Session::pullFlash('errors', []),
            'old'        => Session::pullFlash('old', []),
            'preEmpId'   => trim((string) ($_GET['employee_id'] ?? '')),
        ]);
    }

    public function store(): void
    {
        $data   = $this->payload();
        $errors = $this->validateUpload($data);

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix the errors and try again.');
            $this->redirect('/documents/create');
        }

        $file      = $_FILES['document'];
        $empId     = (int) $data['employee_id'];
        $storageDir = Document201::storageDir($empId);

        // Create per-employee directory if it doesn't exist
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Build safe stored filename: uniqid + real extension
        $originalName = (string) $file['name'];
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $storedName   = uniqid('doc_', true) . '.' . $ext;
        $destPath     = $storageDir . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::flash('old', $data);
            Session::flash('error', 'Failed to save the uploaded file. Please try again.');
            $this->redirect('/documents/create');
        }

        try {
            $userId = (int) (Auth::user()['id'] ?? 0);
            $id = $this->docs->create([
                'employee_id'       => $empId,
                'doc_category'      => $data['doc_category'],
                'title'             => $data['title'],
                'description'       => $data['description'],
                'original_filename' => $originalName,
                'stored_filename'   => $storedName,
                'file_size'         => (int) $file['size'],
                'mime_type'         => (string) $file['type'],
                'uploaded_by'       => $userId ?: null,
            ]);

            $created = $this->docs->find($id);
            Audit::log('documents_201', 'UPLOAD', $id, null, $created);

            Session::flash('success', 'Document uploaded successfully.');
            $this->redirect('/documents/' . $id);
        } catch (\Throwable) {
            // Clean up the uploaded file if DB insert fails
            if (file_exists($destPath)) {
                unlink($destPath);
            }
            Session::flash('old', $data);
            Session::flash('error', 'Unable to save the document record. Please try again.');
            $this->redirect('/documents/create');
        }
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(string $id): void
    {
        $record = $this->docs->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Document not found.';
            return;
        }

        $this->view('documents/show', [
            'title'   => 'Document',
            'record'  => $record,
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Download (streams file, never exposes storage path)
    // -------------------------------------------------------------------------

    public function download(string $id): void
    {
        $record = $this->docs->find((int) $id);

        if (!$record) {
            http_response_code(404);
            echo 'Document not found.';
            return;
        }

        $empId      = (int) ($record['employee_id'] ?? 0);
        $storedName = (string) ($record['stored_filename'] ?? '');
        $filePath   = Document201::storagePath($empId, $storedName);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            http_response_code(404);
            echo 'File not found on server.';
            return;
        }

        $originalName = (string) ($record['original_filename'] ?? 'download');
        $mimeType     = (string) ($record['mime_type'] ?? 'application/octet-stream');
        $fileSize     = (int) ($record['file_size'] ?? filesize($filePath));

        // Clear any output buffering before streaming
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

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function destroy(string $id): void
    {
        $recId  = (int) $id;
        $before = $this->docs->find($recId);

        if (!$before) {
            Session::flash('error', 'Document not found.');
            $this->redirect('/documents');
        }

        // Delete the physical file first
        $empId      = (int) ($before['employee_id'] ?? 0);
        $storedName = (string) ($before['stored_filename'] ?? '');
        $filePath   = Document201::storagePath($empId, $storedName);

        if ($storedName !== '' && file_exists($filePath)) {
            unlink($filePath);
        }

        try {
            $this->docs->deleteById($recId);
            Audit::log('documents_201', 'DELETE', $recId, $before, null);
            Session::flash('success', 'Document deleted successfully.');
        } catch (\Throwable) {
            Session::flash('error', 'Unable to delete document record.');
        }

        $this->redirect('/documents');
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
            'employee_id'  => $p('employee_id'),
            'doc_category' => $p('doc_category'),
            'title'        => $p('title'),
            'description'  => $p('description'),
        ];
    }

    private function validateUpload(array $data): array
    {
        $errors = array_merge(
            Validator::required($data, ['employee_id', 'doc_category', 'title']),
            Validator::inSet($data, 'doc_category', $this->docs->categories())
        );

        $file = $_FILES['document'] ?? null;

        if (!$file || (int) ($file['size'] ?? 0) === 0 || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors['document'] = 'A file is required.';
            return $errors;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors['document'] = 'File upload failed (error code ' . (int) $file['error'] . ').';
            return $errors;
        }

        if ((int) $file['size'] > Document201::MAX_FILE_SIZE) {
            $errors['document'] = 'File must be 10 MB or smaller.';
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, Document201::ALLOWED_EXTENSIONS, true)) {
            $errors['document'] = 'Allowed file types: PDF, JPG, PNG, DOC, DOCX.';
        }

        // Verify MIME via finfo (more reliable than browser-reported type)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file((string) ($file['tmp_name'] ?? ''));
        if ($detectedMime === false || !in_array($detectedMime, Document201::ALLOWED_MIME_TYPES, true)) {
            $errors['document'] = 'File type not allowed. Allowed: PDF, JPG, PNG, DOC, DOCX.';
        }

        return $errors;
    }
}

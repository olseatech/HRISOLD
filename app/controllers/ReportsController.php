<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Clearance;
use App\Models\Employee;
use App\Models\Pds;
use App\Models\ServiceRecord;

final class ReportsController extends Controller
{
    private Employee      $employees;
    private Pds           $pds;
    private ServiceRecord $serviceRecords;
    private Clearance     $clearances;

    public function __construct()
    {
        $this->employees      = new Employee();
        $this->pds            = new Pds();
        $this->serviceRecords = new ServiceRecord();
        $this->clearances     = new Clearance();
    }

    /** Reports dashboard — lists available print reports */
    public function index(): void
    {
        $empId = (int) ($_GET['employee_id'] ?? 0);

        $this->view('reports/index', [
            'title'     => 'Reports',
            'employees' => $this->employees->allActive(),
            'empId'     => $empId,
            'success'   => Session::pullFlash('success'),
            'error'     => Session::pullFlash('error'),
        ]);
    }

    /** Print-ready PDS (CS Form 212) — HTML version */
    public function printPds(string $id): void
    {
        $empId    = (int) $id;
        $employee = $this->employees->find($empId);

        if (!$employee) {
            Session::flash('error', 'Employee not found.');
            $this->redirect('/reports');
        }

        $pdsRecord = $this->pds->findByEmployee($empId);

        $children     = $pdsRecord ? $this->pds->getChildren((int) $pdsRecord['id']) : [];
        $education    = $pdsRecord ? $this->pds->getEducation((int) $pdsRecord['id']) : [];
        $civilService = $pdsRecord ? $this->pds->getCivilService((int) $pdsRecord['id']) : [];
        $workExp      = $pdsRecord ? $this->pds->getWorkExperience((int) $pdsRecord['id']) : [];
        $voluntary    = $pdsRecord ? $this->pds->getVoluntaryWork((int) $pdsRecord['id']) : [];
        $lnd          = $pdsRecord ? $this->pds->getLearningDevelopment((int) $pdsRecord['id']) : [];
        $otherInfo    = $pdsRecord ? $this->pds->getOtherInfo((int) $pdsRecord['id']) : [];
        $references   = $pdsRecord ? $this->pds->getReferences((int) $pdsRecord['id']) : [];

        $this->view('reports/print-pds', [
            'title'       => 'PDS — ' . trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')),
            'employee'    => $employee,
            'pds'         => $pdsRecord ?? [],
            'children'    => $children,
            'education'   => $education,
            'civilService'=> $civilService,
            'workExp'     => $workExp,
            'voluntary'   => $voluntary,
            'lnd'         => $lnd,
            'otherInfo'   => $otherInfo,
            'references'  => $references,
        ], 'print');
    }

    /** Print-ready Service Record */
    public function printServiceRecord(string $id): void
    {
        $empId    = (int) $id;
        $employee = $this->employees->find($empId);

        if (!$employee) {
            Session::flash('error', 'Employee not found.');
            $this->redirect('/reports');
        }

        $this->view('reports/print-service-record', [
            'title'    => 'Service Record — ' . trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')),
            'employee' => $employee,
            'records'  => $this->serviceRecords->findByEmployee($empId),
        ], 'print');
    }

    /** Employee Certification */
    public function printCertification(string $id): void
    {
        $empId    = (int) $id;
        $employee = $this->employees->find($empId);

        if (!$employee) {
            Session::flash('error', 'Employee not found.');
            $this->redirect('/reports');
        }

        $current = $this->serviceRecords->currentForEmployee($empId);

        $this->view('reports/print-certification', [
            'title'    => 'Employee Certification — ' . trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')),
            'employee' => $employee,
            'current'  => $current,
        ], 'print');
    }

    /** Clearance Form */
    public function printClearance(string $id): void
    {
        $clearance = $this->clearances->find((int) $id);

        if (!$clearance) {
            Session::flash('error', 'Clearance not found.');
            $this->redirect('/reports');
        }

        $items    = $this->clearances->getItems((int) $id);
        $empId    = (int) ($clearance['employee_id'] ?? 0);
        $employee = $this->employees->find($empId);

        $this->view('reports/print-clearance', [
            'title'     => 'Clearance Form',
            'clearance' => $clearance,
            'items'     => $items,
            'employee'  => $employee ?? [],
        ], 'print');
    }
}

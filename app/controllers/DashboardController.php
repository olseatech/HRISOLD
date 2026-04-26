<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;

final class DashboardController extends Controller
{
    private Employee $employees;
    private Attendance $attendance;
    private LeaveRequest $leaveRequests;

    public function __construct()
    {
        $this->employees = new Employee();
        $this->attendance = new Attendance();
        $this->leaveRequests = new LeaveRequest();
    }

    public function index(): void
    {
        $today = date('Y-m-d');
        $rangeStart = date('Y-m-d', strtotime('-6 days'));
        $trendMap = $this->attendance->presentTrendByRange($rangeStart, $today);

        $trendLabels = [];
        $trendValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime('-' . $i . ' days'));
            $trendLabels[] = date('M d', strtotime($date));
            $trendValues[] = (int) ($trendMap[$date] ?? 0);
        }

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => Auth::user(),
            'today' => $today,
            'stats' => [
                'totalEmployees' => $this->employees->countAll(),
                'presentToday' => $this->attendance->countPresentByDate($today),
                'pendingLeaves' => $this->leaveRequests->countByStatus('Pending'),
            ],
            'trend' => [
                'labels' => $trendLabels,
                'values' => $trendValues,
            ],
        ]);
    }
}

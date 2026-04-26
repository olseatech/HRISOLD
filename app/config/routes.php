<?php

declare(strict_types=1);

return [
    'GET' => [
        '/' => ['controller' => 'LandingController', 'method' => 'index', 'middleware' => ['guest']],
        '/pricing' => ['controller' => 'LandingController', 'method' => 'pricing', 'middleware' => ['guest']],

        '/dashboard' => ['controller' => 'DashboardController', 'method' => 'index', 'middleware' => ['auth', 'subscription', 'permission:dashboard.view']],
        '/billing' => ['controller' => 'BillingController', 'method' => 'index', 'middleware' => ['auth']],
        '/billing/continue' => ['controller' => 'BillingController', 'method' => 'continueToApp', 'middleware' => ['auth']],

        '/login' => ['controller' => 'AuthController', 'method' => 'showLogin', 'middleware' => ['guest']],
        '/logout' => ['controller' => 'AuthController', 'method' => 'logout', 'middleware' => ['auth']],

        '/employees' => ['controller' => 'EmployeeController', 'method' => 'index', 'middleware' => ['auth', 'subscription', 'permission:employees.view']],
        '/employees/create' => ['controller' => 'EmployeeController', 'method' => 'create', 'middleware' => ['auth', 'subscription', 'permission:employees.create']],
        '/employees/{id}' => ['controller' => 'EmployeeController', 'method' => 'show', 'middleware' => ['auth', 'subscription', 'permission:employees.view']],
        '/employees/{id}/edit' => ['controller' => 'EmployeeController', 'method' => 'edit', 'middleware' => ['auth', 'subscription', 'permission:employees.update']],

        '/attendance' => ['controller' => 'AttendanceController', 'method' => 'index', 'middleware' => ['auth', 'subscription', 'permission:attendance.view']],

        '/leave' => ['controller' => 'LeaveController', 'method' => 'index', 'middleware' => ['auth', 'subscription', 'permission:leave.view']],

        '/payroll' => ['controller' => 'PayrollController', 'method' => 'index', 'middleware' => ['auth', 'subscription', 'permission:payroll.view']],

        '/settings' => ['controller' => 'SettingsController', 'method' => 'index', 'middleware' => ['auth', 'subscription', 'permission:settings.manage']],
    ],
    'POST' => [
        '/subscribe' => ['controller' => 'LandingController', 'method' => 'subscribe', 'middleware' => ['csrf']],

        '/login' => ['controller' => 'AuthController', 'method' => 'login', 'middleware' => ['guest', 'csrf']],

        '/billing/checkout' => ['controller' => 'BillingController', 'method' => 'checkout', 'middleware' => ['auth', 'csrf']],
        '/billing/cancel' => ['controller' => 'BillingController', 'method' => 'cancel', 'middleware' => ['auth', 'csrf', 'permission:billing.manage']],

        '/employees' => ['controller' => 'EmployeeController', 'method' => 'store', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:employees.create']],
        '/employees/{id}/update' => ['controller' => 'EmployeeController', 'method' => 'update', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:employees.update']],
        '/employees/{id}/delete' => ['controller' => 'EmployeeController', 'method' => 'destroy', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:employees.delete']],

        '/attendance' => ['controller' => 'AttendanceController', 'method' => 'store', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:attendance.manage']],

        '/leave/request' => ['controller' => 'LeaveController', 'method' => 'store', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:leave.request']],
        '/leave/{id}/approve' => ['controller' => 'LeaveController', 'method' => 'approve', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:leave.approve']],
        '/leave/{id}/reject' => ['controller' => 'LeaveController', 'method' => 'reject', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:leave.approve']],

        '/payroll/grades' => ['controller' => 'PayrollController', 'method' => 'storeGrade', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:payroll.view']],
        '/payroll/salaries' => ['controller' => 'PayrollController', 'method' => 'storeSalary', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:payroll.view']],

        '/settings/company' => ['controller' => 'SettingsController', 'method' => 'saveCompany', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:settings.manage']],
        '/settings/system' => ['controller' => 'SettingsController', 'method' => 'saveSystem', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:settings.manage']],
        '/settings/roles/{id}/toggle' => ['controller' => 'SettingsController', 'method' => 'toggleRole', 'middleware' => ['auth', 'subscription', 'csrf', 'permission:settings.manage']],
    ],
];

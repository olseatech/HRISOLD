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

        '/pds' => ['controller' => 'PdsController', 'method' => 'index', 'middleware' => ['auth', 'permission:pds.view']],
        '/pds/create' => ['controller' => 'PdsController', 'method' => 'create', 'middleware' => ['auth', 'permission:pds.create']],
        '/pds/{id}' => ['controller' => 'PdsController', 'method' => 'show', 'middleware' => ['auth', 'permission:pds.view']],
        '/pds/{id}/edit' => ['controller' => 'PdsController', 'method' => 'edit', 'middleware' => ['auth', 'permission:pds.update']],

        '/service-records' => ['controller' => 'ServiceRecordController', 'method' => 'index', 'middleware' => ['auth', 'permission:service_records.view']],
        '/service-records/create' => ['controller' => 'ServiceRecordController', 'method' => 'create', 'middleware' => ['auth', 'permission:service_records.create']],
        '/service-records/{id}' => ['controller' => 'ServiceRecordController', 'method' => 'show', 'middleware' => ['auth', 'permission:service_records.view']],
        '/service-records/{id}/edit' => ['controller' => 'ServiceRecordController', 'method' => 'edit', 'middleware' => ['auth', 'permission:service_records.update']],

        '/clearances' => ['controller' => 'ClearanceController', 'method' => 'index', 'middleware' => ['auth', 'permission:clearances.view']],
        '/clearances/create' => ['controller' => 'ClearanceController', 'method' => 'create', 'middleware' => ['auth', 'permission:clearances.create']],
        '/clearances/{id}' => ['controller' => 'ClearanceController', 'method' => 'show', 'middleware' => ['auth', 'permission:clearances.view']],
        '/clearances/{id}/edit' => ['controller' => 'ClearanceController', 'method' => 'edit', 'middleware' => ['auth', 'permission:clearances.update']],

        '/appointments' => ['controller' => 'AppointmentController', 'method' => 'index', 'middleware' => ['auth', 'permission:appointments.view']],
        '/appointments/create' => ['controller' => 'AppointmentController', 'method' => 'create', 'middleware' => ['auth', 'permission:appointments.create']],
        '/appointments/{id}' => ['controller' => 'AppointmentController', 'method' => 'show', 'middleware' => ['auth', 'permission:appointments.view']],
        '/appointments/{id}/edit' => ['controller' => 'AppointmentController', 'method' => 'edit', 'middleware' => ['auth', 'permission:appointments.update']],
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

        '/pds' => ['controller' => 'PdsController', 'method' => 'store', 'middleware' => ['auth', 'csrf', 'permission:pds.create']],
        '/pds/{id}/update' => ['controller' => 'PdsController', 'method' => 'update', 'middleware' => ['auth', 'csrf', 'permission:pds.update']],
        '/pds/{id}/delete' => ['controller' => 'PdsController', 'method' => 'destroy', 'middleware' => ['auth', 'csrf', 'permission:pds.delete']],

        '/service-records' => ['controller' => 'ServiceRecordController', 'method' => 'store', 'middleware' => ['auth', 'csrf', 'permission:service_records.create']],
        '/service-records/{id}/update' => ['controller' => 'ServiceRecordController', 'method' => 'update', 'middleware' => ['auth', 'csrf', 'permission:service_records.update']],
        '/service-records/{id}/delete' => ['controller' => 'ServiceRecordController', 'method' => 'destroy', 'middleware' => ['auth', 'csrf', 'permission:service_records.delete']],

        '/clearances' => ['controller' => 'ClearanceController', 'method' => 'store', 'middleware' => ['auth', 'csrf', 'permission:clearances.create']],
        '/clearances/{id}/update' => ['controller' => 'ClearanceController', 'method' => 'update', 'middleware' => ['auth', 'csrf', 'permission:clearances.update']],
        '/clearances/{id}/delete' => ['controller' => 'ClearanceController', 'method' => 'destroy', 'middleware' => ['auth', 'csrf', 'permission:clearances.delete']],
        '/clearances/{id}/approve' => ['controller' => 'ClearanceController', 'method' => 'approve', 'middleware' => ['auth', 'csrf', 'permission:clearances.approve']],
        '/clearances/{id}/reject' => ['controller' => 'ClearanceController', 'method' => 'reject', 'middleware' => ['auth', 'csrf', 'permission:clearances.approve']],
        '/clearances/{id}/item-update' => ['controller' => 'ClearanceController', 'method' => 'updateItem', 'middleware' => ['auth', 'csrf', 'permission:clearances.update']],

        '/appointments' => ['controller' => 'AppointmentController', 'method' => 'store', 'middleware' => ['auth', 'csrf', 'permission:appointments.create']],
        '/appointments/{id}/update' => ['controller' => 'AppointmentController', 'method' => 'update', 'middleware' => ['auth', 'csrf', 'permission:appointments.update']],
        '/appointments/{id}/delete' => ['controller' => 'AppointmentController', 'method' => 'destroy', 'middleware' => ['auth', 'csrf', 'permission:appointments.delete']],
    ],
];

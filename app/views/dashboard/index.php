<?php
$statsData = is_array($stats ?? null) ? $stats : [];
$trendData = is_array($trend ?? null) ? $trend : ['labels' => [], 'values' => []];
$todayLabel = date('M d, Y', strtotime((string) ($today ?? date('Y-m-d'))));
$trendJson = json_encode($trendData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$roleLabel = (string) (($user['role_name'] ?? 'N/A'));

if (super_admin_only_mode_enabled()) {
    $roleLabel = is_super_admin_user($user)
        ? 'Super Admin (Authority)'
        : 'Restricted Access';
}

if ($trendJson === false) {
    $trendJson = '{"labels":[],"values":[]}';
}
?>

<section class="page dashboard-v3">
    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <header class="page-banner dash-hero-v3">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/><path d="M13 13h4"/><path d="M13 17h4"/><path d="M9 13h0"/><path d="M9 17h0"/></svg>
                Governance Dashboard
            </p>
            <h2 class="page-banner-title">Operational snapshot for <?= e($todayLabel) ?></h2>
            <p class="page-banner-sub">Monitor staffing levels, unblock administrative approvals, and maintain oversight of barangay human resources from a single command point.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Attendance Live</span>
                <span class="badge badge-teal">Leave Workflow</span>
                <span class="badge badge-warning">Payroll Ready</span>
            </div>
        </div>
        
        <aside class="user-context-glass">
            <div class="context-data">
                <span class="context-label">Authenticated As</span>
                <span class="context-value"><?= e((string) ($user['username'] ?? 'Unknown')) ?></span>
                <span class="context-meta"><?= e($roleLabel) ?></span>
            </div>
            <div class="context-avatar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
        </aside>
    </header>

    <div class="stat-grid">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total Employees</span>
                <span class="stat-value"><?= e((string) ($statsData['totalEmployees'] ?? 0)) ?></span>
                <span class="stat-note">Active Records</span>
            </div>
        </article>
        
        <article class="stat card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Present Today</span>
                <span class="stat-value"><?= e((string) ($statsData['presentToday'] ?? 0)) ?></span>
                <span class="stat-note">All Statuses Combined</span>
            </div>
        </article>

        <article class="stat card-shine">
            <div class="stat-icon is-red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 13h4"/><path d="M10 17h4"/><path d="M10 9h2"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Pending Leaves</span>
                <span class="stat-value text-red"><?= e((string) ($statsData['pendingLeaves'] ?? 0)) ?></span>
                <span class="stat-note">Waiting for Review</span>
            </div>
        </article>
    </div>

    <div class="dash-layout-grid">
        <section class="card overflow-hidden">
            <div class="card-header">
                <div class="card-header-copy">
                    <p class="badge badge-blue">Performance</p>
                    <h3>Attendance Trends</h3>
                    <p>Daily classification totals across the last 7 active days.</p>
                </div>
            </div>
            <div class="chart-container" style="padding: 24px;">
                <canvas id="attendanceChart" height="280"></canvas>
            </div>
            <script id="attendanceTrendData" type="application/json"><?= $trendJson ?></script>
        </section>

        <aside class="dash-utility-panels">
            <article class="card glass-card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="action-hub">
                    <?php if (can('employees.view')): ?>
                        <a href="/employees" class="action-item">
                            <div class="action-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                            <div class="action-label">Manage Employees</div>
                            <svg class="action-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (can('attendance.view')): ?>
                        <a href="/attendance" class="action-item">
                            <div class="action-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                            <div class="action-label">Attendance Logs</div>
                            <svg class="action-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    <?php endif; ?>

                    <?php if (can('payroll.view')): ?>
                        <a href="/payroll" class="action-item">
                            <div class="action-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/></svg></div>
                            <div class="action-label">Payroll Overview</div>
                            <svg class="action-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </article>

            <article class="card">
                <div class="card-header">
                    <h3>System Context</h3>
                </div>
                <div class="context-list">
                    <div class="context-row">
                        <span class="context-key">Server Node</span>
                        <span class="context-val text-blue">Primary-01</span>
                    </div>
                    <div class="context-row">
                        <span class="context-key">Sync Status</span>
                        <span class="context-val text-green">Online</span>
                    </div>
                    <div class="context-row">
                        <span class="context-key">Last Check</span>
                        <span class="context-val"><?= date('H:i') ?></span>
                    </div>
                </div>
            </article>
        </aside>
    </div>
</section>

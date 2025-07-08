<?php require_once '../includes/header.php'; ?>
<!-- Make sure Bootstrap Icons CDN is loaded in header.php: <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> -->
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-speedometer2 fs-4 text-primary"></i>
        <h2 class="fw-bold mb-0">Member Dashboard</h2>
    </div>
    <button class="create-new-btn mb-4"><i class="bi bi-plus-circle me-2"></i> Create New</button>
    <div class="summary-row">
        <div class="summary-card total">
            <div class="icon"><i class="bi bi-list-task"></i></div>
            <div class="fw-bold">12</div>
            <div class="label"><i class="bi bi-collection me-1"></i> Total Tasks</div>
        </div>
        <div class="summary-card low">
            <div class="icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="fw-bold">5</div>
            <div class="label"><i class="bi bi-arrow-down-left-circle me-1"></i> Low</div>
        </div>
        <div class="summary-card medium">
            <div class="icon"><i class="bi bi-exclamation-circle"></i></div>
            <div class="fw-bold">4</div>
            <div class="label"><i class="bi bi-exclamation-diamond me-1"></i> Medium</div>
        </div>
        <div class="summary-card high">
            <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="fw-bold">3</div>
            <div class="label"><i class="bi bi-exclamation-octagon me-1"></i> High</div>
        </div>
    </div>
    <div class="filter-bar">
        <span class="filter-pill active"><i class="bi bi-funnel-fill me-1"></i> All</span>
        <span class="filter-pill"><i class="bi bi-list-task me-1"></i> To Do</span>
        <span class="filter-pill"><i class="bi bi-hourglass-split me-1"></i> In Progress</span>
        <span class="filter-pill"><i class="bi bi-check2-circle me-1"></i> Completed</span>
        <span class="filter-pill"><i class="bi bi-exclamation-octagon me-1"></i> Overdue</span>
    </div>
    <div class="task-row">
        <div class="task-card">
            <div class="task-title"><i class="bi bi-brush me-2"></i> Design Landing Page</div>
            <div class="task-desc"><i class="bi bi-info-circle me-1"></i> Create a modern, responsive landing page for
                the new product launch.</div>
            <span class="badge badge-high"><i class="bi bi-exclamation-octagon me-1"></i> High</span>
            <span class="badge badge-todo"><i class="bi bi-list-task me-1"></i> To Do</span>
            <div class="task-meta"><i class="bi bi-calendar-event me-1"></i> Due: 2024-06-30 &nbsp;•&nbsp; <i
                    class="bi bi-person me-1"></i> Assigned: You</div>
            <div class="task-actions">
                <a href="#" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="#" title="Delete"><i class="bi bi-trash"></i></a>
            </div>
        </div>
        <div class="task-card">
            <div class="task-title"><i class="bi bi-journal-text me-2"></i> Update User Guide</div>
            <div class="task-desc"><i class="bi bi-info-circle me-1"></i> Revise the documentation to include new
                dashboard features.</div>
            <span class="badge badge-medium"><i class="bi bi-exclamation-diamond me-1"></i> Medium</span>
            <span class="badge badge-inprogress"><i class="bi bi-hourglass-split me-1"></i> In Progress</span>
            <div class="task-meta"><i class="bi bi-calendar-event me-1"></i> Due: 2024-07-05 &nbsp;•&nbsp; <i
                    class="bi bi-person me-1"></i> Assigned: You</div>
            <div class="task-actions">
                <a href="#" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="#" title="Delete"><i class="bi bi-trash"></i></a>
            </div>
        </div>
        <div class="task-card">
            <div class="task-title"><i class="bi bi-bug me-2"></i> Fix Login Bug</div>
            <div class="task-desc"><i class="bi bi-info-circle me-1"></i> Resolve the issue preventing some users from
                logging in.</div>
            <span class="badge badge-low"><i class="bi bi-arrow-down-left-circle me-1"></i> Low</span>
            <span class="badge badge-completed"><i class="bi bi-check2-circle me-1"></i> Completed</span>
            <div class="task-meta"><i class="bi bi-calendar-event me-1"></i> Due: 2024-06-20 &nbsp;•&nbsp; <i
                    class="bi bi-person me-1"></i> Assigned: You</div>
            <div class="task-actions">
                <a href="#" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="#" title="Delete"><i class="bi bi-trash"></i></a>
            </div>
        </div>
    </div>
    <button class="create-new-outline"><i class="bi bi-plus-lg me-2"></i> Create New Task</button>
</div>
<?php require_once '../includes/footer.php'; ?>
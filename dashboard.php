<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

try {
    // Get statistics
    $students_count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $exams_count = $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
    $results_count = $pdo->query("SELECT COUNT(*) FROM results")->fetchColumn();

    // Get recent activities
    $activities = $pdo->query("
        SELECT 
            'student' as type,
            CONCAT('New student registered: ', name) as description,
            created_at as date
        FROM students
        UNION ALL
        SELECT 
            'exam' as type,
            CONCAT('New exam created: ', title) as description,
            created_at as date
        FROM exams
        UNION ALL
        SELECT 
            'result' as type,
            CONCAT('Exam result submitted for ', s.name) as description,
            r.created_at as date
        FROM results r
        JOIN student_exams se ON r.student_exam_id = se.id
        JOIN students s ON se.student_id = s.id
        ORDER BY date DESC
        LIMIT 10
    ")->fetchAll();

    // Get upcoming exams
    $upcoming_exams = $pdo->query("
        SELECT e.*, s.name as subject_name 
        FROM exams e
        JOIN subjects s ON e.subject_id = s.id
        WHERE DATE(e.exam_date) >= CURDATE() 
        ORDER BY e.exam_date ASC 
        LIMIT 5
    ")->fetchAll();

    // Get recent exam results
    $recent_results = $pdo->query("
        SELECT 
            r.*,
            e.title as exam_title,
            s.name as student_name,
            s.roll_number,
            sub.name as subject_name
        FROM results r
        JOIN student_exams se ON r.student_exam_id = se.id
        JOIN students s ON se.student_id = s.id
        JOIN exams e ON se.exam_id = e.id
        JOIN subjects sub ON e.subject_id = sub.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ")->fetchAll();

} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("Database Error: " . $e->getMessage());
    $error_message = "An error occurred while loading the dashboard. Please try again later.";
    // Initialize empty arrays to prevent undefined variable errors
    $activities = [];
    $upcoming_exams = [];
    $recent_results = [];
    $students_count = 0;
    $exams_count = 0;
    $results_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management System - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }
        .stat-card {
            transition: transform 0.2s;
            border: none;
            border-radius: 10px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .quick-actions .btn {
            margin: 5px;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 10px;
        }
        .activity-icon.student {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .activity-icon.exam {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .activity-icon.result {
            background-color: #fff3e0;
            color: #f57c00;
        }
    </style>
</head>
<body>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger m-3">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <h4 class="text-white mb-4">Exam Management</h4>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="students.php" class="nav-link">
                                <i class="bi bi-people me-2"></i>
                                Students
                            </a>
                        </li>
                        <li>
                            <a href="exams.php" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i>
                                Exams
                            </a>
                        </li>
                        <li>
                            <a href="results.php" class="nav-link">
                                <i class="bi bi-graph-up me-2"></i>
                                Results
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="nav-link">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="quick-actions">
                            <a href="students.php" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>Add Student
                            </a>
                            <a href="exams.php" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>Create Exam
                            </a>
                            <a href="results.php" class="btn btn-info text-white">
                                <i class="bi bi-graph-up me-2"></i>View Results
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Students</h5>
                                        <h2 class="card-text"><?php echo $students_count; ?></h2>
                                    </div>
                                    <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Exams</h5>
                                        <h2 class="card-text"><?php echo $exams_count; ?></h2>
                                    </div>
                                    <i class="bi bi-journal-text" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Results Recorded</h5>
                                        <h2 class="card-text"><?php echo $results_count; ?></h2>
                                    </div>
                                    <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Activities</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($activities as $activity): ?>
                                    <div class="list-group-item border-0">
                                        <div class="d-flex align-items-center">
                                            <div class="activity-icon <?php echo $activity['type']; ?>">
                                                <?php if ($activity['type'] == 'student'): ?>
                                                    <i class="bi bi-person"></i>
                                                <?php elseif ($activity['type'] == 'exam'): ?>
                                                    <i class="bi bi-journal-text"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-graph-up"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="mb-0"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($activity['date'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Exams -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Upcoming Exams</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($upcoming_exams) > 0): ?>
                                    <div class="list-group">
                                        <?php foreach ($upcoming_exams as $exam): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($exam['title']); ?></h6>
                                                    <small class="text-muted">
                                                        Subject: <?php echo htmlspecialchars($exam['subject_name']); ?><br>
                                                        Date: <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?><br>
                                                        Time: <?php echo date('h:i A', strtotime($exam['start_time'])); ?> - 
                                                              <?php echo date('h:i A', strtotime($exam['end_time'])); ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-primary">
                                                    <?php 
                                                        $start = strtotime($exam['start_time']);
                                                        $end = strtotime($exam['end_time']);
                                                        $duration = round(($end - $start) / 60);
                                                        echo $duration . ' mins';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No upcoming exams</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Results -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Exam Results</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_results) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Exam</th>
                                            <th>Marks</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_results as $result): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($result['student_name']); ?>
                                                <br>
                                                <small class="text-muted">Roll No: <?php echo $result['roll_number']; ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($result['exam_title']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($result['subject_name']); ?></small>
                                            </td>
                                            <td><?php echo $result['marks_obtained']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $result['status'] == 'pass' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($result['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($result['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No exam results recorded yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
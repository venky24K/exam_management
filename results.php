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

// Initialize variables
$error_message = null;
$results = [];
$edit_result = null;

try {
    // Get all results with student and exam information
    try {
        $stmt = $pdo->query("
            SELECT 
                r.*,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                s.roll_number,
                c.name as class_name
            FROM results r
            LEFT JOIN students s ON r.student_roll = s.roll_number
            LEFT JOIN classes c ON r.class_id = c.id
            ORDER BY r.semester DESC, s.roll_number
        ");
        
        if ($stmt === false) {
            throw new Exception("Failed to execute query");
        }
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($results === false) {
            throw new Exception("Failed to fetch results");
        }
    } catch (PDOException $e) {
        throw new Exception("Error loading results: " . $e->getMessage());
    }

    // Get students for dropdown
    try {
        $stmt = $pdo->query("
            SELECT roll_number, CONCAT(first_name, ' ', last_name) as full_name 
            FROM students 
            ORDER BY roll_number
        ");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Error loading students: " . $e->getMessage());
    }

    // Get classes for dropdown
    try {
        $stmt = $pdo->query("
            SELECT id, name 
            FROM classes 
            WHERE status = 'Active'
            ORDER BY name
        ");
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Error loading classes: " . $e->getMessage());
    }

    // Handle Delete Operation
    if (isset($_POST['delete_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM results WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            header('Location: results.php?msg=deleted');
            exit();
        } catch (PDOException $e) {
            throw new Exception("Error deleting result: " . $e->getMessage());
        }
    }

    // Handle Add/Edit Operations
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO results (
                        student_roll, class_id, semester, total_marks, average_marks, grade, remarks
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['student_roll'],
                    $_POST['class_id'],
                    $_POST['semester'],
                    $_POST['total_marks'],
                    $_POST['average_marks'],
                    $_POST['grade'],
                    $_POST['remarks']
                ]);
                header('Location: results.php?msg=added');
                exit();
            } catch (PDOException $e) {
                throw new Exception("Error adding result: " . $e->getMessage());
            }
        } elseif ($_POST['action'] == 'edit') {
            try {
                $stmt = $pdo->prepare("
                    UPDATE results SET 
                        student_roll = ?,
                        class_id = ?,
                        semester = ?,
                        total_marks = ?,
                        average_marks = ?,
                        grade = ?,
                        remarks = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['student_roll'],
                    $_POST['class_id'],
                    $_POST['semester'],
                    $_POST['total_marks'],
                    $_POST['average_marks'],
                    $_POST['grade'],
                    $_POST['remarks'],
                    $_POST['id']
                ]);
                header('Location: results.php?msg=updated');
                exit();
            } catch (PDOException $e) {
                throw new Exception("Error updating result: " . $e->getMessage());
            }
        }
    }

    // Get result for editing if edit_id is set
    if (isset($_GET['edit_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM results WHERE id = ?");
            if ($stmt === false) {
                throw new Exception("Failed to prepare query");
            }
            
            if (!$stmt->execute([$_GET['edit_id']])) {
                throw new Exception("Failed to execute query");
            }
            
            $edit_result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$edit_result) {
                throw new Exception("Result not found!");
            }
        } catch (PDOException $e) {
            throw new Exception("Error loading result for editing: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Management - Exam System</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <h4 class="text-white mb-4">Exam Management</h4>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
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
                            <a href="results.php" class="nav-link active">
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
                    <h2>Results Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#resultModal">
                        <i class="bi bi-plus-circle me-2"></i>Add New Result
                    </button>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['msg']) {
                            case 'added':
                                echo 'Result added successfully!';
                                break;
                            case 'updated':
                                echo 'Result updated successfully!';
                                break;
                            case 'deleted':
                                echo 'Result deleted successfully!';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Results Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Roll Number</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Semester</th>
                                        <th>Total Marks</th>
                                        <th>Average Marks</th>
                                        <th>Grade</th>
                                        <th>Remarks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['student_roll']); ?></td>
                                        <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['semester']); ?></td>
                                        <td><?php echo htmlspecialchars($result['total_marks']); ?></td>
                                        <td><?php echo htmlspecialchars($result['average_marks']); ?></td>
                                        <td><?php echo htmlspecialchars($result['grade']); ?></td>
                                        <td><?php echo htmlspecialchars($result['remarks']); ?></td>
                                        <td>
                                            <a href="?edit_id=<?php echo $result['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="deleteResult(<?php echo $result['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $edit_result ? 'Edit Result' : 'Add New Result'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_result ? 'edit' : 'add'; ?>">
                        <?php if ($edit_result): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_result['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <select class="form-select" name="student_roll" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo htmlspecialchars($student['roll_number']); ?>" 
                                            <?php echo (isset($edit_result) && $edit_result['student_roll'] == $student['roll_number']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['roll_number'] . ' - ' . $student['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo htmlspecialchars($class['id']); ?>" 
                                            <?php echo (isset($edit_result) && $edit_result['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="number" class="form-control" name="semester" required min="1"
                                value="<?php echo $edit_result ? $edit_result['semester'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" class="form-control" name="total_marks" required min="0" step="0.01"
                                value="<?php echo $edit_result ? $edit_result['total_marks'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Average Marks</label>
                            <input type="number" class="form-control" name="average_marks" required min="0" step="0.01"
                                value="<?php echo $edit_result ? $edit_result['average_marks'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grade</label>
                            <input type="text" class="form-control" name="grade" maxlength="5"
                                value="<?php echo $edit_result ? $edit_result['grade'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <input type="text" class="form-control" name="remarks" maxlength="100"
                                value="<?php echo $edit_result ? $edit_result['remarks'] : ''; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_id" id="deleteId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show add/edit modal if edit_id is present
        <?php if (isset($_GET['edit_id'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('resultModal'));
            modal.show();
        });
        <?php endif; ?>

        // Handle delete confirmation
        function deleteResult(id) {
            if (confirm('Are you sure you want to delete this result?')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html> 
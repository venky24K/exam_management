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
    // Debug database connection
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }

    // Get all subjects for the dropdown
    try {
        $subjects = $pdo->query("
            SELECT * FROM subjects ORDER BY name
        ")->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error loading subjects: " . $e->getMessage());
    }

    // Handle Delete Operation
    if (isset($_POST['delete_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            header('Location: exams.php?msg=deleted');
            exit();
        } catch (PDOException $e) {
            throw new Exception("Error deleting exam: " . $e->getMessage());
        }
    }

    // Handle Add/Edit Operations
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        try {
            if ($_POST['action'] == 'add') {
                $stmt = $pdo->prepare("
                    INSERT INTO exams (
                        subject_id, exam_type, exam_date, start_time, 
                        end_time, room_no
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['subject_id'],
                    $_POST['exam_type'],
                    $_POST['exam_date'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['room_no']
                ]);
                header('Location: exams.php?msg=added');
                exit();
            } elseif ($_POST['action'] == 'edit') {
                $stmt = $pdo->prepare("
                    UPDATE exams SET 
                        subject_id = ?, 
                        exam_type = ?, 
                        exam_date = ?, 
                        start_time = ?, 
                        end_time = ?, 
                        room_no = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['subject_id'],
                    $_POST['exam_type'],
                    $_POST['exam_date'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['room_no'],
                    $_POST['id']
                ]);
                header('Location: exams.php?msg=updated');
                exit();
            }
        } catch (PDOException $e) {
            throw new Exception("Error processing form: " . $e->getMessage());
        }
    }

    // Get all exams with their related information
    try {
        $stmt = $pdo->query("
            SELECT 
                e.*,
                s.name as subject_name
            FROM exams e
            LEFT JOIN subjects s ON e.subject_id = s.id
            ORDER BY e.exam_date DESC, e.start_time ASC
        ");
        $exams = $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("Error loading exams: " . $e->getMessage());
    }

    // Get exam for editing if edit_id is set
    $edit_exam = null;
    if (isset($_GET['edit_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
            $stmt->execute([$_GET['edit_id']]);
            $edit_exam = $stmt->fetch();
            if (!$edit_exam) {
                throw new Exception("Exam not found!");
            }
        } catch (PDOException $e) {
            throw new Exception("Error loading exam for editing: " . $e->getMessage());
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
    <title>Exam Management - Exam System</title>
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
                            <a href="exams.php" class="nav-link active">
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
                    <h2>Exam Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#examModal">
                        <i class="bi bi-plus-circle me-2"></i>Add New Exam
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
                                echo 'Exam added successfully!';
                                break;
                            case 'updated':
                                echo 'Exam updated successfully!';
                                break;
                            case 'deleted':
                                echo 'Exam deleted successfully!';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Exams Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Room No</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($exams as $exam): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['exam_type']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['start_time'] . ' - ' . $exam['end_time']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['room_no']); ?></td>
                                        <td>
                                            <a href="?edit_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="deleteExam(<?php echo $exam['id']; ?>)">
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

    <!-- Add/Edit Exam Modal -->
    <div class="modal fade" id="examModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $edit_exam ? 'Edit Exam' : 'Add New Exam'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_exam ? 'edit' : 'add'; ?>">
                        <?php if ($edit_exam): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_exam['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>" 
                                            <?php echo ($edit_exam && $edit_exam['subject_id'] == $subject['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($subject['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Exam Type</label>
                            <select class="form-select" name="exam_type" required>
                                <option value="">Select Type</option>
                                <option value="Internal" <?php echo ($edit_exam && $edit_exam['exam_type'] == 'Internal') ? 'selected' : ''; ?>>Internal</option>
                                <option value="External" <?php echo ($edit_exam && $edit_exam['exam_type'] == 'External') ? 'selected' : ''; ?>>External</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Exam Date</label>
                            <input type="date" class="form-control" name="exam_date" required
                                value="<?php echo $edit_exam ? $edit_exam['exam_date'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time" required
                                value="<?php echo $edit_exam ? $edit_exam['start_time'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" class="form-control" name="end_time" required
                                value="<?php echo $edit_exam ? $edit_exam['end_time'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room No</label>
                            <input type="text" class="form-control" name="room_no" required maxlength="10"
                                value="<?php echo $edit_exam ? $edit_exam['room_no'] : ''; ?>">
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
            var modal = new bootstrap.Modal(document.getElementById('examModal'));
            modal.show();
        });
        <?php endif; ?>

        // Handle delete confirmation
        function deleteExam(id) {
            if (confirm('Are you sure you want to delete this exam?')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html> 
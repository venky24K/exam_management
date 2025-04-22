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
$students = [];
$edit_student = null;

try {
    // Get all students
    try {
        $stmt = $pdo->query("
            SELECT 
                *,
                CONCAT(first_name, ' ', last_name) as full_name
            FROM students
            ORDER BY roll_number
        ");
        
        if ($stmt === false) {
            throw new Exception("Failed to execute query");
        }
        
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($students === false) {
            throw new Exception("Failed to fetch results");
        }
    } catch (PDOException $e) {
        throw new Exception("Error loading students: " . $e->getMessage());
    }

    // Handle Delete Operation
    if (isset($_POST['delete_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM students WHERE roll_number = ?");
            $stmt->execute([$_POST['delete_id']]);
            header('Location: students.php?msg=deleted');
            exit();
        } catch (PDOException $e) {
            throw new Exception("Error deleting student: " . $e->getMessage());
        }
    }

    // Handle Add/Edit Operations
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        // Check if roll number already exists
        if ($_POST['action'] == 'add' || ($_POST['action'] == 'edit' && $_POST['roll_number'] != $_POST['old_roll_number'])) {
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE roll_number = ?");
            $check_stmt->execute([$_POST['roll_number']]);
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception("Roll number already exists!");
            }
        }

        // Check if email already exists
        if (!empty($_POST['email'])) {
            if ($_POST['action'] == 'add' || ($_POST['action'] == 'edit' && $_POST['email'] != $_POST['old_email'])) {
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
                $check_stmt->execute([$_POST['email']]);
                if ($check_stmt->fetchColumn() > 0) {
                    throw new Exception("Email already exists!");
                }
            }
        }

        if ($_POST['action'] == 'add') {
            $stmt = $pdo->prepare("
                INSERT INTO students (
                    roll_number, class_id, first_name, last_name, 
                    email, phone
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                trim($_POST['roll_number']),
                $_POST['class_id'],
                trim($_POST['first_name']),
                trim($_POST['last_name']),
                !empty($_POST['email']) ? trim($_POST['email']) : null,
                !empty($_POST['phone']) ? trim($_POST['phone']) : null
            ]);
            header('Location: students.php?msg=added');
            exit();
        } elseif ($_POST['action'] == 'edit') {
            $stmt = $pdo->prepare("
                UPDATE students SET 
                    roll_number = ?,
                    class_id = ?, 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?
                WHERE roll_number = ?
            ");
            $stmt->execute([
                trim($_POST['roll_number']),
                $_POST['class_id'],
                trim($_POST['first_name']),
                trim($_POST['last_name']),
                !empty($_POST['email']) ? trim($_POST['email']) : null,
                !empty($_POST['phone']) ? trim($_POST['phone']) : null,
                $_POST['old_roll_number']
            ]);
            header('Location: students.php?msg=updated');
            exit();
        }
    }

    // Get student for editing if edit_id is set
    $edit_student = null;
    if (isset($_GET['edit_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_number = ?");
            if ($stmt === false) {
                throw new Exception("Failed to prepare query");
            }
            
            if (!$stmt->execute([$_GET['edit_id']])) {
                throw new Exception("Failed to execute query");
            }
            
            $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$edit_student) {
                throw new Exception("Student not found!");
            }
        } catch (PDOException $e) {
            throw new Exception("Error loading student for editing: " . $e->getMessage());
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
    <title>Student Management - Exam System</title>
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
                            <a href="students.php" class="nav-link active">
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
                    <h2>Student Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal">
                        <i class="bi bi-plus-circle me-2"></i>Add New Student
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
                                echo 'Student added successfully!';
                                break;
                            case 'updated':
                                echo 'Student updated successfully!';
                                break;
                            case 'deleted':
                                echo 'Student deleted successfully!';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Roll Number</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Class ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_id']); ?></td>
                                        <td>
                                            <a href="?edit_id=<?php echo urlencode($student['roll_number']); ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="deleteStudent('<?php echo htmlspecialchars($student['roll_number']); ?>')">
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

    <!-- Add/Edit Student Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_student ? 'edit' : 'add'; ?>">
                        <?php if ($edit_student): ?>
                            <input type="hidden" name="old_roll_number" value="<?php echo $edit_student['roll_number']; ?>">
                            <input type="hidden" name="old_email" value="<?php echo $edit_student['email']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Roll Number</label>
                            <input type="text" class="form-control" name="roll_number" required maxlength="20"
                                value="<?php echo $edit_student ? $edit_student['roll_number'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required maxlength="50"
                                value="<?php echo $edit_student ? $edit_student['first_name'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required maxlength="50"
                                value="<?php echo $edit_student ? $edit_student['last_name'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" maxlength="100"
                                value="<?php echo $edit_student ? $edit_student['email'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" maxlength="20"
                                value="<?php echo $edit_student ? $edit_student['phone'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control" name="class_id" required maxlength="11"
                                value="<?php echo $edit_student ? $edit_student['class_id'] : ''; ?>">
                            <div class="form-text">Enter the class ID number</div>
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
            var modal = new bootstrap.Modal(document.getElementById('studentModal'));
            modal.show();
        });
        <?php endif; ?>

        // Handle delete confirmation
        function deleteStudent(rollNumber) {
            if (confirm('Are you sure you want to delete this student?')) {
                document.getElementById('deleteId').value = rollNumber;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html> 
<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Fetch classes
$classes_sql = "SELECT * FROM classes ORDER BY class_name";
$classes_result = mysqli_query($conn, $classes_sql);

// Fetch exams with class names
$exams_sql = "SELECT e.*, c.class_name 
              FROM exams e 
              JOIN classes c ON e.class_id = c.id 
              ORDER BY e.exam_date DESC";
$exams_result = mysqli_query($conn, $exams_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .wrapper{ padding: 20px; }
        .nav-link{ color: #333; }
        .nav-link:hover{ color: #007bff; }
        .card{ margin-bottom: 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Exam Management System</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_classes.php">Manage Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_exams.php">Manage Exams</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Classes</h5>
                            <p class="card-text">Total Classes: <?php echo mysqli_num_rows($classes_result); ?></p>
                            <a href="manage_classes.php" class="btn btn-primary">Manage Classes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Exams</h5>
                            <p class="card-text">Total Exams: <?php echo mysqli_num_rows($exams_result); ?></p>
                            <a href="manage_exams.php" class="btn btn-primary">Manage Exams</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Upcoming Exams</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Exam Name</th>
                                            <th>Class</th>
                                            <th>Date</th>
                                            <th>Duration</th>
                                            <th>Total Marks</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($exam = mysqli_fetch_assoc($exams_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                            <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($exam['exam_date'])); ?></td>
                                            <td><?php echo $exam['duration']; ?> minutes</td>
                                            <td><?php echo $exam['total_marks']; ?></td>
                                            <td>
                                                <a href="edit_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 
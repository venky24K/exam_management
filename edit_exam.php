<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$exam_name = $exam_date = $duration = $total_marks = "";
$class_id = "";
$exam_name_err = $exam_date_err = $duration_err = $total_marks_err = $class_id_err = "";

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    $id = $_POST["id"];
    
    // Validate exam name
    if(empty(trim($_POST["exam_name"]))){
        $exam_name_err = "Please enter an exam name.";
    } else{
        $exam_name = trim($_POST["exam_name"]);
    }
    
    // Validate class
    if(empty(trim($_POST["class_id"]))){
        $class_id_err = "Please select a class.";
    } else{
        $class_id = trim($_POST["class_id"]);
    }
    
    // Validate exam date
    if(empty(trim($_POST["exam_date"]))){
        $exam_date_err = "Please enter an exam date.";
    } else{
        $exam_date = trim($_POST["exam_date"]);
    }
    
    // Validate duration
    if(empty(trim($_POST["duration"]))){
        $duration_err = "Please enter the duration in minutes.";
    } else{
        $duration = trim($_POST["duration"]);
    }
    
    // Validate total marks
    if(empty(trim($_POST["total_marks"]))){
        $total_marks_err = "Please enter total marks.";
    } else{
        $total_marks = trim($_POST["total_marks"]);
    }
    
    // Check input errors before updating the database
    if(empty($exam_name_err) && empty($class_id_err) && empty($exam_date_err) && empty($duration_err) && empty($total_marks_err)){
        $sql = "UPDATE exams SET class_id=?, exam_name=?, exam_date=?, duration=?, total_marks=? WHERE id=?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "issiii", $param_class_id, $param_exam_name, $param_exam_date, $param_duration, $param_total_marks, $param_id);
            
            $param_class_id = $class_id;
            $param_exam_name = $exam_name;
            $param_exam_date = $exam_date;
            $param_duration = $duration;
            $param_total_marks = $total_marks;
            $param_id = $id;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: manage_exams.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
} else{
    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT e.*, c.class_name 
                FROM exams e 
                JOIN classes c ON e.class_id = c.id 
                WHERE e.id = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id;
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) == 1){
                    $row = mysqli_fetch_array($result);
                    
                    $exam_name = $row["exam_name"];
                    $class_id = $row["class_id"];
                    $exam_date = $row["exam_date"];
                    $duration = $row["duration"];
                    $total_marks = $row["total_marks"];
                } else{
                    header("location: manage_exams.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        mysqli_stmt_close($stmt);
    } else{
        header("location: manage_exams.php");
        exit();
    }
}

// Fetch all classes for dropdown
$classes_sql = "SELECT * FROM classes ORDER BY class_name";
$classes_result = mysqli_query($conn, $classes_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Exam - Exam Management System</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_classes.php">Manage Classes</a>
                    </li>
                    <li class="nav-item active">
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
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Edit Exam</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <div class="form-group">
                                    <label>Class</label>
                                    <select name="class_id" class="form-control <?php echo (!empty($class_id_err)) ? 'is-invalid' : ''; ?>">
                                        <option value="">Select Class</option>
                                        <?php while($class = mysqli_fetch_assoc($classes_result)): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo ($class_id == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <span class="invalid-feedback"><?php echo $class_id_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Exam Name</label>
                                    <input type="text" name="exam_name" class="form-control <?php echo (!empty($exam_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $exam_name; ?>">
                                    <span class="invalid-feedback"><?php echo $exam_name_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Exam Date</label>
                                    <input type="date" name="exam_date" class="form-control <?php echo (!empty($exam_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $exam_date; ?>">
                                    <span class="invalid-feedback"><?php echo $exam_date_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Duration (minutes)</label>
                                    <input type="number" name="duration" class="form-control <?php echo (!empty($duration_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $duration; ?>">
                                    <span class="invalid-feedback"><?php echo $duration_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Total Marks</label>
                                    <input type="number" name="total_marks" class="form-control <?php echo (!empty($total_marks_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $total_marks; ?>">
                                    <span class="invalid-feedback"><?php echo $total_marks_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary" value="Update Exam">
                                    <a href="manage_exams.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
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
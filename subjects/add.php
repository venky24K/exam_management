<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = $_POST['subject_code'];
    $name = $_POST['name'];
    $class_id = $_POST['class_id'];

    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, name, class_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $subject_code, $name, $class_id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

$classes = $conn->query("SELECT * FROM classes");
?>

<h2>Add Subject</h2>
<form method="post">
    <label>Subject Code:</label><br>
    <input type="text" name="subject_code" required><br><br>

    <label>Subject Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Class:</label><br>
    <select name="class_id" required>
        <?php while ($c = $classes->fetch_assoc()) {
            echo "<option value='{$c['id']}'>{$c['name']} - Sem {$c['semester']} - Sec {$c['section']}</option>";
        } ?>
    </select
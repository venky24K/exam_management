<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $department_id = $_POST['department_id'];
    $semester = $_POST['semester'];
    $section = $_POST['section'];

    $stmt = $conn->prepare("INSERT INTO classes (name, department_id, semester, section)
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $name, $department_id, $semester, $section);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

$departments = $conn->query("SELECT * FROM departments");
?>

<h2>Add Class</h2>
<form method="post">
    <label>Class Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Department:</label><br>
    <select name="department_id" required>
        <?php while ($d = $departments->fetch_assoc()) {
            echo "<option value='{$d['id']}'>{$d['name']}</option>";
        } ?>
    </select><br><br>

    <label>Semester:</label><br>
    <input type="number" name="semester" min="1" max="10" required><br><br>

    <label>Section:</label><br>
    <input type="text" name="section" maxlength="1" required><br><br>

    <button type="submit">Save</button>
</form>

<?php include('../includes/footer.php'); ?>
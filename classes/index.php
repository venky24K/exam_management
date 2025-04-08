<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

$sql = "SELECT classes.*, departments.name AS department_name 
        FROM classes 
        JOIN departments ON classes.department_id = departments.id";
$result = $conn->query($sql);
?>

<h2>All Classes</h2>
<a href="add.php">+ Add Class</a><br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>Class Name</th>
        <th>Department</th>
        <th>Semester</th>
        <th>Section</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['name'] ?></td>
        <td><?= $row['department_name'] ?></td>
        <td><?= $row['semester'] ?></td>
        <td><?= $row['section'] ?></td>
        <td>
            <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this class?')">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include('../includes/footer.php'); ?>
<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include 'connection.php';

// Handle Add Constituency
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_constituency'])) {
    $constituency_name = mysqli_real_escape_string($conn, $_POST['constituency_name']);

    $sql = "INSERT INTO constituencies (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $constituency_name);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_constituencies.php");
    exit;
}

// Handle Delete Constituency
if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];

    $sql = "DELETE FROM constituencies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_constituencies.php");
    exit;
}

// Fetch all constituencies
$sql = "SELECT * FROM constituencies";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Constituencies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="navbar navbar-dark bg-dark px-3">
    <span class="navbar-brand">Admin Dashboard</span>
    <a href="logout.php" class="btn btn-light">Logout</a>
</div>

<div class="container mt-5">
    <h2>Manage Constituencies</h2>

    <!-- Add Form -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Add New Constituency</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="manage_constituencies.php">
                <div class="form-group">
                    <label for="constituency_name">Constituency Name</label>
                    <input type="text" class="form-control" name="constituency_name" id="constituency_name" required>
                </div>
                <button type="submit" name="add_constituency" class="btn btn-primary mt-3">Add</button>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card mt-5">
        <div class="card-header">
            <h5>Existing Constituencies</h5>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Constituency ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <a href="manage_constituencies.php?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this constituency?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No constituencies found. Add one above.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="bg-light text-center py-3 mt-5">
    <small>&copy; 2025 Online Voting System. All rights reserved.</small>
</footer>
</body>
</html>

<?php
$conn->close();
?>

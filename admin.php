<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include 'connection.php';

$admin_name = $admin_phone = '';

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];

    $stmt = $conn->prepare("SELECT name, phone FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($admin_name, $admin_phone);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    body {
    background: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg') no-repeat center center fixed;
    background-size: cover;
    color: var(--bs-body-color);
    font-family: Arial, sans-serif;
}

/* Cards background with semi-transparent white and blur */
.admin-card, .card-flash {
    background-color: rgba(255, 255, 255, 0.85); /* White with opacity */
    border-radius: 15px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    padding: 30px 25px;
    color: #222;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    transition: 0.3s ease-in-out;
}

/* Hover effect to lift card */
.admin-card:hover, .card-flash:hover {
    transform: translateY(-7px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.3);
}

/* Icon size */
.admin-card .bi, .card-flash .bi {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #007bff;
}

/* Titles inside cards */
.card-flash h5, .admin-card h3 {
    font-weight: 700;
    margin-bottom: 15px;
}

/* Text inside cards */
.admin-card p, .card-flash p {
    font-size: 1.1rem;
    line-height: 1.4;
}

/* Button style override */
.card-flash .btn-light, .admin-card .btn-light {
    color: #007bff;
    background-color: white;
    border: none;
    font-weight: 600;
    padding: 8px 18px;
}

.card-flash .btn-light:hover, .admin-card .btn-light:hover {
    background-color: #e2e6ea;
}

/* Navbar */
.navbar {
    background-color: rgba(0, 123, 255, 0.85) !important; /* Slightly transparent blue */
    color: white;
    padding: 0.5rem 1rem;
}

/* Footer */
footer.bg-light {
    background-color: rgba(255, 255, 255, 0.85) !important;
    color: #333;
    padding: 12px 0;
    font-size: 0.9rem;
    box-shadow: 0 -1px 10px rgba(0,0,0,0.1);
}

/* Dark mode overrides */
@media (prefers-color-scheme: dark) {
    body {
        background-color: #121212;
        color: #f1f1f1;
    }

    .admin-card, .card-flash {
        background-color: rgba(30, 30, 30, 0.85);
        color: #eee;
        box-shadow: 0 8px 24px rgba(0,0,0,0.8);
    }

    .admin-card:hover, .card-flash:hover {
        box-shadow: 0 16px 40px rgba(0,0,0,1);
    }

    .card-flash .btn-light, .admin-card .btn-light {
        background-color: #222;
        color: #66aaff;
    }

    .card-flash .btn-light:hover, .admin-card .btn-light:hover {
        background-color: #444;
    }

    .navbar {
        background-color: rgba(0, 123, 255, 0.75) !important;
    }

    footer.bg-light {
        background-color: rgba(30, 30, 30, 0.85) !important;
        color: #eee;
    }
}
</style>
</head>
<body>
<div class="navbar navbar-dark" style="background-color: #007bff; padding: 0.5rem 1rem;">
    <span class="navbar-brand">Admin Dashboard</span>
    <a href="logout.php" class="btn btn-light">Logout</a>
</div>

    <div class="container mt-5">
        <!-- Admin Info Card -->
        <div class="d-flex justify-content-center mb-5">
            <div class="card admin-card text-center p-4" style="max-width: 500px; width: 100%;">
                <i class="bi bi-person-circle text-primary mb-3"></i>
                <h3 class="mb-3">Welcome, Admin</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($admin_name); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($admin_phone); ?></p>
            </div>
        </div>

        <!-- Flash Card Dashboard Options -->
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card card-flash p-4 h-100">
                    <i class="bi bi-plus-square-fill"></i>
                    <h5 class="card-title">Create Election</h5>
                    <p>Start a new election by providing title and schedule.</p>
                    <a href="create_election.php" class="btn btn-light">Go</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-flash p-4 h-100">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <h5 class="card-title">View Results</h5>
                    <p>Check the final results of completed elections.</p>
                    <a href="results.php" class="btn btn-light">Go</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-flash p-4 h-100">
                    <i class="bi bi-people-fill"></i>
                    <h5 class="card-title">Manage Voters</h5>
                    <p>Add, update, or delete voter information.</p>
                    <a href="manage_voters.php" class="btn btn-light">Go</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-flash p-4 h-100">
                    <i class="bi bi-person-lines-fill"></i>
                    <h5 class="card-title">Manage Candidates</h5>
                    <p>Update candidate info and assign them to elections.</p>
                    <a href="manage_candidates.php" class="btn btn-light">Go</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-flash p-4 h-100">
                    <i class="bi bi-geo-alt-fill"></i>
                    <h5 class="card-title">Manage Constituencies</h5>
                    <p>Create or update the list of constituencies.</p>
                    <a href="constituency.php" class="btn btn-light">Go</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light text-center py-3 mt-5">
        <small>&copy; 2025 Online Voting System. All rights reserved.</small>
    </footer>
</body>
</html>

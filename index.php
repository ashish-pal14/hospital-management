<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$userRole = $_SESSION['user_role'];
$userName = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">Hospital Management System</span>
            <div class="d-flex">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($userName); ?> (<?php echo htmlspecialchars($userRole); ?>)</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Dashboard Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Total Patients</h5>
                        <h2 id="totalPatients" class="display-4">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Doctors</h5>
                        <h2 id="totalDoctors" class="display-4">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Today's Appointments</h5>
                        <h2 id="todayAppointments" class="display-4">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Quick Actions (Role‑based) -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($userRole == 'admin' || $userRole == 'receptionist'): ?>
                                <a href="patients.php" class="btn btn-primary">Manage Patients</a>
                                <a href="appointments.php" class="btn btn-info">Manage Appointments</a>
                            <?php endif; ?>
                            <?php if ($userRole == 'admin'): ?>
                                <a href="doctors.php" class="btn btn-success">Manage Doctors</a>
                            <?php endif; ?>
                            <?php if ($userRole == 'doctor'): ?>
                                <a href="appointments.php" class="btn btn-info">My Appointments</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments List -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Appointments</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentAppointments"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load dashboard stats using AJAX (real‑time API call)
        async function loadDashboard() {
            try {
                const response = await fetch('/api/dashboard.php', {
                    credentials: 'include'
                });
                const data = await response.json();
                
                document.getElementById('totalPatients').textContent = data.total_patients || 0;
                document.getElementById('totalDoctors').textContent = data.total_doctors || 0;
                document.getElementById('todayAppointments').textContent = data.today_appointments || 0;
                
                const appointmentsHtml = data.recent_appointments.map(apt => `
                    <div class="mb-2 p-2 border rounded">
                        <strong>${apt.patient_name}</strong> with Dr. ${apt.doctor_name}<br>
                        <small>${new Date(apt.appointment_date).toLocaleString()} - ${apt.status}</small>
                    </div>
                `).join('');
                document.getElementById('recentAppointments').innerHTML = appointmentsHtml || '<p>No recent appointments</p>';
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }
        
        // Auto‑refresh every 30 seconds for real‑time feel
        loadDashboard();
        setInterval(loadDashboard, 30000);
    </script>
</body>
</html>

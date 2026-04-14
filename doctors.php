<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">Hospital Management - Doctors</span>
            <div>
                <a href="index.php" class="btn btn-outline-light me-2">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Doctor Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#doctorModal" onclick="resetForm()">Add New Doctor</button>
            </div>
            <div class="card-body">
                <table id="doctorsTable" class="table table-striped">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Specialization</th><th>Phone</th><th>Email</th><th>Schedule</th><th>Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Doctor Modal -->
    <div class="modal fade" id="doctorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Doctor Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="doctorForm">
                        <input type="hidden" id="doctorId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="schedule" class="form-label">Schedule</label>
                            <textarea class="form-control" id="schedule" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveDoctor()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        let dataTable;
        
        async function loadDoctors() {
            const response = await fetch('/api/doctors.php', { credentials: 'include' });
            const doctors = await response.json();
            
            if (dataTable) {
                dataTable.clear().destroy();
            }
            
            $('#doctorsTable tbody').html(doctors.map(d => `
                <tr>
                    <td>${d.id}</td>
                    <td>${escapeHtml(d.name)}</td>
                    <td>${escapeHtml(d.specialization)}</td>
                    <td>${escapeHtml(d.phone)}</td>
                    <td>${escapeHtml(d.email)}</td>
                    <td>${escapeHtml(d.schedule || '')}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editDoctor(${d.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteDoctor(${d.id})">Delete</button>
                    </td>
                </tr>
            `).join(''));
            
            dataTable = $('#doctorsTable').DataTable();
        }
        
        async function saveDoctor() {
            const id = document.getElementById('doctorId').value;
            const data = {
                name: document.getElementById('name').value,
                specialization: document.getElementById('specialization').value,
                phone: document.getElementById('phone').value,
                email: document.getElementById('email').value,
                schedule: document.getElementById('schedule').value
            };
            
            const url = id ? `/api/doctors.php?id=${id}` : '/api/doctors.php';
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('doctorModal')).hide();
                loadDoctors();
                resetForm();
            } else {
                alert('Error saving doctor');
            }
        }
        
        async function editDoctor(id) {
            const response = await fetch(`/api/doctors.php?id=${id}`, { credentials: 'include' });
            const doctor = await response.json();
            
            document.getElementById('doctorId').value = doctor.id;
            document.getElementById('name').value = doctor.name;
            document.getElementById('specialization').value = doctor.specialization;
            document.getElementById('phone').value = doctor.phone;
            document.getElementById('email').value = doctor.email;
            document.getElementById('schedule').value = doctor.schedule;
            
            new bootstrap.Modal(document.getElementById('doctorModal')).show();
        }
        
        async function deleteDoctor(id) {
            if (confirm('Are you sure? This will also remove related appointments.')) {
                const response = await fetch(`/api/doctors.php?id=${id}`, {
                    method: 'DELETE',
                    credentials: 'include'
                });
                if (response.ok) loadDoctors();
            }
        }
        
        function resetForm() {
            document.getElementById('doctorId').value = '';
            document.getElementById('doctorForm').reset();
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
        
        loadDoctors();
    </script>
</body>
</html>

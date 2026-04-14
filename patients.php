<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">Hospital Management - Patients</span>
            <div>
                <a href="index.php" class="btn btn-outline-light me-2">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Patient Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#patientModal" onclick="resetForm()">Add New Patient</button>
            </div>
            <div class="card-body">
                <table id="patientsTable" class="table table-striped">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Phone</th><th>Address</th><th>Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Patient Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Patient Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="patientForm">
                        <input type="hidden" id="patientId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" required>
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-control" id="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="savePatient()">Save</button>
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
        
        async function loadPatients() {
            const response = await fetch('/api/patients.php', { credentials: 'include' });
            const patients = await response.json();
            
            if (dataTable) {
                dataTable.clear().destroy();
            }
            
            $('#patientsTable tbody').html(patients.map(p => `
                <tr>
                    <td>${p.id}</td>
                    <td>${p.name}</td>
                    <td>${p.age}</td>
                    <td>${p.gender}</td>
                    <td>${p.phone}</td>
                    <td>${p.address || ''}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editPatient(${p.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deletePatient(${p.id})">Delete</button>
                    </td>
                </tr>
            `).join(''));
            
            dataTable = $('#patientsTable').DataTable();
        }
        
        async function savePatient() {
            const id = document.getElementById('patientId').value;
            const data = {
                name: document.getElementById('name').value,
                age: document.getElementById('age').value,
                gender: document.getElementById('gender').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value
            };
            
            const url = id ? `/api/patients.php?id=${id}` : '/api/patients.php';
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('patientModal')).hide();
                loadPatients();
                resetForm();
            } else {
                alert('Error saving patient');
            }
        }
        
        async function editPatient(id) {
            const response = await fetch(`/api/patients.php?id=${id}`, { credentials: 'include' });
            const patient = await response.json();
            
            document.getElementById('patientId').value = patient.id;
            document.getElementById('name').value = patient.name;
            document.getElementById('age').value = patient.age;
            document.getElementById('gender').value = patient.gender;
            document.getElementById('phone').value = patient.phone;
            document.getElementById('address').value = patient.address;
            
            new bootstrap.Modal(document.getElementById('patientModal')).show();
        }
        
        async function deletePatient(id) {
            if (confirm('Are you sure?')) {
                const response = await fetch(`/api/patients.php?id=${id}`, {
                    method: 'DELETE',
                    credentials: 'include'
                });
                if (response.ok) loadPatients();
            }
        }
        
        function resetForm() {
            document.getElementById('patientId').value = '';
            document.getElementById('patientForm').reset();
        }
        
        loadPatients();
    </script>
</body>
</html>

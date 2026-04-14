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
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">Hospital Management - Appointments</span>
            <div>
                <a href="index.php" class="btn btn-outline-light me-2">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Appointments</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointmentModal" onclick="resetForm()">New Appointment</button>
            </div>
            <div class="card-body">
                <table id="appointmentsTable" class="table table-striped">
                    <thead>
                        <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date & Time</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for create/edit -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="appointmentForm">
                        <input type="hidden" id="appointmentId">
                        <div class="mb-3">
                            <label>Patient</label>
                            <select id="patient_id" class="form-control" required></select>
                        </div>
                        <div class="mb-3">
                            <label>Doctor</label>
                            <select id="doctor_id" class="form-control" required></select>
                        </div>
                        <div class="mb-3">
                            <label>Date & Time</label>
                            <input type="datetime-local" id="appointment_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select id="status" class="form-control">
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea id="notes" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveAppointment()">Save</button>
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
        let patientsList = [];
        let doctorsList = [];

        async function loadDropdowns() {
            // Load patients
            let resp = await fetch('/api/patients.php', { credentials: 'include' });
            patientsList = await resp.json();
            const patientSelect = document.getElementById('patient_id');
            patientSelect.innerHTML = '<option value="">Select Patient</option>' + 
                patientsList.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');

            // Load doctors
            resp = await fetch('/api/doctors.php', { credentials: 'include' });
            doctorsList = await resp.json();
            const doctorSelect = document.getElementById('doctor_id');
            doctorSelect.innerHTML = '<option value="">Select Doctor</option>' + 
                doctorsList.map(d => `<option value="${d.id}">${escapeHtml(d.name)} (${escapeHtml(d.specialization)})</option>`).join('');
        }

        async function loadAppointments() {
            const response = await fetch('/api/appointments.php', { credentials: 'include' });
            const appointments = await response.json();
            
            if (dataTable) dataTable.clear().destroy();
            
            $('#appointmentsTable tbody').html(appointments.map(apt => `
                <tr>
                    <td>${apt.id}</td>
                    <td>${escapeHtml(apt.patient_name)}</td>
                    <td>${escapeHtml(apt.doctor_name)}</td>
                    <td>${new Date(apt.appointment_date).toLocaleString()}</td>
                    <td><span class="badge bg-${apt.status === 'scheduled' ? 'primary' : (apt.status === 'completed' ? 'success' : 'danger')}">${apt.status}</span></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editAppointment(${apt.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteAppointment(${apt.id})">Delete</button>
                    </td>
                </tr>
            `).join(''));
            
            dataTable = $('#appointmentsTable').DataTable();
        }

        async function saveAppointment() {
            const id = document.getElementById('appointmentId').value;
            const data = {
                patient_id: document.getElementById('patient_id').value,
                doctor_id: document.getElementById('doctor_id').value,
                appointment_date: document.getElementById('appointment_date').value,
                status: document.getElementById('status').value,
                notes: document.getElementById('notes').value
            };
            
            const url = id ? `/api/appointments.php?id=${id}` : '/api/appointments.php';
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('appointmentModal')).hide();
                loadAppointments();
                resetForm();
            } else {
                const err = await response.json();
                alert('Error: ' + (err.error || 'Unknown error'));
            }
        }

        async function editAppointment(id) {
            const response = await fetch(`/api/appointments.php?id=${id}`, { credentials: 'include' });
            const apt = await response.json();
            
            document.getElementById('appointmentId').value = apt.id;
            document.getElementById('patient_id').value = apt.patient_id;
            document.getElementById('doctor_id').value = apt.doctor_id;
            // Convert MySQL datetime (YYYY-MM-DD HH:MM:SS) to datetime-local format (YYYY-MM-DDTHH:MM)
            const datetimeLocal = apt.appointment_date.replace(' ', 'T').slice(0, 16);
            document.getElementById('appointment_date').value = datetimeLocal;
            document.getElementById('status').value = apt.status;
            document.getElementById('notes').value = apt.notes;
            
            new bootstrap.Modal(document.getElementById('appointmentModal')).show();
        }

        async function deleteAppointment(id) {
            if (confirm('Delete this appointment?')) {
                const response = await fetch(`/api/appointments.php?id=${id}`, {
                    method: 'DELETE',
                    credentials: 'include'
                });
                if (response.ok) loadAppointments();
            }
        }

        function resetForm() {
            document.getElementById('appointmentId').value = '';
            document.getElementById('appointmentForm').reset();
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

        // Initialize
        loadDropdowns().then(() => loadAppointments());
    </script>
</body>
</html>

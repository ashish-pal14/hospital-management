<?php
require_once '../config/db.php';
require_once '../config/auth.php';

requireAuth();

$stats = [];

// Total patients
$stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
$stats['total_patients'] = $stmt->fetch()['count'];

// Total doctors
$stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors");
$stats['total_doctors'] = $stmt->fetch()['count'];

// Today's appointments
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()");
$stmt->execute();
$stats['today_appointments'] = $stmt->fetch()['count'];

// Recent appointments
$stmt = $pdo->query("SELECT a.*, p.name as patient_name, d.name as doctor_name 
                    FROM appointments a 
                    JOIN patients p ON a.patient_id = p.id 
                    JOIN doctors d ON a.doctor_id = d.id 
                    ORDER BY a.appointment_date DESC LIMIT 5");
$stats['recent_appointments'] = $stmt->fetchAll();

echo json_encode($stats);
?>

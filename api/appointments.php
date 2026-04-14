<?php
require_once '../config/db.php';
require_once '../config/auth.php';

requireAuth();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT a.*, p.name as patient_name, d.name as doctor_name 
                                   FROM appointments a 
                                   JOIN patients p ON a.patient_id = p.id 
                                   JOIN doctors d ON a.doctor_id = d.id 
                                   WHERE a.id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch());
        } else {
            $stmt = $pdo->query("SELECT a.*, p.name as patient_name, d.name as doctor_name 
                                FROM appointments a 
                                JOIN patients p ON a.patient_id = p.id 
                                JOIN doctors d ON a.doctor_id = d.id 
                                ORDER BY a.appointment_date DESC");
            echo json_encode($stmt->fetchAll());
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['patient_id'], $data['doctor_id'], $data['appointment_date'], $data['status'] ?? 'scheduled', $data['notes'] ?? '']);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Appointment ID required']);
            exit();
        }
        $stmt = $pdo->prepare("UPDATE appointments SET patient_id=?, doctor_id=?, appointment_date=?, status=?, notes=? WHERE id=?");
        $stmt->execute([$data['patient_id'], $data['doctor_id'], $data['appointment_date'], $data['status'], $data['notes'] ?? '', $_GET['id']]);
        echo json_encode(['success' => true]);
        break;
        
    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Appointment ID required']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['success' => true]);
        break;
}
?>

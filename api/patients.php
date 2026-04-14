<?php
require_once '../config/db.php';
require_once '../config/auth.php';

requireAuth();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single patient
            $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $patient = $stmt->fetch();
            echo json_encode($patient ?: ['error' => 'Patient not found']);
        } else {
            // Get all patients
            $stmt = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC");
            $patients = $stmt->fetchAll();
            echo json_encode($patients);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO patients (name, age, gender, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['age'], $data['gender'], $data['phone'], $data['address']]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;
        
    case 'PUT':
        parse_str(file_get_contents('php://input'), $_PUT);
        $data = json_decode(file_get_contents('php://input'), true) ?: $_PUT;
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Patient ID required']);
            exit();
        }
        $stmt = $pdo->prepare("UPDATE patients SET name=?, age=?, gender=?, phone=?, address=? WHERE id=?");
        $stmt->execute([$data['name'], $data['age'], $data['gender'], $data['phone'], $data['address'], $_GET['id']]);
        echo json_encode(['success' => true]);
        break;
        
    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Patient ID required']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['success' => true]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>

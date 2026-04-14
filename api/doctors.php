<?php
require_once '../config/db.php';
require_once '../config/auth.php';

// Only admin can manage doctors
requireRole('admin');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch() ?: ['error' => 'Doctor not found']);
        } else {
            $stmt = $pdo->query("SELECT * FROM doctors ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO doctors (name, specialization, phone, email, schedule) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['specialization'], $data['phone'], $data['email'], $data['schedule']]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Doctor ID required']);
            exit();
        }
        $stmt = $pdo->prepare("UPDATE doctors SET name=?, specialization=?, phone=?, email=?, schedule=? WHERE id=?");
        $stmt->execute([$data['name'], $data['specialization'], $data['phone'], $data['email'], $data['schedule'], $_GET['id']]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Doctor ID required']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>

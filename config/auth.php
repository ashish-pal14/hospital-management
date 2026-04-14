<?php
session_start();

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please login.']);
        exit();
    }
}

function requireRole($role) {
    requireAuth();
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: Insufficient permissions']);
        exit();
    }
}

function getCurrentUser() {
    return isAuthenticated() ? [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role']
    ] : null;
}
?>

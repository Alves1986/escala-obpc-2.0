<?php
require_once __DIR__ . '/firebase.php';

class Auth {
    public static function requireAuth() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de autorização não fornecido']);
            exit;
        }
        return FirebaseAuth::verifyToken($matches[1]);
    }
}
?>
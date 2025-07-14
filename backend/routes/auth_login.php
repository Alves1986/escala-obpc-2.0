<?php
// Endpoint de login
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/firebase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email e senha são obrigatórios']);
    exit;
}

// Aqui você pode implementar a lógica de autenticação com Firebase ou banco de dados
// Exemplo de resposta de sucesso:
echo json_encode([
    'token' => 'jwt_token_exemplo',
    'user' => [
        'email' => $email,
        'nome' => 'Usuário Exemplo',
        'tipo_usuario' => 'voluntario'
    ]
]); 
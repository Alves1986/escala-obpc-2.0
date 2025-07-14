<?php
// Endpoint de registro
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/firebase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$nome = $data['nome'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$nome || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Nome, email e senha são obrigatórios']);
    exit;
}

// Aqui você pode implementar a lógica de registro com Firebase ou banco de dados
// Exemplo de resposta de sucesso:
echo json_encode([
    'message' => 'Usuário registrado com sucesso',
    'user' => [
        'email' => $email,
        'nome' => $nome,
        'tipo_usuario' => 'voluntario'
    ]
]); 
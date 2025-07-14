<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$route = $_GET['route'] ?? '';

switch ($route) {
    // Rotas de autenticação (públicas)
    case 'auth/login':
        require __DIR__ . '/routes/auth_login.php';
        break;
    case 'auth/register':
        require __DIR__ . '/routes/auth_register.php';
        break;
        
    // Rotas de voluntários (protegidas)
    case 'voluntarios':
        require __DIR__ . '/routes/voluntarios.php';
        break;
        
    // Rotas de funções (protegidas)
    case 'funcoes':
        require __DIR__ . '/routes/funcoes.php';
        break;
        
    // Rotas de disponibilidade (protegidas)
    case 'disponibilidade':
        require __DIR__ . '/routes/disponibilidade.php';
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint não encontrado']);
        break;
} 
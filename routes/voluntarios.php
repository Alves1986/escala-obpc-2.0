<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Voluntario.php';

// Verificar autenticação
$user = Auth::requireAuth();

$voluntarioModel = new Voluntario();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Buscar voluntário específico
            $voluntario = $voluntarioModel->findById($_GET['id']);
            if (!$voluntario) {
                http_response_code(404);
                echo json_encode(['error' => 'Voluntário não encontrado']);
                exit;
            }
            echo json_encode($voluntario);
        } else {
            // Listar voluntários com filtros e paginação
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            $filtros = [
                'nome' => $_GET['nome'] ?? null,
                'funcao_id' => $_GET['funcao_id'] ?? null
            ];
            
            $voluntarios = $voluntarioModel->getAll($limit, $offset, $filtros);
            $total = $voluntarioModel->count($filtros);
            
            echo json_encode([
                'data' => $voluntarios,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        break;
        
    case 'POST':
        // Criar novo voluntário
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['nome']) || empty($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e email são obrigatórios']);
            exit;
        }
        
        // Validar email
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email inválido']);
            exit;
        }
        
        // Validar funções
        if (empty($input['funcoes_ids']) || !is_array($input['funcoes_ids'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Selecione pelo menos uma função']);
            exit;
        }
        
        try {
            $id = $voluntarioModel->create($input);
            $voluntario = $voluntarioModel->findById($id);
            echo json_encode($voluntario);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar voluntário']);
        }
        break;
        
    case 'PUT':
        // Atualizar voluntário
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do voluntário é obrigatório']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['nome']) || empty($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e email são obrigatórios']);
            exit;
        }
        
        // Validar email
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email inválido']);
            exit;
        }
        
        // Validar funções
        if (empty($input['funcoes_ids']) || !is_array($input['funcoes_ids'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Selecione pelo menos uma função']);
            exit;
        }
        
        try {
            $voluntario = $voluntarioModel->update($_GET['id'], $input);
            echo json_encode($voluntario);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar voluntário']);
        }
        break;
        
    case 'DELETE':
        // Excluir voluntário
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do voluntário é obrigatório']);
            exit;
        }
        
        try {
            $voluntarioModel->delete($_GET['id']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir voluntário']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
} 
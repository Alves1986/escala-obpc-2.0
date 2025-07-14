<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Funcao.php';

// Verificar autenticação
$user = Auth::requireAuth();

$funcaoModel = new Funcao();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Buscar função específica
            $funcao = $funcaoModel->findById($_GET['id']);
            if (!$funcao) {
                http_response_code(404);
                echo json_encode(['error' => 'Função não encontrada']);
                exit;
            }
            
            // Se solicitado, incluir voluntários
            if (isset($_GET['with_voluntarios']) && $_GET['with_voluntarios'] === 'true') {
                $funcao = $funcaoModel->getWithVoluntarios($_GET['id']);
            }
            
            echo json_encode($funcao);
        } else {
            // Listar funções com paginação
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            
            $funcoes = $funcaoModel->getAll($limit, $offset);
            $total = $funcaoModel->count();
            
            echo json_encode([
                'data' => $funcoes,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        break;
        
    case 'POST':
        // Criar nova função
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['nome'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome da função é obrigatório']);
            exit;
        }
        
        try {
            $id = $funcaoModel->create($input);
            $funcao = $funcaoModel->findById($id);
            echo json_encode($funcao);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar função']);
        }
        break;
        
    case 'PUT':
        // Atualizar função
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID da função é obrigatório']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['nome'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome da função é obrigatório']);
            exit;
        }
        
        try {
            $funcao = $funcaoModel->update($_GET['id'], $input);
            echo json_encode($funcao);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar função']);
        }
        break;
        
    case 'DELETE':
        // Excluir função
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID da função é obrigatório']);
            exit;
        }
        
        try {
            $funcaoModel->delete($_GET['id']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
} 
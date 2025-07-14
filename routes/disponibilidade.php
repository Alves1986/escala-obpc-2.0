<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Disponibilidade.php';

// Verificar autenticação
$user = Auth::requireAuth();

$disponibilidadeModel = new Disponibilidade();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['voluntario_id']) && isset($_GET['mes']) && isset($_GET['ano'])) {
            // Buscar disponibilidade de um voluntário específico
            $disponibilidade = $disponibilidadeModel->getDisponibilidadeVoluntario(
                $_GET['voluntario_id'], 
                $_GET['mes'], 
                $_GET['ano']
            );
            echo json_encode($disponibilidade);
        } elseif (isset($_GET['data'])) {
            // Buscar disponibilidade por data
            $turno = $_GET['turno'] ?? null;
            $disponibilidade = $disponibilidadeModel->getDisponibilidadePorData($_GET['data'], $turno);
            echo json_encode($disponibilidade);
        } elseif (isset($_GET['mes']) && isset($_GET['ano'])) {
            // Relatório mensal
            if (isset($_GET['relatorio']) && $_GET['relatorio'] === 'true') {
                $relatorio = $disponibilidadeModel->getRelatorioMensal($_GET['mes'], $_GET['ano']);
                echo json_encode($relatorio);
            } elseif (isset($_GET['estatisticas']) && $_GET['estatisticas'] === 'true') {
                $estatisticas = $disponibilidadeModel->getEstatisticasGerais($_GET['mes'], $_GET['ano']);
                echo json_encode($estatisticas);
            } elseif (isset($_GET['datas_eventos']) && $_GET['datas_eventos'] === 'true') {
                $datasEventos = $disponibilidadeModel->getDatasEventos($_GET['mes'], $_GET['ano']);
                echo json_encode($datasEventos);
            } else {
                // Buscar disponibilidade do usuário logado
                $disponibilidade = $disponibilidadeModel->getDisponibilidadeVoluntario(
                    $user['user_id'], 
                    $_GET['mes'], 
                    $_GET['ano']
                );
                echo json_encode($disponibilidade);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros obrigatórios: mes, ano']);
        }
        break;
        
    case 'POST':
        // Salvar disponibilidade
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['data']) || empty($input['turno']) || !isset($input['disponivel'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Data, turno e disponibilidade são obrigatórios']);
            exit;
        }
        
        // Validar turno
        $turnosValidos = ['manha', 'noite', 'quarta'];
        if (!in_array($input['turno'], $turnosValidos)) {
            http_response_code(400);
            echo json_encode(['error' => 'Turno inválido. Use: manha, noite, quarta']);
            exit;
        }
        
        try {
            $voluntarioId = $input['voluntario_id'] ?? $user['user_id'];
            $disponibilidadeModel->saveDisponibilidade(
                $voluntarioId,
                $input['data'],
                $input['turno'],
                $input['disponivel'],
                $input['observacoes'] ?? null
            );
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao salvar disponibilidade']);
        }
        break;
        
    case 'PUT':
        // Atualizar disponibilidade
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['data']) || empty($input['turno']) || !isset($input['disponivel'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Data, turno e disponibilidade são obrigatórios']);
            exit;
        }
        
        try {
            $voluntarioId = $input['voluntario_id'] ?? $user['user_id'];
            $disponibilidadeModel->saveDisponibilidade(
                $voluntarioId,
                $input['data'],
                $input['turno'],
                $input['disponivel'],
                $input['observacoes'] ?? null
            );
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar disponibilidade']);
        }
        break;
        
    case 'DELETE':
        // Excluir disponibilidade
        if (!isset($_GET['data']) || !isset($_GET['turno'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Data e turno são obrigatórios']);
            exit;
        }
        
        try {
            $voluntarioId = $_GET['voluntario_id'] ?? $user['user_id'];
            $disponibilidadeModel->deleteDisponibilidade(
                $voluntarioId,
                $_GET['data'],
                $_GET['turno']
            );
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir disponibilidade']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
} 
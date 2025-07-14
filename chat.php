<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

class Chat {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO chat_mensagens (autor_id, conteudo, data_criacao) VALUES (?, ?, NOW())";
        $this->db->query($sql, [
            $data['autor_id'],
            $data['conteudo']
        ]);
        return $this->db->getConnection()->lastInsertId();
    }

    public function findById($id) {
        $sql = "SELECT c.*, u.nome as autor_nome FROM chat_mensagens c LEFT JOIN usuarios u ON c.autor_id = u.id WHERE c.id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT c.*, u.nome as autor_nome FROM chat_mensagens c LEFT JOIN usuarios u ON c.autor_id = u.id ORDER BY c.data_criacao DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }
}

$user = Auth::requireAuth();
$chatModel = new Chat();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        $mensagens = $chatModel->getAll($limit, $offset);
        echo json_encode(['mensagens' => $mensagens]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['conteudo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Conteúdo é obrigatório']);
            exit;
        }
        try {
            $id = $chatModel->create([
                'autor_id' => $user['user_id'],
                'conteudo' => $input['conteudo']
            ]);
            $mensagem = $chatModel->findById($id);
            echo json_encode($mensagem);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao enviar mensagem: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
} 
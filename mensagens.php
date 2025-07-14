<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

class Mensagem {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data, $file = null) {
        $filePath = null;
        if ($file) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = time() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Falha ao salvar arquivo');
            }
            $filePath = '/uploads/' . $fileName;
        }

        $sql = "INSERT INTO mensagens (titulo, conteudo, categoria, autor_id, media_url, data_criacao) VALUES (?, ?, ?, ?, ?, NOW())";
        $this->db->query($sql, [
            $data['titulo'],
            $data['conteudo'],
            $data['categoria'],
            $data['autor_id'],
            $filePath
        ]);

        return $this->db->getConnection()->lastInsertId();
    }

    public function findById($id) {
        $sql = "SELECT m.*, u.nome as autor_nome FROM mensagens m LEFT JOIN usuarios u ON m.autor_id = u.id WHERE m.id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT m.*, u.nome as autor_nome FROM mensagens m LEFT JOIN usuarios u ON m.autor_id = u.id ORDER BY m.data_criacao DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }
}

$user = Auth::requireAuth();
$mensagemModel = new Mensagem();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        $mensagens = $mensagemModel->getAll($limit, $offset);
        echo json_encode(['mensagens' => $mensagens]);
        break;

    case 'POST':
        $titulo = trim($_POST['titulo'] ?? '');
        $conteudo = trim($_POST['conteudo'] ?? '');
        $categoria = $_POST['categoria'] ?? 'geral';

        if (empty($titulo) || empty($conteudo)) {
            http_response_code(400);
            echo json_encode(['error' => 'Título e conteúdo são obrigatórios']);
            exit;
        }

        $file = $_FILES['media'] ?? null;
        if ($file && !in_array($file['type'], ['image/jpeg', 'image/png', 'application/pdf'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Apenas JPEG, PNG ou PDF são permitidos']);
            exit;
        }

        try {
            $id = $mensagemModel->create([
                'titulo' => $titulo,
                'conteudo' => $conteudo,
                'categoria' => $categoria,
                'autor_id' => $user['user_id'],
            ], $file);
            $mensagem = $mensagemModel->findById($id);
            echo json_encode($mensagem);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao salvar mensagem: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
} 
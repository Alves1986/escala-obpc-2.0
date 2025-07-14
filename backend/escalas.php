<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

class Escala {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO escalas (data, turno, funcao_id, voluntario_id, observacoes) VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['data'],
            $data['turno'],
            $data['funcao_id'],
            $data['voluntario_id'],
            $data['observacoes'] ?? null
        ]);
        return $this->db->getConnection()->lastInsertId();
    }

    public function findById($id) {
        $sql = "SELECT e.*, f.nome as funcao_nome, v.nome as voluntario_nome FROM escalas e LEFT JOIN funcoes f ON e.funcao_id = f.id LEFT JOIN voluntarios v ON e.voluntario_id = v.id WHERE e.id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function getAll($inicio, $fim) {
        $sql = "SELECT e.*, f.nome as funcao_nome, v.nome as voluntario_nome FROM escalas e LEFT JOIN funcoes f ON e.funcao_id = f.id LEFT JOIN voluntarios v ON e.voluntario_id = v.id WHERE e.data BETWEEN ? AND ? ORDER BY e.data, e.turno";
        $stmt = $this->db->query($sql, [$inicio, $fim]);
        return $stmt->fetchAll();
    }
}

$user = Auth::requireAuth();
$escalaModel = new Escala();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!isset($_GET['inicio']) || !isset($_GET['fim'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inicio e fim são obrigatórios']);
            exit;
        }
        $escalas = $escalaModel->getAll($_GET['inicio'], $_GET['fim']);
        echo json_encode(['escalas' => $escalas]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['data']) || empty($input['turno']) || empty($input['funcao_id']) || empty($input['voluntario_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Data, turno, funcao_id e voluntario_id são obrigatórios']);
            exit;
        }
        try {
            $id = $escalaModel->create($input);
            $escala = $escalaModel->findById($id);
            echo json_encode($escala);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar escala: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
} 
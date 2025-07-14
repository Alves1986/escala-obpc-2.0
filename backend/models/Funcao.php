<?php
/**
 * Modelo de Função
 * Gerencia operações de funções/ministérios no banco de dados
 */

require_once __DIR__ . '/../config/database.php';

class Funcao {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO funcoes (nome, descricao, cor, data_criacao) 
                VALUES (?, ?, ?, NOW())";
        
        $this->db->query($sql, [
            $data['nome'],
            $data['descricao'] ?? null,
            $data['cor'] ?? '#6366f1'
        ]);
        
        return $this->db->getConnection()->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM funcoes WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT f.*, COUNT(v.id) as total_voluntarios 
                FROM funcoes f 
                LEFT JOIN voluntarios v ON f.id = v.funcao_id 
                GROUP BY f.id 
                ORDER BY f.nome 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE funcoes SET nome = ?, descricao = ?, cor = ? WHERE id = ?";
        
        $this->db->query($sql, [
            $data['nome'],
            $data['descricao'] ?? null,
            $data['cor'] ?? '#6366f1',
            $id
        ]);
        
        return $this->findById($id);
    }
    
    public function delete($id) {
        // Verificar se há voluntários associados
        $sql = "SELECT COUNT(*) as total FROM voluntarios WHERE funcao_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            throw new Exception('Não é possível excluir função com voluntários associados');
        }
        
        $sql = "DELETE FROM funcoes WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM funcoes";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    public function getWithVoluntarios($id) {
        $sql = "SELECT f.*, v.id as voluntario_id, v.nome as voluntario_nome, 
                       v.email, v.telefone, v.disponibilidade 
                FROM funcoes f 
                LEFT JOIN voluntarios v ON f.id = v.funcao_id 
                WHERE f.id = ? 
                ORDER BY v.nome";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetchAll();
    }
} 
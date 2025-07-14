<?php
/**
 * Modelo de Voluntário - Ministério de Comunicação
 * Gerencia operações de voluntários no banco de dados
 */

require_once __DIR__ . '/../config/database.php';

class Voluntario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO voluntarios (nome, email, whatsapp, funcoes_ids, observacoes) 
                VALUES (?, ?, ?, ?, ?)";
        
        $funcoesIds = json_encode($data['funcoes_ids'] ?? []);
        
        $this->db->query($sql, [
            $data['nome'],
            $data['email'],
            $data['whatsapp'] ?? null,
            $funcoesIds,
            $data['observacoes'] ?? null
        ]);
        
        return $this->db->getConnection()->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM voluntarios WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $voluntario = $stmt->fetch();
        
        if ($voluntario) {
            $voluntario['funcoes_ids'] = json_decode($voluntario['funcoes_ids'], true) ?? [];
        }
        
        return $voluntario;
    }
    
    public function getAll($limit = 50, $offset = 0, $filtros = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['nome'])) {
            $where .= " AND nome LIKE ?";
            $params[] = '%' . $filtros['nome'] . '%';
        }
        
        if (!empty($filtros['funcao_id'])) {
            $where .= " AND JSON_CONTAINS(funcoes_ids, ?)";
            $params[] = json_encode($filtros['funcao_id']);
        }
        
        $sql = "SELECT * FROM voluntarios $where ORDER BY nome LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->query($sql, $params);
        $voluntarios = $stmt->fetchAll();
        
        // Decodificar funcoes_ids para cada voluntário
        foreach ($voluntarios as &$voluntario) {
            $voluntario['funcoes_ids'] = json_decode($voluntario['funcoes_ids'], true) ?? [];
        }
        
        return $voluntarios;
    }
    
    public function update($id, $data) {
        $sql = "UPDATE voluntarios SET 
                nome = ?, email = ?, whatsapp = ?, funcoes_ids = ?, observacoes = ? 
                WHERE id = ?";
        
        $funcoesIds = json_encode($data['funcoes_ids'] ?? []);
        
        $this->db->query($sql, [
            $data['nome'],
            $data['email'],
            $data['whatsapp'] ?? null,
            $funcoesIds,
            $data['observacoes'] ?? null,
            $id
        ]);
        
        return $this->findById($id);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM voluntarios WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    public function count($filtros = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['nome'])) {
            $where .= " AND nome LIKE ?";
            $params[] = '%' . $filtros['nome'] . '%';
        }
        
        if (!empty($filtros['funcao_id'])) {
            $where .= " AND JSON_CONTAINS(funcoes_ids, ?)";
            $params[] = json_encode($filtros['funcao_id']);
        }
        
        $sql = "SELECT COUNT(*) as total FROM voluntarios $where";
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    public function getByFuncao($funcaoId) {
        $sql = "SELECT * FROM voluntarios WHERE JSON_CONTAINS(funcoes_ids, ?)";
        $stmt = $this->db->query($sql, [json_encode($funcaoId)]);
        $voluntarios = $stmt->fetchAll();
        
        foreach ($voluntarios as &$voluntario) {
            $voluntario['funcoes_ids'] = json_decode($voluntario['funcoes_ids'], true) ?? [];
        }
        
        return $voluntarios;
    }
    
    public function getDisponibilidade($voluntarioId, $mes, $ano) {
        $sql = "SELECT * FROM disponibilidade 
                WHERE voluntario_id = ? 
                AND YEAR(data_disponibilidade) = ? 
                AND MONTH(data_disponibilidade) = ?
                ORDER BY data_disponibilidade, turno";
        
        $stmt = $this->db->query($sql, [$voluntarioId, $ano, $mes]);
        return $stmt->fetchAll();
    }
    
    public function saveDisponibilidade($voluntarioId, $data, $turno, $disponivel, $observacoes = null) {
        $sql = "INSERT INTO disponibilidade (voluntario_id, data_disponibilidade, turno, disponivel, observacoes) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                disponivel = VALUES(disponivel), 
                observacoes = VALUES(observacoes)";
        
        $this->db->query($sql, [
            $voluntarioId,
            $data,
            $turno,
            $disponivel ? 1 : 0,
            $observacoes
        ]);
        
        return true;
    }
    
    public function getDisponiveisPorFuncao($funcaoId, $data, $turno) {
        $sql = "SELECT v.* FROM voluntarios v
                INNER JOIN disponibilidade d ON v.id = d.voluntario_id
                WHERE JSON_CONTAINS(v.funcoes_ids, ?)
                AND d.data_disponibilidade = ?
                AND d.turno = ?
                AND d.disponivel = 1
                ORDER BY v.nome";
        
        $stmt = $this->db->query($sql, [json_encode($funcaoId), $data, $turno]);
        $voluntarios = $stmt->fetchAll();
        
        foreach ($voluntarios as &$voluntario) {
            $voluntario['funcoes_ids'] = json_decode($voluntario['funcoes_ids'], true) ?? [];
        }
        
        return $voluntarios;
    }
} 
<?php
/**
 * Modelo de Disponibilidade - Ministério de Comunicação
 * Gerencia operações de disponibilidade dos voluntários
 */

require_once __DIR__ . '/../config/database.php';

class Disponibilidade {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
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
    
    public function getDisponibilidadeVoluntario($voluntarioId, $mes, $ano) {
        $sql = "SELECT * FROM disponibilidade 
                WHERE voluntario_id = ? 
                AND YEAR(data_disponibilidade) = ? 
                AND MONTH(data_disponibilidade) = ?
                ORDER BY data_disponibilidade, turno";
        
        $stmt = $this->db->query($sql, [$voluntarioId, $ano, $mes]);
        return $stmt->fetchAll();
    }
    
    public function getDisponibilidadePorData($data, $turno = null) {
        $sql = "SELECT d.*, v.nome as voluntario_nome, v.whatsapp 
                FROM disponibilidade d
                INNER JOIN voluntarios v ON d.voluntario_id = v.id
                WHERE d.data_disponibilidade = ?";
        
        $params = [$data];
        
        if ($turno) {
            $sql .= " AND d.turno = ?";
            $params[] = $turno;
        }
        
        $sql .= " ORDER BY v.nome, d.turno";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
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
    
    public function getRelatorioMensal($mes, $ano) {
        $sql = "SELECT 
                    v.nome,
                    COUNT(CASE WHEN d.disponivel = 1 THEN 1 END) as total_disponivel,
                    COUNT(CASE WHEN d.disponivel = 0 THEN 1 END) as total_indisponivel,
                    COUNT(*) as total_datas,
                    ROUND((COUNT(CASE WHEN d.disponivel = 1 THEN 1 END) / COUNT(*)) * 100, 2) as percentual_disponivel
                FROM voluntarios v
                LEFT JOIN disponibilidade d ON v.id = d.voluntario_id 
                    AND YEAR(d.data_disponibilidade) = ? 
                    AND MONTH(d.data_disponibilidade) = ?
                GROUP BY v.id, v.nome
                ORDER BY v.nome";
        
        $stmt = $this->db->query($sql, [$ano, $mes]);
        return $stmt->fetchAll();
    }
    
    public function getEstatisticasGerais($mes, $ano) {
        $sql = "SELECT 
                    COUNT(DISTINCT v.id) as total_voluntarios,
                    COUNT(DISTINCT d.voluntario_id) as voluntarios_com_disponibilidade,
                    COUNT(CASE WHEN d.disponivel = 1 THEN 1 END) as total_disponibilidades,
                    COUNT(CASE WHEN d.disponivel = 0 THEN 1 END) as total_indisponibilidades,
                    ROUND((COUNT(CASE WHEN d.disponivel = 1 THEN 1 END) / COUNT(*)) * 100, 2) as percentual_disponivel
                FROM voluntarios v
                LEFT JOIN disponibilidade d ON v.id = d.voluntario_id 
                    AND YEAR(d.data_disponibilidade) = ? 
                    AND MONTH(d.data_disponibilidade) = ?";
        
        $stmt = $this->db->query($sql, [$ano, $mes]);
        return $stmt->fetch();
    }
    
    public function getDatasEventos($mes, $ano) {
        // Retorna domingos e quartas do mês
        $sql = "SELECT 
                    DATE(data_disponibilidade) as data,
                    turno,
                    COUNT(CASE WHEN disponivel = 1 THEN 1 END) as disponiveis,
                    COUNT(CASE WHEN disponivel = 0 THEN 1 END) as indisponiveis,
                    COUNT(*) as total_voluntarios
                FROM disponibilidade 
                WHERE YEAR(data_disponibilidade) = ? 
                AND MONTH(data_disponibilidade) = ?
                GROUP BY data_disponibilidade, turno
                ORDER BY data_disponibilidade, turno";
        
        $stmt = $this->db->query($sql, [$ano, $mes]);
        return $stmt->fetchAll();
    }
    
    public function deleteDisponibilidade($voluntarioId, $data, $turno) {
        $sql = "DELETE FROM disponibilidade 
                WHERE voluntario_id = ? AND data_disponibilidade = ? AND turno = ?";
        
        $this->db->query($sql, [$voluntarioId, $data, $turno]);
        return true;
    }
    
    public function getVoluntariosSemDisponibilidade($mes, $ano) {
        $sql = "SELECT v.* FROM voluntarios v
                LEFT JOIN disponibilidade d ON v.id = d.voluntario_id 
                    AND YEAR(d.data_disponibilidade) = ? 
                    AND MONTH(d.data_disponibilidade) = ?
                WHERE d.id IS NULL
                ORDER BY v.nome";
        
        $stmt = $this->db->query($sql, [$ano, $mes]);
        $voluntarios = $stmt->fetchAll();
        
        foreach ($voluntarios as &$voluntario) {
            $voluntario['funcoes_ids'] = json_decode($voluntario['funcoes_ids'], true) ?? [];
        }
        
        return $voluntarios;
    }
} 
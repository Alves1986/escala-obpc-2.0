<?php
/**
 * Modelo de Usuário
 * Gerencia operações de usuários no banco de dados
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo_usuario, data_criacao) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $senhaHash = password_hash($data['senha'], PASSWORD_DEFAULT);
        $tipoUsuario = $data['tipo_usuario'] ?? 'user';
        
        $this->db->query($sql, [
            $data['nome'],
            $data['email'],
            $senhaHash,
            $tipoUsuario
        ]);
        
        return $this->db->getConnection()->lastInsertId();
    }
    
    public function findByEmail($email) {
        $sql = "SELECT id, nome, email, senha, tipo_usuario, data_criacao 
                FROM usuarios WHERE email = ?";
        
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $sql = "SELECT id, nome, email, tipo_usuario, data_criacao 
                FROM usuarios WHERE id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function authenticate($email, $senha) {
        $user = $this->findByEmail($email);
        
        if (!$user || !password_verify($senha, $user['senha'])) {
            return false;
        }
        
        // Remove a senha do retorno por segurança
        unset($user['senha']);
        return $user;
    }
    
    public function update($id, $data) {
        $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
        
        $this->db->query($sql, [
            $data['nome'],
            $data['email'],
            $id
        ]);
        
        return $this->findById($id);
    }
    
    public function updatePassword($id, $novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
        
        $this->db->query($sql, [$senhaHash, $id]);
        return true;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    public function getAll($limit = 50, $offset = 0) {
        $sql = "SELECT id, nome, email, tipo_usuario, data_criacao 
                FROM usuarios ORDER BY nome LIMIT ? OFFSET ?";
        
        $stmt = $this->db->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
} 
-- Script de criação do banco de dados para o Sistema de Escalas da Igreja
-- Ministério de Comunicação

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('admin', 'voluntario') DEFAULT 'voluntario',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de funções do ministério de comunicação
CREATE TABLE funcoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    cor VARCHAR(7) DEFAULT '#6366f1',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de voluntários
CREATE TABLE voluntarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    whatsapp VARCHAR(20),
    funcoes_ids JSON, -- Array de IDs das funções que pode exercer
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de disponibilidade
CREATE TABLE disponibilidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voluntario_id INT NOT NULL,
    data_disponibilidade DATE NOT NULL,
    turno ENUM('manha', 'noite', 'quarta') NOT NULL,
    disponivel BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (voluntario_id) REFERENCES voluntarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_disponibilidade (voluntario_id, data_disponibilidade, turno)
);

-- Tabela de escalas
CREATE TABLE escalas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    data_escala DATE NOT NULL,
    turno ENUM('manha', 'noite', 'quarta') NOT NULL,
    descricao TEXT,
    status ENUM('rascunho', 'publicada', 'cancelada') DEFAULT 'rascunho',
    criado_por INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    UNIQUE KEY unique_escala (data_escala, turno)
);

-- Tabela de itens da escala (voluntários escalados)
CREATE TABLE escala_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    escala_id INT NOT NULL,
    voluntario_id INT NOT NULL,
    funcao_id INT NOT NULL,
    observacoes TEXT,
    status ENUM('confirmado', 'pendente', 'recusado') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escala_id) REFERENCES escalas(id) ON DELETE CASCADE,
    FOREIGN KEY (voluntario_id) REFERENCES voluntarios(id),
    FOREIGN KEY (funcao_id) REFERENCES funcoes(id),
    UNIQUE KEY unique_escala_item (escala_id, voluntario_id, funcao_id)
);

-- Tabela de notificações
CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    lida BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Inserir dados iniciais
INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
('Administrador', 'admin@igreja.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Amanda Silva', 'amanda@igreja.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'voluntario');

INSERT INTO funcoes (nome, descricao, cor) VALUES 
('Projeção', 'Operação dos slides durante cultos', '#3b82f6'),
('Live/Iluminação', 'Transmissão ao vivo e controle de iluminação', '#ef4444'),
('Foto', 'Registro fotográfico dos eventos', '#10b981'),
('Stories', 'Criação de conteúdo para redes sociais', '#f59e0b'),
('Treinamento Foto/Stories', 'Capacitação de novos voluntários em foto e stories', '#8b5cf6'),
('Treinamento Projeção/Live', 'Capacitação de novos voluntários em projeção e live', '#ec4899');

-- Índices para melhor performance
CREATE INDEX idx_voluntarios_funcoes ON voluntarios((JSON_EXTRACT(funcoes_ids, '$')));
CREATE INDEX idx_disponibilidade_voluntario ON disponibilidade(voluntario_id);
CREATE INDEX idx_disponibilidade_data ON disponibilidade(data_disponibilidade);
CREATE INDEX idx_escalas_data ON escalas(data_escala);
CREATE INDEX idx_escala_itens_escala ON escala_itens(escala_id);
CREATE INDEX idx_notificacoes_usuario ON notificacoes(usuario_id); 
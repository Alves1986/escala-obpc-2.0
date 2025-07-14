# Backend PHP - Sistema de Escalas da Igreja

Backend completo em PHP para o sistema de gerenciamento de escalas do **ministério de comunicação** da igreja.

## 🚀 Características

- **Autenticação JWT** - Sistema seguro de login/logout
- **Banco de dados real** - MySQL/PostgreSQL com PDO
- **Endpoints protegidos** - Autenticação obrigatória para rotas sensíveis
- **CRUD completo** - Voluntários, funções, disponibilidade
- **Validação robusta** - Validação de dados e tratamento de erros
- **CORS configurado** - Compatível com frontend React
- **Relatórios** - Estatísticas e relatórios de disponibilidade

## 📋 Pré-requisitos

- PHP 7.4+ ou 8.0+
- MySQL 5.7+ ou PostgreSQL 10+
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, JSON, mbstring

## 🔧 Instalação

### 1. Configurar banco de dados

```sql
-- Execute o script de criação do banco
mysql -u root -p < database/schema.sql
```

### 2. Configurar variáveis de ambiente

```bash
# Copie o arquivo de exemplo
cp config/env.example.php config/env.php

# Edite as configurações
nano config/env.php
```

### 3. Configurar servidor web

#### Apache (.htaccess já configurado)
```apache
# O arquivo .htaccess já está configurado para:
# - CORS
# - URL rewriting
# - Headers de segurança
```

#### Nginx
```nginx
location /backend/ {
    try_files $uri $uri/ /backend/index.php?route=$uri&$args;
    
    # CORS
    add_header 'Access-Control-Allow-Origin' '*';
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS';
    add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization';
}
```

## 📚 Endpoints Disponíveis

### Autenticação

#### POST /auth/login
```json
{
  "email": "admin@igreja.com",
  "password": "123456"
}
```

#### POST /auth/register
```json
{
  "nome": "João Silva",
  "email": "joao@igreja.com",
  "password": "123456"
}
```

### Voluntários

#### GET /voluntarios
Listar voluntários com filtros e paginação.

**Parâmetros:**
- `limit` (opcional): Número de registros (padrão: 50)
- `offset` (opcional): Registro inicial (padrão: 0)
- `nome` (opcional): Filtrar por nome
- `funcao_id` (opcional): Filtrar por função

#### GET /voluntarios?id=1
Buscar voluntário específico.

#### POST /voluntarios
```json
{
  "nome": "Maria Santos",
  "email": "maria@igreja.com",
  "whatsapp": "(11) 99999-9999",
  "funcoes_ids": [1, 2, 3],
  "observacoes": "Disponível aos domingos"
}
```

#### PUT /voluntarios?id=1
Atualizar voluntário.

#### DELETE /voluntarios?id=1
Excluir voluntário.

### Funções

#### GET /funcoes
Listar funções do ministério de comunicação.

#### GET /funcoes?id=1
Buscar função específica.

#### POST /funcoes
```json
{
  "nome": "Projeção",
  "descricao": "Operação dos slides durante cultos",
  "cor": "#3b82f6"
}
```

### Disponibilidade

#### GET /disponibilidade?mes=12&ano=2024
Buscar disponibilidade do usuário logado no mês/ano.

#### GET /disponibilidade?mes=12&ano=2024&relatorio=true
Relatório mensal de disponibilidade (apenas admin).

#### GET /disponibilidade?mes=12&ano=2024&estatisticas=true
Estatísticas gerais do mês (apenas admin).

#### POST /disponibilidade
```json
{
  "data": "2024-12-15",
  "turno": "manha",
  "disponivel": true,
  "observacoes": "Posso ajudar"
}
```

**Turnos disponíveis:**
- `manha` - Domingo manhã
- `noite` - Domingo noite  
- `quarta` - Quarta-feira

## 🏗️ Estrutura do Sistema

### Funções do Ministério de Comunicação
1. **Projeção** - Operação dos slides durante cultos
2. **Live/Iluminação** - Transmissão ao vivo e controle de iluminação
3. **Foto** - Registro fotográfico dos eventos
4. **Stories** - Criação de conteúdo para redes sociais
5. **Treinamento Foto/Stories** - Capacitação de novos voluntários
6. **Treinamento Projeção/Live** - Capacitação de novos voluntários

### Calendário de Eventos
- **Domingos**: Dois turnos (Manhã e Noite)
- **Quartas-feiras**: Um turno (Quarta)

### Tipos de Usuário
- **Administrador**: Acesso completo ao sistema
- **Voluntário**: Acesso para visualizar escalas e informar disponibilidade

## 🔐 Autenticação

### Headers necessários
```http
Authorization: Bearer seu_token_jwt_aqui
Content-Type: application/json
```

### Exemplo de uso
```javascript
// Login
const response = await fetch('/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
});

const { token } = await response.json();

// Usar token em outras requisições
const voluntarios = await fetch('/voluntarios', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

## 🛠️ Estrutura do Projeto

```
php-api/
├── config/
│   ├── auth.php          # Sistema JWT
│   ├── database.php      # Conexão com banco
│   └── env.example.php   # Configurações de exemplo
├── models/
│   ├── User.php          # Modelo de usuários
│   ├── Voluntario.php    # Modelo de voluntários
│   ├── Funcao.php        # Modelo de funções
│   └── Disponibilidade.php # Modelo de disponibilidade
├── routes/
│   ├── auth_login.php    # Login
│   ├── auth_register.php # Cadastro
│   ├── voluntarios.php   # CRUD voluntários
│   ├── funcoes.php       # CRUD funções
│   └── disponibilidade.php # CRUD disponibilidade
├── database/
│   └── schema.sql        # Script de criação do banco
├── .htaccess             # Configuração Apache
├── index.php             # Roteador principal
└── README.md             # Esta documentação
```

## 🔧 Configuração de Produção

### 1. Segurança
- Altere a chave JWT_SECRET
- Configure HTTPS
- Defina APP_ENV como 'production'

### 2. Performance
- Ative cache do servidor web
- Configure índices no banco
- Use CDN para arquivos estáticos

### 3. Monitoramento
- Configure logs de erro
- Monitore performance do banco
- Implemente health checks

## 🐛 Troubleshooting

### Erro de conexão com banco
- Verifique as credenciais em `config/env.php`
- Confirme se o banco existe e está acessível
- Teste a conexão manualmente

### Erro 405 Method Not Allowed
- Verifique se o .htaccess está funcionando
- Confirme se o mod_rewrite está ativo
- Teste com diferentes métodos HTTP

### Token JWT inválido
- Verifique se o JWT_SECRET está configurado
- Confirme se o token não expirou
- Valide o formato do token

## 📝 Logs

Os logs de erro são gravados em:
- Apache: `/var/log/apache2/error.log`
- Nginx: `/var/log/nginx/error.log`
- PHP: Configurado em `php.ini`

## 🤝 Contribuição

1. Faça fork do projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. 
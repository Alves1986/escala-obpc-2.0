<?php
/**
 * Arquivo de exemplo de configuração de ambiente
 * Copie este arquivo para env.php e ajuste as configurações
 */

// Configurações do banco de dados
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'escala_igreja';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_DRIVER'] = 'mysql'; // mysql ou pgsql

// Configurações de segurança
$_ENV['JWT_SECRET'] = 'sua_chave_secreta_muito_segura_aqui';
$_ENV['JWT_EXPIRATION'] = 86400; // 24 horas em segundos

// Configurações da aplicação
$_ENV['APP_NAME'] = 'Sistema de Escalas';
$_ENV['APP_URL'] = 'https://seu-dominio.com';
$_ENV['APP_ENV'] = 'production'; // development ou production

// Configurações de email (opcional)
$_ENV['MAIL_HOST'] = 'smtp.gmail.com';
$_ENV['MAIL_PORT'] = '587';
$_ENV['MAIL_USERNAME'] = 'seu-email@gmail.com';
$_ENV['MAIL_PASSWORD'] = 'sua-senha-de-app';
$_ENV['MAIL_FROM_ADDRESS'] = 'noreply@igreja.com';
$_ENV['MAIL_FROM_NAME'] = 'Sistema de Escalas';

// Configurações de upload (opcional)
$_ENV['UPLOAD_PATH'] = '/uploads/';
$_ENV['MAX_FILE_SIZE'] = '5242880'; // 5MB em bytes

// Configurações de log
$_ENV['LOG_LEVEL'] = 'error'; // debug, info, warning, error
$_ENV['LOG_PATH'] = '/logs/';

// Configurações de cache (opcional)
$_ENV['CACHE_ENABLED'] = 'true';
$_ENV['CACHE_PATH'] = '/cache/';
$_ENV['CACHE_TTL'] = '3600'; // 1 hora em segundos 
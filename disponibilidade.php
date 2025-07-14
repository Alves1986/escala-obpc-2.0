<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

$user = Auth::requireAuth();
// ... restante do código, use $user['user_id'] para identificar o usuário autenticado ... 
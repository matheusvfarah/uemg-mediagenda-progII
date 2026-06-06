<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../conexao.php';

function mediagenda_require_login(): array
{
    if (empty($_SESSION['cod_usuario'])) {
        header('Location: login.php');
        exit;
    }

    return [
        'cod_usuario' => (int)($_SESSION['cod_usuario'] ?? 0),
        'nome' => (string)($_SESSION['nome'] ?? ''),
        'email' => (string)($_SESSION['email'] ?? ''),
        'username' => (string)($_SESSION['username'] ?? ''),
    ];
}

function mediagenda_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

<?php
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$senha = isset($_POST['senha']) ? (string)$_POST['senha'] : '';

session_start();

if ($usuario === '' || $senha === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Informe usuário e senha.'
    ]);
    exit;
}

try {
    $sql = 'SELECT cod_usuario, nome, email, username, passWord_sha256 FROM usuario WHERE username = ? OR email = ? LIMIT 1';
    $stmt = mysqli_prepare($conexao_bd, $sql);

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro na preparação da consulta.'
        ]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $usuario, $usuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) !== 1) {
        mysqli_stmt_close($stmt);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não encontrado.'
        ]);
        exit;
    }

    mysqli_stmt_bind_result($stmt, $cod_usuario_bd, $nome_bd, $email_bd, $username_bd, $passWord_sha256);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (hash('sha256', $senha) !== $passWord_sha256) {
        echo json_encode([
            'success' => false,
            'message' => 'Senha incorreta. Verifique!'
        ]);
        exit;
    }

    $_SESSION['logado'] = true;
    $_SESSION['cod_usuario'] = (int)$cod_usuario_bd;
    $_SESSION['username'] = $username_bd;
    $_SESSION['email'] = $email_bd;
    $_SESSION['nome'] = $nome_bd;

    echo json_encode([
        'success' => true,
        'message' => 'Autenticado com sucesso!'
    ]);
    exit;
} catch (mysqli_sql_exception $exception) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao consultar o banco de dados.'
    ]);
    exit;
}
?>
<?php
$host_bd = 'mysql';
$login_bd = 'mediagenda_user';
$password_bd = 'mediagenda_pass';
$nome_bd = 'mediagenda';
$port = 3306;

$conexao_bd = mysqli_connect($host_bd, $login_bd, $password_bd, $nome_bd, $port);

if (!$conexao_bd) {
    die('Falha na conexão com o banco de dados: ' . mysqli_connect_error());
}

mysqli_set_charset($conexao_bd, 'utf8mb4');
?>
<?php
    require_once("conexao.php");// importar o conexao.php para esta página
    $usuario = $_POST["usuario"];
    $senha = $_POST["senha"];

    session_start();
    //wtf, que lógica é essa? kkkk
    //vamo melhorar isso ae pae, vai vendo, respeita o roxo

    if(isset($usuario) && isset($senha)){

        //prepared statements parav evitar SQL injection, respeita o ROXO
        $sql = "SELECT email, username, passWord_sha256 FROM usuario WHERE username = ? OR email = ?";

        if($stmt = mysqli_prepare($conexao_bd, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if(mysqli_stmt_num_rows($stmt) == 1){
                mysqli_stmt_bind_result($stmt, $email_bd, $username_bd, $passWord_sha256);
                mysqli_stmt_fetch($stmt);

                if(hash("sha256", $senha) === $passWord_sha256){
                    //autenticado com sucesso
                    $_SESSION["logado"] = true;
                    $_SESSION["username"] = $username_bd;
                    $_SESSION["email"] = $email_bd;

                    echo json_encode([
                        "success" => true,
                        "message" => "Autenticado com sucesso!"
                    ]);
                    exit;
                } else {
                    //senha incorreta
                    echo json_encode([
                        "success" => false,
                        "message" => "Senha incorreta. Verifique!"
                    ]);
                    exit;
                }
            } else {
                //usuário não encontrado
                echo json_encode([
                    "success" => false,
                    "message" => "Usuário não encontrado"
                ]);
                exit;
            }

            mysqli_stmt_close($stmt);
        } else {
            //erro na preparação da consulta
            echo json_encode([
                "success" => false,
                "message" => "Erro na preparação da consulta."
            ]);
            exit;
        }
    }

    //validar no banco de dados
    //ir para página autenticada
    //ou retornar para index
    /*
    echo "Cadastrar no banco o $usuario com a $senha <br>";

    echo "<a href='index.php'>Retornar</a>";*/
?>
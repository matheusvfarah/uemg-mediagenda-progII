<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Agendador de consultas</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
</head>

<body>
    <div class="top-content">
        <div class="inner-bg">
            <div class="container">
                <div class="row">
                    <div class="col-sm6 col-sm-offset-3 form-box">
                        <div class="formt-top-left">
                            <h3>Sistema de agendamento de consultas</h3>
                            <p>Digite seu Usuário e Senha</p>
                        </div>
                    </div>
                    <div class="form-bottom">
                        <form role="form"
                            class="login-form"
                            id = "loginForm"
                            onSubmit="return validateForm()">

                            <div class="form-group">
                                <label class="sr-only" for="usuario">Usuário:</label>
                                <input type="text" name="usuario" id="usuario"
                                    placeholder="Usuário"
                                    class="form-username form-control">
                            </div>

                            <div class="form-group">
                                <label class="sr-only" for="senha">Senha:</label>
                                <input type="password" name="senha" id="senha"
                                    placeholder="Senha"
                                    class="form-username form-control">
                            </div>

                            <div class="form-group">
                                <br>
                                <button type="submit" class="btn btn-primary" id="entrarBtn">Entrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function validateForm() {
            var usuarioTela = document.getElementById("usuario").value;
            var senhaTela = document.getElementById("senha").value;
            if (usuarioTela.length == 0) {
                //alert("Usuário em branco. Verifique!");
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Usuário em branco. Verifique!"
                });
                return false;
            } else {
                if (senhaTela.length == 0) {
                    //alert("Senha em branco. Verifique!");
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Senha em branco. Verifique!"
                    });
                    return false;
                } else {
                    return true;
                }
            }
        }
        //chamando um fetch passando os dados para receber a resposta do php ao inves de fazer um submit normal, respeita o roxo
        const entrarBtn = document.getElementById("entrarBtn");
        entrarBtn.addEventListener("click", function(event) {
            event.preventDefault(); //impede o submit normal do formulário
            if (!validateForm()) {
                return;
            }

            var usuarioTela = document.getElementById("usuario").value;
            var senhaTela = document.getElementById("senha").value;

            fetch("cadastrobanco.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "usuario=" + encodeURIComponent(usuarioTela) + "&senha=" + encodeURIComponent(senhaTela)
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (!data.success) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: data.message
                    });
                }else{
                    window.location.href = "principal.php";
                }
                
            })
            .catch(error => {
                console.error("Erro:", error);
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Ocorreu um erro ao tentar entrar. Tente novamente mais tarde."
                });
            });
        });
    </script>
</body>

</html>
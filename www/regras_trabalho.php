<?php
session_start();
require_once 'conexao.php';

if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

if (!isset($_SESSION['cod_usuario'])) {
    header('Location: login.php');
    exit;
}

$operadorNome = $_SESSION['nome'] ?? '';
$operadorEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Regras do trabalho</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --azul-primario: #0d6efd;
            --azul-escuro: #084298;
            --azul-claro: #e7f1ff;
            --cinza-fundo: #f5f7fa;
            --cinza-borda: #e3e6ea;
            --texto-escuro: #1f2d3d;
            --sidebar-larg: 250px;
        }
        body { background: var(--cinza-fundo); font-family: 'Segoe UI', Tahoma, sans-serif; color: var(--texto-escuro); overflow-x: hidden; }
        .navbar-topo { background: linear-gradient(90deg, var(--azul-primario) 0%, var(--azul-escuro) 100%); height: 60px; box-shadow: 0 2px 8px rgba(0,0,0,.08); position: fixed; top: 0; left: 0; right: 0; z-index: 1030; }
        .navbar-topo .navbar-brand { color: #fff; font-weight: 600; font-size: 1.25rem; }
        .btn-sanduiche { background: transparent; border: none; color: #fff; font-size: 1.3rem; padding: 6px 12px; border-radius: 6px; }
        .btn-sanduiche:hover { background: rgba(255,255,255,.15); }
        .operador-toggle { background: transparent; border: none; color: #fff; display: flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 30px; }
        .operador-toggle:hover,.operador-toggle:focus { background: rgba(255,255,255,.15); color: #fff; }
        .dropdown-menu-operador { min-width: 220px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,.12); border: none; }
        .sidebar { position: fixed; top: 60px; left: 0; width: var(--sidebar-larg); height: calc(100vh - 60px); background: #fff; border-right: 1px solid var(--cinza-borda); padding: 20px 0; overflow-y: auto; z-index: 1020; transition: transform .3s ease; }
        .sidebar.oculta { transform: translateX(calc(var(--sidebar-larg) * -1)); }
        .sidebar .nav-link { color: var(--texto-escuro); padding: 12px 20px; border-left: 3px solid transparent; transition: all .2s; display: flex; align-items: center; gap: 12px; }
        .sidebar .nav-link i { width: 22px; color: var(--azul-primario); font-size: 1.05rem; }
        .sidebar .nav-link:hover,.sidebar .nav-link.ativo { background: var(--azul-claro); border-left-color: var(--azul-primario); color: var(--azul-escuro); font-weight: 600; }
        .sidebar-overlay { display: none; position: fixed; top: 60px; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.4); z-index: 1010; }
        .sidebar-overlay.ativo { display: block; }
        .conteudo-principal { margin-top: 60px; margin-left: var(--sidebar-larg); padding: 25px; min-height: calc(100vh - 60px); }
        @media (max-width: 991.98px) { .sidebar { transform: translateX(calc(var(--sidebar-larg) * -1)); } .sidebar.aberta { transform: translateX(0); box-shadow: 2px 0 12px rgba(0,0,0,.15); } .conteudo-principal { margin-left: 0; } }
        .card-pagina { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.05); border: 1px solid var(--cinza-borda); padding: 24px; }
        .card-pagina h1 { font-size: 1.4rem; font-weight: 700; color: var(--azul-escuro); }
        .card-pagina ul { margin-bottom: 0; }
    </style>
</head>
<body>
    <nav class="navbar-topo d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn-sanduiche" id="btnSanduiche" type="button"><i class="fa-solid fa-bars"></i></button>
            <a class="navbar-brand mb-0 d-flex align-items-center" href="principal.php"><i class="fa-solid fa-stethoscope"></i><span>MediAgenda</span></a>
        </div>
        <div class="dropdown">
            <button class="operador-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-circle-user"></i><span class="d-none d-md-inline"><?php echo htmlspecialchars($operadorNome) ?></span><i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i></button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-operador">
                <li><span class="dropdown-item-text"><i class="fa-solid fa-user me-2"></i><?php echo htmlspecialchars($operadorNome) ?></span></li>
                <li><span class="dropdown-item-text"><i class="fa-solid fa-envelope me-2"></i><?php echo htmlspecialchars($operadorEmail) ?></span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Sair</a></li>
            </ul>
        </div>
    </nav>

    <aside class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="principal.php"><i class="fa-solid fa-calendar-days"></i> Agenda</a></li>
            <li class="nav-item"><a class="nav-link" href="cadastro_agendas.php"><i class="fa-solid fa-calendar-plus"></i> Agendamentos</a></li>
            <li class="nav-item"><a class="nav-link" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</a></li>
            <li class="nav-item"><a class="nav-link" href="cadastro_especialidades.php"><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</a></li>
            <li class="nav-item"><a class="nav-link ativo" href="regras_trabalho.php"><i class="fa-solid fa-clipboard-list"></i> Regras do trabalho</a></li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="conteudo-principal" id="conteudoPrincipal">
        <div class="card-pagina">
            <h1 class="mb-3"><i class="fa-solid fa-clipboard-list me-2"></i>Regras do trabalho</h1>
            <p>Esta página registra as orientações da atividade e os critérios que o grupo deve cumprir.</p>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">CRUD completo de médicos com listagem, edição e inativação.</li>
                <li class="list-group-item">CRUD completo de especialidades com integração ao cadastro de médicos.</li>
                <li class="list-group-item">Navegação lateral funcionando para Agenda, Agendamentos, Médicos, Especialidades e Regras do trabalho.</li>
                <li class="list-group-item">Banco de dados alinhado ao `script.sql` atualizado.</li>
                <li class="list-group-item">README com descrição, tecnologias, execução e integrantes do grupo.</li>
            </ul>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var btnSanduiche = document.getElementById('btnSanduiche');
        var sidebar = document.getElementById('sidebar');
        var conteudoPrincipal = document.getElementById('conteudoPrincipal');
        var sidebarOverlay = document.getElementById('sidebarOverlay');

        btnSanduiche.addEventListener('click', function () {
            if (window.innerWidth <= 991.98) {
                sidebar.classList.toggle('aberta');
                sidebarOverlay.classList.toggle('ativo');
            } else {
                sidebar.classList.toggle('oculta');
                conteudoPrincipal.classList.toggle('expandido');
            }
        });

        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('aberta');
            sidebarOverlay.classList.remove('ativo');
        });
    </script>
</body>
</html>
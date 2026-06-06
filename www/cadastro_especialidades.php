<?php
/* ============================================================
   cadastro_especialidades.php - Cadastro de Especialidades
============================================================ */

session_start();
require_once("conexao.php");

if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

if (!isset($_SESSION['cod_usuario'])) {
    header("Location: login.php");
    exit;
}
$cod_usuario = $_SESSION['cod_usuario'];
$nomeUsuario  = "";
$emailUsuario = "";
$sql = "SELECT * FROM usuario WHERE cod_usuario = '$cod_usuario'";
$result = mysqli_query($conexao_bd, $sql);
if ($consulta = mysqli_fetch_assoc($result)) {
    $nomeUsuario  = $consulta['nome'];
    $emailUsuario = $consulta['email'];
}

$operadorNome  = $nomeUsuario;
$operadorEmail = $emailUsuario;

/* ============================================================
   PROCESSAMENTO DE AÇÕES (POST) - CRUD com o banco
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';

    if ($acao === 'novo') {
        $nome      = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $status    = isset($_POST['status']) ? $_POST['status'] : 'Ativa';

        $nome_esc = mysqli_real_escape_string($conexao_bd, $nome);
        $desc_esc = mysqli_real_escape_string($conexao_bd, $descricao);
        $sql = "INSERT INTO especialidades (nome, descricao, status)
                VALUES ('{$nome_esc}', '{$desc_esc}', '{$status}')";
        mysqli_query($conexao_bd, $sql);

    } elseif ($acao === 'editar') {
        $id        = intval($_POST['id']);
        $nome      = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $status    = isset($_POST['status']) ? $_POST['status'] : 'Ativa';

        $nome_esc = mysqli_real_escape_string($conexao_bd, $nome);
        $desc_esc = mysqli_real_escape_string($conexao_bd, $descricao);
        $sql = "UPDATE especialidades
                SET nome='{$nome_esc}', descricao='{$desc_esc}', status='{$status}'
                WHERE id = {$id}";
        mysqli_query($conexao_bd, $sql);

    } elseif ($acao === 'excluir') {
        $id  = intval($_POST['id']);
        $sql = "UPDATE especialidades SET status='Inativa' WHERE id = {$id}";
        mysqli_query($conexao_bd, $sql);
    }

    header('Location: cadastro_especialidades.php');
    exit;
}

/* ============================================================
   FILTROS DE BUSCA
============================================================ */
$filtroNome   = trim(isset($_GET['nome'])   ? $_GET['nome']   : '');
$filtroStatus = trim(isset($_GET['status']) ? $_GET['status'] : '');

/* ============================================================
   Consulta de especialidades com filtros aplicados
============================================================ */
$especialidades = array();
$sql = "SELECT id, nome, descricao, status FROM especialidades";

$conds = array();
if ($filtroNome !== '') {
    $conds[] = "nome LIKE '%" . mysqli_real_escape_string($conexao_bd, $filtroNome) . "%'";
}
if ($filtroStatus !== '') {
    $conds[] = "status = '" . mysqli_real_escape_string($conexao_bd, $filtroStatus) . "'";
}
if (!empty($conds)) {
    $sql .= ' WHERE ' . implode(' AND ', $conds);
}
$sql .= ' ORDER BY nome ASC';

$resAll = mysqli_query($conexao_bd, $sql);
if ($resAll) {
    while ($row = mysqli_fetch_assoc($resAll)) {
        $especialidades[] = $row;
    }
    mysqli_free_result($resAll);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Cadastro de Especialidades</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --azul-primario: #0d6efd;
            --azul-escuro:   #084298;
            --azul-claro:    #e7f1ff;
            --cinza-fundo:   #f5f7fa;
            --cinza-borda:   #e3e6ea;
            --texto-escuro:  #1f2d3d;
            --sidebar-larg:  250px;
        }

        body {
            background-color: var(--cinza-fundo);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            color: var(--texto-escuro);
            overflow-x: hidden;
        }

        /* ==================== NAVBAR SUPERIOR ==================== */
        .navbar-topo {
            background: linear-gradient(90deg, var(--azul-primario) 0%, var(--azul-escuro) 100%);
            height: 60px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
        }
        .navbar-topo .navbar-brand {
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
        }
        .navbar-topo .navbar-brand i {
            margin-right: 8px;
        }
        .btn-sanduiche {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .btn-sanduiche:hover {
            background: rgba(255,255,255,0.15);
        }
        .operador-toggle {
            background: transparent;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 30px;
            transition: background 0.2s;
        }
        .operador-toggle:hover, .operador-toggle:focus {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .operador-toggle i.fa-circle-user {
            font-size: 1.6rem;
        }
        .dropdown-menu-operador {
            min-width: 220px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            border: none;
        }
        .dropdown-menu-operador .dropdown-item i {
            width: 22px;
            color: var(--azul-primario);
        }

        /* ==================== SIDEBAR LATERAL ==================== */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: var(--sidebar-larg);
            height: calc(100vh - 60px);
            background: #fff;
            border-right: 1px solid var(--cinza-borda);
            padding: 20px 0;
            transition: transform 0.3s ease;
            z-index: 1020;
            overflow-y: auto;
        }
        .sidebar.oculta {
            transform: translateX(calc(var(--sidebar-larg) * -1));
        }
        .sidebar .nav-link {
            color: var(--texto-escuro);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar .nav-link i {
            width: 22px;
            color: var(--azul-primario);
            font-size: 1.05rem;
        }
        .sidebar .nav-link:hover {
            background: var(--azul-claro);
            border-left-color: var(--azul-primario);
            color: var(--azul-escuro);
        }
        .sidebar .nav-link.ativo {
            background: var(--azul-claro);
            border-left-color: var(--azul-primario);
            color: var(--azul-escuro);
            font-weight: 600;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 60px; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1010;
        }
        .sidebar-overlay.ativo {
            display: block;
        }

        /* ==================== CONTEÚDO PRINCIPAL ==================== */
        .conteudo-principal {
            margin-top: 60px;
            margin-left: var(--sidebar-larg);
            padding: 25px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 60px);
        }
        .conteudo-principal.expandido {
            margin-left: 0;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(calc(var(--sidebar-larg) * -1));
            }
            .sidebar.aberta {
                transform: translateX(0);
                box-shadow: 2px 0 12px rgba(0,0,0,0.15);
            }
            .conteudo-principal {
                margin-left: 0;
            }
        }

        /* ==================== CABEÇALHO DA PÁGINA ==================== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 22px;
        }
        .page-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--azul-escuro);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header h2 i {
            color: var(--azul-primario);
        }

        /* ==================== CARD GENÉRICO ==================== */
        .card-pagina {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid var(--cinza-borda);
            padding: 20px 24px;
            margin-bottom: 20px;
        }
        .card-pagina .card-titulo {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--azul-escuro);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-pagina .card-titulo i {
            color: var(--azul-primario);
        }

        /* ==================== TABELA ==================== */
        .tabela-especialidades {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.88rem;
        }
        .tabela-especialidades thead th {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-weight: 600;
            padding: 10px 14px;
            border-bottom: 2px solid var(--cinza-borda);
            white-space: nowrap;
        }
        .tabela-especialidades tbody tr {
            transition: background 0.15s;
        }
        .tabela-especialidades tbody tr:hover {
            background: #f8fbff;
        }
        .tabela-especialidades tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--cinza-borda);
            vertical-align: middle;
        }
        .tabela-especialidades tbody tr:last-child td {
            border-bottom: none;
        }

        /* ==================== BADGES DE STATUS ==================== */
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .badge-ativo {
            background: #d1e7dd;
            color: #0a3622;
        }
        .badge-inativo {
            background: #f8d7da;
            color: #58151c;
        }

        /* ==================== ÍCONE DA ESPECIALIDADE ==================== */
        .icone-especialidade {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--azul-claro);
            color: var(--azul-primario);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            margin-right: 8px;
            flex-shrink: 0;
        }

        /* ==================== MODAL ==================== */
        .modal-form .modal-header {
            background: var(--azul-primario);
            color: #fff;
        }
        .modal-form .modal-header .btn-close {
            filter: invert(1);
        }
        .modal-form label {
            font-weight: 500;
            font-size: 0.88rem;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <!-- ==================================================
         NAVBAR SUPERIOR
    ================================================== -->
    <nav class="navbar-topo d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn-sanduiche" id="btnSanduiche" title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="navbar-brand mb-0 d-flex align-items-center" href="principal.php">
                <i class="fa-solid fa-stethoscope"></i>
                <span>MediAgenda</span>
            </a>
        </div>

        <div class="dropdown">
            <button class="operador-toggle" type="button" id="dropdownOperador" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-circle-user"></i>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($operadorNome) ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-operador" aria-labelledby="dropdownOperador">
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user"></i><?php echo htmlspecialchars($operadorNome) ?></a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-envelope"></i><?php echo htmlspecialchars($operadorEmail) ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear"></i>Configurações</a></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sair</a></li>
            </ul>
        </div>
    </nav>

    <!-- ==================================================
         SIDEBAR LATERAL
    ================================================== -->
    <aside class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="principal.php"><i class="fa-solid fa-calendar-days"></i> Agenda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cadastro_agendas.php"><i class="fa-solid fa-calendar-plus"></i> Agendamentos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link ativo" href="cadastro_especialidades.php"><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="regras_trabalho.php"><i class="fa-solid fa-clipboard-list"></i> Regras do trabalho</a>
            </li>
        </ul>
    </aside>

    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ==================================================
         CONTEÚDO PRINCIPAL
    ================================================== -->
    <main class="conteudo-principal" id="conteudoPrincipal">

        <!-- Cabeçalho da página -->
        <div class="page-header">
            <h2><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFormEspecialidade">
                <i class="fa-solid fa-plus me-1"></i> Nova Especialidade
            </button>
        </div>

        <!-- FILTROS DE BUSCA -->
        <div class="card-pagina">
            <div class="card-titulo"><i class="fa-solid fa-magnifying-glass"></i> Filtros</div>
            <form method="GET" action="cadastro_especialidades.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="filtroNome">Nome</label>
                        <input type="text" class="form-control form-control-sm" id="filtroNome"
                               name="nome" placeholder="Nome da especialidade"
                               value="<?php echo htmlspecialchars($filtroNome) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroStatus">Status</label>
                        <select class="form-select form-select-sm" id="filtroStatus" name="status">
                            <option value="">Todos</option>
                            <option value="Ativa"   <?php echo ($filtroStatus === 'Ativa')   ? 'selected' : '' ?>>Ativa</option>
                            <option value="Inativa" <?php echo ($filtroStatus === 'Inativa') ? 'selected' : '' ?>>Inativa</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar
                    </button>
                    <a href="cadastro_especialidades.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-xmark me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- TABELA DE ESPECIALIDADES -->
        <div class="card-pagina">
            <div class="card-titulo d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-table-list"></i> Especialidades</span>
                <span id="contadorRegistros" class="text-muted" style="font-size:0.82rem; font-weight:400;">
                    <?php echo count($especialidades) ?> registro(s) encontrado(s)
                </span>
            </div>

            <div class="table-responsive">
                <table class="tabela-especialidades">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($especialidades)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-list-xmark me-2"></i>Nenhuma especialidade encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($especialidades as $esp):
                                $classeBadge = ($esp['status'] === 'Ativa') ? 'badge-ativo' : 'badge-inativo';
                            ?>
                            <tr>
                                <td class="text-muted"><?php echo $esp['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="icone-especialidade">
                                            <i class="fa-solid fa-stethoscope"></i>
                                        </span>
                                        <?php echo htmlspecialchars($esp['nome']) ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($esp['descricao'] ?? '—') ?></td>
                                <td><span class="badge-status <?php echo $classeBadge ?>"><?php echo htmlspecialchars($esp['status']) ?></span></td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2 btn-editar"
                                            title="Editar"
                                            data-id="<?php echo $esp['id'] ?>"
                                            data-nome="<?php echo htmlspecialchars($esp['nome']) ?>"
                                            data-descricao="<?php echo htmlspecialchars($esp['descricao'] ?? '') ?>"
                                            data-status="<?php echo htmlspecialchars($esp['status']) ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-excluir"
                                            title="Excluir especialidade"
                                            data-id="<?php echo $esp['id'] ?>"
                                            data-nome="<?php echo htmlspecialchars($esp['nome']) ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <nav aria-label="Paginação">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
                    </ul>
                </nav>
            </div>
        </div>

    </main>

    <!-- ==================================================
         MODAL — NOVA / EDITAR ESPECIALIDADE
    ================================================== -->
    <div class="modal fade modal-form" id="modalFormEspecialidade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormTitulo">
                        <i class="fa-solid fa-plus me-2"></i>Nova Especialidade
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <form id="formEspecialidade" action="cadastro_especialidades.php" method="POST">
                    <input type="hidden" name="acao" id="formAcao" value="novo">
                    <input type="hidden" name="id"   id="formId"   value="">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="formNome">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="formNome" name="nome"
                                       placeholder="Ex: Cardiologia" required>
                            </div>
                            <div class="col-12">
                                <label for="formDescricao">Descrição</label>
                                <textarea class="form-control" id="formDescricao" name="descricao"
                                          rows="3" placeholder="Descrição da especialidade..."></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="formStatus">Status</label>
                                <select class="form-select" id="formStatus" name="status">
                                    <option value="Ativa">Ativa</option>
                                    <option value="Inativa">Inativa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================ SCRIPTS ================ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==================================================
        // TOGGLE DA SIDEBAR (responsivo)
        // ==================================================
        var btnSanduiche      = document.getElementById('btnSanduiche');
        var sidebar           = document.getElementById('sidebar');
        var conteudoPrincipal = document.getElementById('conteudoPrincipal');
        var sidebarOverlay    = document.getElementById('sidebarOverlay');

        btnSanduiche.addEventListener('click', function() {
            if (window.innerWidth <= 991.98) {
                sidebar.classList.toggle('aberta');
                sidebarOverlay.classList.toggle('ativo');
            } else {
                sidebar.classList.toggle('oculta');
                conteudoPrincipal.classList.toggle('expandido');
            }
        });
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('aberta');
            sidebarOverlay.classList.remove('ativo');
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('aberta');
                sidebarOverlay.classList.remove('ativo');
            }
        });

        // ==================================================
        // MODAL — CONTROLE DE MODO (novo / editar)
        // ==================================================
        var modalFormEspEl = document.getElementById('modalFormEspecialidade');
        var modalFormEsp   = new bootstrap.Modal(modalFormEspEl);
        var modoEdicao     = false;

        modalFormEspEl.addEventListener('show.bs.modal', function() {
            if (!modoEdicao) {
                document.getElementById('modalFormTitulo').innerHTML =
                    '<i class="fa-solid fa-plus me-2"></i>Nova Especialidade';
                document.getElementById('formAcao').value = 'novo';
                document.getElementById('formId').value   = '';
                document.getElementById('formEspecialidade').reset();
            }
            modoEdicao = false;
        });

        // ==================================================
        // EVENT DELEGATION — Editar e Excluir
        // ==================================================
        document.querySelector('.tabela-especialidades').addEventListener('click', function(e) {
            var btnEditar  = e.target.closest('.btn-editar');
            var btnExcluir = e.target.closest('.btn-excluir');

            if (btnEditar) {
                modoEdicao = true;
                document.getElementById('modalFormTitulo').innerHTML =
                    '<i class="fa-solid fa-pen me-2"></i>Editar Especialidade';
                document.getElementById('formAcao').value      = 'editar';
                document.getElementById('formId').value        = btnEditar.dataset.id;
                document.getElementById('formNome').value      = btnEditar.dataset.nome;
                document.getElementById('formDescricao').value = btnEditar.dataset.descricao;
                document.getElementById('formStatus').value    = btnEditar.dataset.status;
                modalFormEsp.show();
            }

            if (btnExcluir) {
                Swal.fire({
                    title: 'Excluir especialidade?',
                    html: 'Deseja excluir <strong>' + btnExcluir.dataset.nome + '</strong>?',
                    icon: 'warning',
                    showCancelButton:   true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor:  '#6c757d',
                    confirmButtonText:  'Sim, excluir',
                    cancelButtonText:   'Voltar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var f = document.createElement('form');
                        f.method = 'POST';
                        f.action = 'cadastro_especialidades.php';
                        var a = document.createElement('input'); a.type='hidden'; a.name='acao'; a.value='excluir';
                        var i = document.createElement('input'); i.type='hidden'; i.name='id';   i.value=btnExcluir.dataset.id;
                        f.appendChild(a); f.appendChild(i); document.body.appendChild(f); f.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>
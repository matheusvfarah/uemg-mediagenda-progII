<?php
session_start();
require_once("conexao.php");// importar o conexao.php para esta página

if(!isset($_SESSION['cod_usuario'])){
    header("Location: login.php");
    exit;
}

$cod_usuario = $_SESSION['cod_usuario'];
$nomeUsuario = $_SESSION['nome'];
$emailUsuario = $_SESSION['email'];

$operadorNome  = $nomeUsuario;
$operadorEmail = $emailUsuario;

$sql = "SELECT * FROM vw_medicos WHERE 1=1"; // Já deixamos o WHERE 1=1 aqui para facilitar

// 1. Puxando os valores corretos baseados nos 'names' do formulário HTML
$filtroNome          = $_GET['nome'] ?? '';
$filtroEspecialidade = $_GET['especialidade'] ?? '';
$filtroStatus        = $_GET['status'] ?? '';

// 2. Aplicando os filtros na query se eles foram preenchidos
if ($filtroNome != '') {
    // DICA: Usar mysqli_real_escape_string previne SQL Injection (segurança)
    $nomeSeguro = mysqli_real_escape_string($conexao_bd, $filtroNome);
    $sql .= " AND nome LIKE '%" . $nomeSeguro . "%'";
}

if ($filtroEspecialidade != '') {
    $espSegura = mysqli_real_escape_string($conexao_bd, $filtroEspecialidade);
    $sql .= " AND especialidade LIKE '%" . $espSegura . "%'";
}

if ($filtroStatus != '') {
    $statusSeguro = mysqli_real_escape_string($conexao_bd, $filtroStatus);
    $sql .= " AND status = '" . $statusSeguro . "'";
}

// 3. Só agora executamos a query
$result = mysqli_query($conexao_bd, $sql);

$medicos = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $medicos[] = $row;
    }
} else {
    echo "Erro ao buscar médicos: " . mysqli_error($conexao_bd);
    exit;
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MediAgenda - Cadastro de médicos</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <!-- ================ CDNs ================ -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
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

        /* Overlay (em mobile, escurece o fundo quando sidebar aberta) */
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
        .tabela-agendamentos {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.88rem;
        }
        .tabela-agendamentos thead th {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-weight: 600;
            padding: 10px 14px;
            border-bottom: 2px solid var(--cinza-borda);
            white-space: nowrap;
        }
        .tabela-agendamentos tbody tr {
            transition: background 0.15s;
        }
        .tabela-agendamentos tbody tr:hover {
            background: #f8fbff;
        }
        .tabela-agendamentos tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--cinza-borda);
            vertical-align: middle;
        }
        .tabela-agendamentos tbody tr:last-child td {
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
        .badge-confirmado {
            background: #d1e7dd;
            color: #0a3622;
        }
        .badge-pendente {
            background: #fff3cd;
            color: #664d03;
        }
        .badge-cancelado {
            background: #f8d7da;
            color: #58151c;
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
</style>

<body>
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
                <span class="d-none d-md-inline"><?php echo $operadorNome ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-operador" aria-labelledby="dropdownOperador">
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user"></i><?php echo htmlspecialchars($operadorNome) ?></a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-envelope"></i><?php echo htmlspecialchars($operadorEmail) ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear"></i>Configurações</a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-right-from-bracket"></i>Sair</a></li>
            </ul>
        </div>
    </nav>
    <aside class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="principal.php"><i class="fa-solid fa-calendar-days"></i> Calendário</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cadastro_agendas.php"><i class="fa-solid fa-calendar-plus"></i> Agendamentos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link ativo" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cadastro_especialidades.php"><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</a>
            </li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

<main class="conteudo-principal" id="conteudoPrincipal">

        <div class="page-header">
            <h2><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFormMedico">
                <i class="fa-solid fa-plus me-1"></i> Novo Médico
            </button>
        </div>

        <div class="card-pagina">
            <div class="card-titulo"><i class="fa-solid fa-magnifying-glass"></i> Filtros</div>
            <form method="GET" action="cadastro_medicos.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="filtroNome">Nome do Médico</label>
                        <input type="text" class="form-control form-control-sm" id="filtroNome"
                               name="nome" placeholder="Digite o nome do médico"
                               value="<?php echo htmlspecialchars($filtroNome ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroEspecialidade">Especialidade</label>
                        <input type="text" class="form-control form-control-sm" id="filtroEspecialidade"
                               name="especialidade" placeholder="Ex: Cardiologia"
                               value="<?php echo htmlspecialchars($filtroEspecialidade ?? '') ?>">
                    </div>
                     <div class="col-md-4">
                        <label for="filtroStatus">Status</label>
                        <select class="form-select form-select-sm" id="filtroStatus" name="status">
                            <option value="">Todos</option>
                            <option value="Ativo" <?php echo (isset($filtroStatus) && $filtroStatus === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($filtroStatus) && $filtroStatus === 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar
                    </button>
                    <a href="cadastro_medicos.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-xmark me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="card-pagina">
            <div class="card-titulo d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-table-list"></i> Médicos Cadastrados</span>
                <span id="contadorRegistros" class="text-muted" style="font-size:0.82rem; font-weight:400;">
                    <?php echo count($medicos ?? []) ?> registro(s) encontrado(s)
                </span>
            </div>

            <div class="table-responsive">
                <table class="tabela-agendamentos"> <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CRM</th>
                            <th>Especialidade</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($medicos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-user-xmark me-2"></i>Nenhum médico encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($medicos as $med): 
                                // Define classe do badge conforme status
                                if (isset($med['status']) && $med['status'] === 'Ativo') {
                                    $classeBadge = 'badge-confirmado'; 
                                } else {
                                    $classeBadge = 'badge-cancelado'; 
                                }
                            ?>
                            <tr>
                                <td class="text-muted"><?php echo $med['id'] ?></td>
                                <td class="fw-medium"><?php echo htmlspecialchars($med['nome']) ?></td>
                                <td><?php echo htmlspecialchars($med['crm'] ?? 'N/A') ?></td>
                                <td><?php echo htmlspecialchars($med['especialidade']) ?></td>
                                <td><?php echo htmlspecialchars($med['telefone'] ?? 'N/A') ?></td>
                                <td><?php echo htmlspecialchars($med['email'] ?? 'N/A') ?></td>
                                <td><span class="badge-status <?php echo $classeBadge ?>"><?php echo htmlspecialchars($med['status'] ?? 'Ativo') ?></span></td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2 btn-editar-medico"
                                            title="Editar Médico"
                                            data-id="<?php echo $med['id'] ?>"
                                            data-nome="<?php echo htmlspecialchars($med['nome']) ?>"
                                            data-crm="<?php echo htmlspecialchars($med['crm'] ?? '') ?>"
                                            data-especialidade="<?php echo htmlspecialchars($med['especialidade']) ?>"
                                            data-telefone="<?php echo htmlspecialchars($med['telefone'] ?? '') ?>"
                                            data-email="<?php echo htmlspecialchars($med['email'] ?? '') ?>"
                                            data-status="<?php echo htmlspecialchars($med['status'] ?? 'Ativo') ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-excluir-medico"
                                            title="Excluir Médico"
                                            data-id="<?php echo $med['id'] ?>"
                                            data-nome="<?php echo htmlspecialchars($med['nome']) ?>">
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

</body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
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
        
    </script>
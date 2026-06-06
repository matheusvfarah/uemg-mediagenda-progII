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

function redirecionarMedicos(string $tipo, string $mensagem): void
{
    $_SESSION['flash_medicos'] = [
        'tipo' => $tipo,
        'mensagem' => $mensagem,
    ];
    header('Location: cadastro_medicos.php');
    exit;
}

$nomeUsuario = $_SESSION['nome'] ?? '';
$emailUsuario = $_SESSION['email'] ?? '';
$operadorNome = $nomeUsuario;
$operadorEmail = $emailUsuario;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'novo' || $acao === 'editar') {
        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $crm = trim($_POST['crm'] ?? '');
        $especialidade_id = (int)($_POST['especialidade_id'] ?? 0);
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = ($_POST['status'] ?? 'Ativo') === 'Inativo' ? 'Inativo' : 'Ativo';

        if ($nome === '' || $crm === '' || $especialidade_id <= 0) {
            redirecionarMedicos('error', 'Preencha nome, CRM e especialidade.');
        }

        if ($acao === 'novo') {
            $stmt = mysqli_prepare($conexao_bd, 'INSERT INTO medicos (nome, crm, especialidade_id, telefone, email, status) VALUES (?, ?, ?, ?, ?, ?)');
            if (!$stmt) {
                redirecionarMedicos('error', 'Não foi possível preparar o cadastro do médico.');
            }
            mysqli_stmt_bind_param($stmt, 'ssisss', $nome, $crm, $especialidade_id, $telefone, $email, $status);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                redirecionarMedicos('error', 'Erro ao cadastrar médico. Verifique se o CRM já existe.');
            }
            mysqli_stmt_close($stmt);
            redirecionarMedicos('success', 'Médico cadastrado com sucesso.');
        }

        $stmt = mysqli_prepare($conexao_bd, 'UPDATE medicos SET nome = ?, crm = ?, especialidade_id = ?, telefone = ?, email = ?, status = ? WHERE id = ?');
        if (!$stmt) {
            redirecionarMedicos('error', 'Não foi possível preparar a atualização do médico.');
        }
        mysqli_stmt_bind_param($stmt, 'ssisssi', $nome, $crm, $especialidade_id, $telefone, $email, $status, $id);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirecionarMedicos('error', 'Erro ao atualizar médico.');
        }
        mysqli_stmt_close($stmt);
        redirecionarMedicos('success', 'Médico atualizado com sucesso.');
    }

    if ($acao === 'inativar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirecionarMedicos('error', 'ID inválido para inativação.');
        }

        $stmt = mysqli_prepare($conexao_bd, "UPDATE medicos SET status = 'Inativo' WHERE id = ?");
        if (!$stmt) {
            redirecionarMedicos('error', 'Não foi possível preparar a exclusão do médico.');
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirecionarMedicos('error', 'Erro ao inativar médico.');
        }
        mysqli_stmt_close($stmt);
        redirecionarMedicos('success', 'Médico inativado com sucesso.');
    }
}

$filtroNome = trim($_GET['nome'] ?? '');
$filtroEspecialidade = trim($_GET['especialidade'] ?? '');
$filtroStatus = trim($_GET['status'] ?? '');

$especialidades = [];
$resEspecialidades = mysqli_query($conexao_bd, 'SELECT id, nome FROM especialidades ORDER BY nome ASC');
if ($resEspecialidades) {
    while ($row = mysqli_fetch_assoc($resEspecialidades)) {
        $especialidades[] = $row;
    }
}

$sql = "
    SELECT
        m.id,
        m.nome,
        m.crm,
        m.especialidade_id,
        e.nome AS especialidade,
        m.telefone,
        m.email,
        m.status
    FROM medicos m
    INNER JOIN especialidades e ON e.id = m.especialidade_id
    WHERE 1 = 1
";

if ($filtroNome !== '') {
    $sql .= " AND m.nome LIKE '%" . mysqli_real_escape_string($conexao_bd, $filtroNome) . "%'";
}

if ($filtroEspecialidade !== '') {
    $sql .= " AND e.nome LIKE '%" . mysqli_real_escape_string($conexao_bd, $filtroEspecialidade) . "%'";
}

if ($filtroStatus !== '') {
    $sql .= " AND m.status = '" . mysqli_real_escape_string($conexao_bd, $filtroStatus) . "'";
}

$sql .= ' ORDER BY m.nome ASC';

$medicos = [];
$resMedicos = mysqli_query($conexao_bd, $sql);
if ($resMedicos) {
    while ($row = mysqli_fetch_assoc($resMedicos)) {
        $medicos[] = $row;
    }
}

$flashMedicos = $_SESSION['flash_medicos'] ?? null;
unset($_SESSION['flash_medicos']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Cadastro de Médicos</title>
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

        body {
            background-color: var(--cinza-fundo);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            color: var(--texto-escuro);
            overflow-x: hidden;
        }

        .navbar-topo {
            background: linear-gradient(90deg, var(--azul-primario) 0%, var(--azul-escuro) 100%);
            height: 60px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }

        .navbar-topo .navbar-brand {
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .btn-sanduiche {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .btn-sanduiche:hover {
            background: rgba(255, 255, 255, 0.15);
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
        }

        .operador-toggle:hover,
        .operador-toggle:focus {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .dropdown-menu-operador {
            min-width: 220px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            border: none;
        }

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: var(--sidebar-larg);
            height: calc(100vh - 60px);
            background: #fff;
            border-right: 1px solid var(--cinza-borda);
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1020;
            transition: transform 0.3s ease;
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

        .sidebar .nav-link:hover,
        .sidebar .nav-link.ativo {
            background: var(--azul-claro);
            border-left-color: var(--azul-primario);
            color: var(--azul-escuro);
            font-weight: 600;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1010;
        }

        .sidebar-overlay.ativo {
            display: block;
        }

        .conteudo-principal {
            margin-top: 60px;
            margin-left: var(--sidebar-larg);
            padding: 25px;
            min-height: calc(100vh - 60px);
            transition: margin-left 0.3s ease;
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
                box-shadow: 2px 0 12px rgba(0, 0, 0, 0.15);
            }

            .conteudo-principal {
                margin-left: 0;
            }
        }

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

        .card-pagina {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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

        .tabela-medicos {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.88rem;
        }

        .tabela-medicos thead th {
            background: var(--azul-claro);
            color: var(--azul-escuro);
            font-weight: 600;
            padding: 10px 14px;
            border-bottom: 2px solid var(--cinza-borda);
            white-space: nowrap;
        }

        .tabela-medicos tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--cinza-borda);
            vertical-align: middle;
        }

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
    <nav class="navbar-topo d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn-sanduiche" id="btnSanduiche" title="Menu" type="button">
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
            <li class="nav-item"><a class="nav-link ativo" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</a></li>
            <li class="nav-item"><a class="nav-link" href="cadastro_especialidades.php"><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</a></li>
            <li class="nav-item"><a class="nav-link" href="regras_trabalho.php"><i class="fa-solid fa-clipboard-list"></i> Regras do trabalho</a></li>
        </ul>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="conteudo-principal" id="conteudoPrincipal">
        <div class="page-header">
            <h2><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</h2>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalFormMedico">
                <i class="fa-solid fa-plus me-1"></i> Novo Médico
            </button>
        </div>

        <div class="card-pagina">
            <div class="card-titulo"><i class="fa-solid fa-magnifying-glass"></i> Filtros</div>
            <form method="GET" action="cadastro_medicos.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="filtroNome">Nome do Médico</label>
                        <input type="text" class="form-control form-control-sm" id="filtroNome" name="nome" value="<?php echo htmlspecialchars($filtroNome) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroEspecialidade">Especialidade</label>
                        <input type="text" class="form-control form-control-sm" id="filtroEspecialidade" name="especialidade" value="<?php echo htmlspecialchars($filtroEspecialidade) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroStatus">Status</label>
                        <select class="form-select form-select-sm" id="filtroStatus" name="status">
                            <option value="">Todos</option>
                            <option value="Ativo" <?php echo $filtroStatus === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?php echo $filtroStatus === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar</button>
                    <a href="cadastro_medicos.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-xmark me-1"></i> Limpar</a>
                </div>
            </form>
        </div>

        <div class="card-pagina">
            <div class="card-titulo d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-table-list"></i> Médicos Cadastrados</span>
                <span class="text-muted" style="font-size:0.82rem; font-weight:400;"><?php echo count($medicos) ?> registro(s) encontrado(s)</span>
            </div>

            <div class="table-responsive">
                <table class="tabela-medicos">
                    <thead>
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
                                <td colspan="8" class="text-center text-muted py-4"><i class="fa-solid fa-user-xmark me-2"></i>Nenhum médico encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($medicos as $med): ?>
                                <?php $badge = $med['status'] === 'Ativo' ? 'badge-ativo' : 'badge-inativo'; ?>
                                <tr>
                                    <td class="text-muted"><?php echo (int)$med['id'] ?></td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($med['nome']) ?></td>
                                    <td><?php echo htmlspecialchars($med['crm']) ?></td>
                                    <td><?php echo htmlspecialchars($med['especialidade']) ?></td>
                                    <td><?php echo htmlspecialchars($med['telefone'] ?: '—') ?></td>
                                    <td><?php echo htmlspecialchars($med['email'] ?: '—') ?></td>
                                    <td><span class="badge-status <?php echo $badge; ?>"><?php echo htmlspecialchars($med['status']) ?></span></td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary py-0 px-2 btn-editar-medico"
                                            data-id="<?php echo (int)$med['id'] ?>"
                                            data-nome="<?php echo htmlspecialchars($med['nome'], ENT_QUOTES) ?>"
                                            data-crm="<?php echo htmlspecialchars($med['crm'], ENT_QUOTES) ?>"
                                            data-especialidade-id="<?php echo (int)$med['especialidade_id'] ?>"
                                            data-telefone="<?php echo htmlspecialchars($med['telefone'] ?? '', ENT_QUOTES) ?>"
                                            data-email="<?php echo htmlspecialchars($med['email'] ?? '', ENT_QUOTES) ?>"
                                            data-status="<?php echo htmlspecialchars($med['status'], ENT_QUOTES) ?>"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger py-0 px-2 btn-inativar-medico"
                                            data-id="<?php echo (int)$med['id'] ?>"
                                            data-nome="<?php echo htmlspecialchars($med['nome'], ENT_QUOTES) ?>"
                                        >
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade modal-form" id="modalFormMedico" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMedicoTitulo"><i class="fa-solid fa-plus me-2"></i>Novo Médico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form method="POST" action="cadastro_medicos.php" id="formMedico">
                    <input type="hidden" name="acao" id="formAcao" value="novo">
                    <input type="hidden" name="id" id="formId" value="">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="formNome">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="formNome" name="nome" required>
                            </div>
                            <div class="col-md-4">
                                <label for="formCrm">CRM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="formCrm" name="crm" required>
                            </div>
                            <div class="col-md-8">
                                <label for="formEspecialidade">Especialidade <span class="text-danger">*</span></label>
                                <select class="form-select" id="formEspecialidade" name="especialidade_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($especialidades as $especialidade): ?>
                                        <option value="<?php echo (int)$especialidade['id'] ?>"><?php echo htmlspecialchars($especialidade['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="formTelefone">Telefone</label>
                                <input type="text" class="form-control" id="formTelefone" name="telefone">
                            </div>
                            <div class="col-md-4">
                                <label for="formEmail">E-mail</label>
                                <input type="email" class="form-control" id="formEmail" name="email">
                            </div>
                            <div class="col-md-4">
                                <label for="formStatus">Status</label>
                                <select class="form-select" id="formStatus" name="status">
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form method="POST" action="cadastro_medicos.php" id="formInativarMedico" class="d-none">
        <input type="hidden" name="acao" value="inativar">
        <input type="hidden" name="id" id="formInativarId" value="">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var btnSanduiche = document.getElementById('btnSanduiche');
        var sidebar = document.getElementById('sidebar');
        var conteudoPrincipal = document.getElementById('conteudoPrincipal');
        var sidebarOverlay = document.getElementById('sidebarOverlay');
        var modalMedicoEl = document.getElementById('modalFormMedico');
        var modalMedico = new bootstrap.Modal(modalMedicoEl);
        var modoEdicao = false;

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

        window.addEventListener('resize', function () {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('aberta');
                sidebarOverlay.classList.remove('ativo');
            }
        });

        modalMedicoEl.addEventListener('show.bs.modal', function () {
            if (!modoEdicao) {
                document.getElementById('modalMedicoTitulo').innerHTML = '<i class="fa-solid fa-plus me-2"></i>Novo Médico';
                document.getElementById('formAcao').value = 'novo';
                document.getElementById('formId').value = '';
                document.getElementById('formMedico').reset();
                document.getElementById('formStatus').value = 'Ativo';
            }
            modoEdicao = false;
        });

        document.querySelector('.tabela-medicos').addEventListener('click', function (e) {
            var btnEditar = e.target.closest('.btn-editar-medico');
            var btnInativar = e.target.closest('.btn-inativar-medico');

            if (btnEditar) {
                modoEdicao = true;
                document.getElementById('modalMedicoTitulo').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Médico';
                document.getElementById('formAcao').value = 'editar';
                document.getElementById('formId').value = btnEditar.dataset.id;
                document.getElementById('formNome').value = btnEditar.dataset.nome;
                document.getElementById('formCrm').value = btnEditar.dataset.crm;
                document.getElementById('formEspecialidade').value = btnEditar.dataset.especialidadeId;
                document.getElementById('formTelefone').value = btnEditar.dataset.telefone;
                document.getElementById('formEmail').value = btnEditar.dataset.email;
                document.getElementById('formStatus').value = btnEditar.dataset.status;
                modalMedico.show();
            }

            if (btnInativar) {
                Swal.fire({
                    title: 'Inativar médico?',
                    html: 'Deseja inativar <strong>' + btnInativar.dataset.nome + '</strong>?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sim, inativar',
                    cancelButtonText: 'Voltar'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById('formInativarId').value = btnInativar.dataset.id;
                        document.getElementById('formInativarMedico').submit();
                    }
                });
            }
        });

        <?php if ($flashMedicos): ?>
        Swal.fire({
            icon: '<?php echo $flashMedicos['tipo'] ?>',
            title: '<?php echo $flashMedicos['tipo'] === 'success' ? 'Tudo certo' : 'Atenção' ?>',
            text: '<?php echo addslashes($flashMedicos['mensagem']) ?>',
            confirmButtonColor: '#0d6efd'
        });
        <?php endif; ?>
    </script>
</body>
</html>

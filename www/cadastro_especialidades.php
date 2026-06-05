<?php
// Conexão com o banco de dados
require_once __DIR__ . '/conexao.php';

// Mock do operador logado (para a barra superior não dar erro)
$operadorNome  = "Dr. João Silva";
$operadorEmail = "joao.silva@clinica.com";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';
    
    if ($acao === 'novo') {
        $nome = trim($_POST['nome']);
        $nome_esc = mysqli_real_escape_string($conexao_bd, $nome);
        
        $sql = "INSERT INTO especialidades (nome) VALUES ('{$nome_esc}')";
        mysqli_query($conexao_bd, $sql);
        
    } elseif ($acao === 'editar') {
        $id = intval($_POST['id']);
        $nome = trim($_POST['nome']);
        $nome_esc = mysqli_real_escape_string($conexao_bd, $nome);
        
        $sql = "UPDATE especialidades SET nome='{$nome_esc}' WHERE id = {$id}";
        mysqli_query($conexao_bd, $sql);
        
    } elseif ($acao === 'excluir') {
        $id = intval($_POST['id']);
        
        // Tenta excluir
        $sql = "DELETE FROM especialidades WHERE id = {$id}";
        mysqli_query($conexao_bd, $sql);
    }
    
    // Atualiza a página para evitar reenvio de formulário
    header('Location: cadastro_especialidades.php');
    exit;
}

// filtro
$filtroNome = trim(isset($_GET['nome']) ? $_GET['nome'] : '');
$especialidades = array();

$sql = "SELECT id, nome FROM especialidades";

if ($filtroNome !== '') {
    $sql .= " WHERE nome LIKE '%" . mysqli_real_escape_string($conexao_bd, $filtroNome) . "%'";
}

$sql .= " ORDER BY nome ASC";
$res = mysqli_query($conexao_bd, $sql);

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $especialidades[] = $row;
    }
    mysqli_free_result($res);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Cadastro de Especialidades</title>

    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --azul-primario: #0d6efd; --azul-escuro: #084298; --azul-claro: #e7f1ff;
            --cinza-fundo: #f5f7fa; --cinza-borda: #e3e6ea; --texto-escuro: #1f2d3d;
            --sidebar-larg: 250px;
        }
        body { background-color: var(--cinza-fundo); font-family: 'Segoe UI', Tahoma, sans-serif; color: var(--texto-escuro); overflow-x: hidden; }
        
        .navbar-topo { background: linear-gradient(90deg, var(--azul-primario) 0%, var(--azul-escuro) 100%); height: 60px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: fixed; top: 0; left: 0; right: 0; z-index: 1030; }
        .navbar-topo .navbar-brand { color: #fff; font-weight: 600; font-size: 1.25rem; }
        .btn-sanduiche { background: transparent; border: none; color: #fff; font-size: 1.3rem; padding: 6px 12px; border-radius: 6px; }
        .operador-toggle { background: transparent; border: none; color: #fff; display: flex; align-items: center; gap: 8px; padding: 6px 12px; }
        
        .sidebar { position: fixed; top: 60px; left: 0; width: var(--sidebar-larg); height: calc(100vh - 60px); background: #fff; border-right: 1px solid var(--cinza-borda); padding: 20px 0; transition: transform 0.3s ease; z-index: 1020; overflow-y: auto; }
        .sidebar.oculta { transform: translateX(calc(var(--sidebar-larg) * -1)); }
        .sidebar .nav-link { color: var(--texto-escuro); padding: 12px 20px; border-left: 3px solid transparent; display: flex; align-items: center; gap: 12px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.ativo { background: var(--azul-claro); border-left-color: var(--azul-primario); color: var(--azul-escuro); font-weight: 600; }
        .sidebar .nav-link i { width: 22px; color: var(--azul-primario); }
        .sidebar-overlay { display: none; position: fixed; top: 60px; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 1010; }
        .sidebar-overlay.ativo { display: block; }

        .conteudo-principal { margin-top: 60px; margin-left: var(--sidebar-larg); padding: 25px; transition: margin-left 0.3s ease; min-height: calc(100vh - 60px); }
        .conteudo-principal.expandido { margin-left: 0; }
        @media (max-width: 991.98px) { .sidebar { transform: translateX(calc(var(--sidebar-larg) * -1)); } .sidebar.aberta { transform: translateX(0); } .conteudo-principal { margin-left: 0; } }

        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .page-header h2 { font-size: 1.4rem; font-weight: 700; color: var(--azul-escuro); margin: 0; display: flex; align-items: center; gap: 10px; }
        
        .card-pagina { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid var(--cinza-borda); padding: 20px 24px; margin-bottom: 20px; }
        .card-titulo { font-weight: 600; color: var(--azul-escuro); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        
        .tabela-dados { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.88rem; }
        .tabela-dados thead th { background: var(--azul-claro); padding: 10px 14px; border-bottom: 2px solid var(--cinza-borda); }
        .tabela-dados tbody td { padding: 10px 14px; border-bottom: 1px solid var(--cinza-borda); vertical-align: middle; }
        
        .modal-header { background: var(--azul-primario); color: #fff; }
        .modal-header .btn-close { filter: invert(1); }
    </style>
</head>
<body>

    <nav class="navbar-topo d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn-sanduiche" id="btnSanduiche"><i class="fa-solid fa-bars"></i></button>
            <a class="navbar-brand mb-0 d-flex align-items-center" href="principal.php">
                <i class="fa-solid fa-stethoscope"></i> <span>MediAgenda</span>
            </a>
        </div>
        <div class="dropdown">
            <button class="operador-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fa-solid fa-circle-user" style="font-size: 1.6rem;"></i>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($operadorNome); ?></span>
            </button>
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
                <a class="nav-link" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Cadastro de Médicos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link ativo" href="cadastro_especialidades.php"><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</a>
            </li>
        </ul>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="conteudo-principal" id="conteudoPrincipal">

        <div class="page-header">
            <h2><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</h2>
            <button class="btn btn-primary" onclick="abrirModalNovo()">
                <i class="fa-solid fa-plus me-1"></i> Nova Especialidade
            </button>
        </div>

        <div class="card-pagina">
            <div class="card-titulo"><i class="fa-solid fa-magnifying-glass"></i> Filtros</div>
            <form method="GET" action="cadastro_especialidades.php" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label mb-1" style="font-size: 0.85rem;">Nome da Especialidade</label>
                    <input type="text" class="form-control form-control-sm" name="nome" placeholder="Buscar..." value="<?php echo htmlspecialchars($filtroNome); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Filtrar</button>
                <a href="cadastro_especialidades.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-eraser"></i> Limpar</a>
            </form>
        </div>

        <div class="card-pagina">
            <div class="table-responsive">
                <table class="tabela-dados">
                    <thead>
                        <tr>
                            <th width="10%">#</th>
                            <th width="70%">Especialidade</th>
                            <th width="20%" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($especialidades)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma especialidade encontrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($especialidades as $esp): ?>
                            <tr>
                                <td class="text-muted"><?php echo $esp['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($esp['nome']); ?></strong></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2" title="Editar" onclick="abrirModalEditar(<?php echo $esp['id']; ?>, '<?php echo htmlspecialchars(addslashes($esp['nome'])); ?>')">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form action="cadastro_especialidades.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?php echo $esp['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <div class="modal fade" id="modalEspecialidade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo"><i class="fa-solid fa-plus me-2"></i>Nova Especialidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form action="cadastro_especialidades.php" method="POST">
                    <input type="hidden" name="acao" id="formAcao" value="novo">
                    <input type="hidden" name="id" id="formId" value="">
                    <div class="modal-body">
                        <label class="form-label">Nome da Especialidade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nome" id="formNome" required placeholder="Ex: Cardiologia">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lógica da Barra Lateral (Responsive)
        var btnSanduiche = document.getElementById('btnSanduiche');
        var sidebar = document.getElementById('sidebar');
        var conteudoPrincipal = document.getElementById('conteudoPrincipal');
        var sidebarOverlay = document.getElementById('sidebarOverlay');

        btnSanduiche.addEventListener('click', function() {
            if (window.innerWidth <= 991.98) {
                sidebar.classList.toggle('aberta'); sidebarOverlay.classList.toggle('ativo');
            } else {
                sidebar.classList.toggle('oculta'); conteudoPrincipal.classList.toggle('expandido');
            }
        });
        sidebarOverlay.addEventListener('click', function() { sidebar.classList.remove('aberta'); sidebarOverlay.classList.remove('ativo'); });

        // Lógica do Modal
        var myModal = new bootstrap.Modal(document.getElementById('modalEspecialidade'));

        function abrirModalNovo() {
            document.getElementById('modalTitulo').innerHTML = '<i class="fa-solid fa-plus me-2"></i>Nova Especialidade';
            document.getElementById('formAcao').value = 'novo';
            document.getElementById('formId').value = '';
            document.getElementById('formNome').value = '';
            myModal.show();
        }

        function abrirModalEditar(id, nome) {
            document.getElementById('modalTitulo').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Especialidade';
            document.getElementById('formAcao').value = 'editar';
            document.getElementById('formId').value = id;
            document.getElementById('formNome').value = nome;
            myModal.show();
        }
    </script>
</body>
</html>
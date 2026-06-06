<?php

function mediagenda_render_head(string $title, string $extraStyles = ''): void
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo mediagenda_escape($title); ?></title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/app-shell.css">
<?php echo $extraStyles; ?>
</head>
<body>
    <?php
}

function mediagenda_render_topbar(array $user): void
{
    $nome = mediagenda_escape($user['nome'] ?? '');
    $email = mediagenda_escape($user['email'] ?? '');
    ?>
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
                <span class="d-none d-md-inline"><?php echo $nome; ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-operador" aria-labelledby="dropdownOperador">
                <li><span class="dropdown-item-text"><i class="fa-solid fa-user me-2"></i><?php echo $nome; ?></span></li>
                <li><span class="dropdown-item-text"><i class="fa-solid fa-envelope me-2"></i><?php echo $email; ?></span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Sair</a></li>
            </ul>
        </div>
    </nav>
    <?php
}

function mediagenda_render_sidebar(string $activePage): void
{
    $items = [
        ['href' => 'principal.php', 'icon' => 'fa-calendar-days', 'label' => 'Agenda', 'page' => 'principal.php'],
        ['href' => 'cadastro_agendas.php', 'icon' => 'fa-calendar-plus', 'label' => 'Agendamentos', 'page' => 'cadastro_agendas.php'],
        ['href' => 'cadastro_medicos.php', 'icon' => 'fa-user-doctor', 'label' => 'Cadastro de Médicos', 'page' => 'cadastro_medicos.php'],
        ['href' => 'cadastro_especialidades.php', 'icon' => 'fa-list-check', 'label' => 'Cadastro de Especialidades', 'page' => 'cadastro_especialidades.php'],
        ['href' => 'regras_trabalho.php', 'icon' => 'fa-clipboard-list', 'label' => 'Regras do trabalho', 'page' => 'regras_trabalho.php'],
    ];
    ?>
    <aside class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <?php foreach ($items as $item): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === $item['page'] ? 'ativo' : ''; ?>" href="<?php echo $item['href']; ?>">
                        <i class="fa-solid <?php echo $item['icon']; ?>"></i> <?php echo mediagenda_escape($item['label']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php
}

function mediagenda_render_footer(string $extraScripts = ''): void
{
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/app-shell.js"></script>
<?php echo $extraScripts; ?>
</body>
</html>
    <?php
}

<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/shell.php';

$usuarioLogado = mediagenda_require_login();

/* ============================================================
   principal.php - Dashboard de Agendamento de Consultas Médicas
   ------------------------------------------------------------
   TODO: Adicionar validação de sessão aqui (após implementar login)
   Ex:
   session_start();
   if (!isset($_SESSION['operador'])) {
       header("Location: login.php");
       exit;
   }
============================================================ */

/* ============================================================
   DADOS DO OPERADOR LOGADO
   TODO: Substituir pelos dados vindos da $_SESSION
============================================================ */
$operadorNome  = $usuarioLogado['nome'];
$operadorEmail = $usuarioLogado['email'];

/* ============================================================
   DADOS DO MÊS ATUAL (cálculo do calendário)
============================================================ */
$mesAtual    = isset($_GET['mes']) ? max(1, min(12, (int)$_GET['mes'])) : (int)date('n');
$anoAtual    = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
$nomesMeses  = [
    '',
    'Janeiro',
    'Fevereiro',
    'Março',
    'Abril',
    'Maio',
    'Junho',
    'Julho',
    'Agosto',
    'Setembro',
    'Outubro',
    'Novembro',
    'Dezembro'
];
$nomeMes     = $nomesMeses[$mesAtual];
$primeiroDia = mktime(0, 0, 0, $mesAtual, 1, $anoAtual);
$diaSemanaInicio = (int)date('w', $primeiroDia); // 0=Dom ... 6=Sáb
$totalDias   = (int)date('t', $primeiroDia);
$diaHoje     = (int)date('j');
$mesHoje     = (int)date('n');
$anoHoje     = (int)date('Y');

// Mês anterior
$mesAnterior = $mesAtual - 1;
$anoAnterior = $anoAtual;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anoAnterior--;
}

// Próximo mês
$proximoMes = $mesAtual + 1;
$proximoAno = $anoAtual;
if ($proximoMes > 12) {
    $proximoMes = 1;
    $proximoAno++;
}


$agendamentosFicticios = [];
$sql = "select *, DAY(data) diaAgenda from vw_agendamentos where MONTH(data) = $mesAtual AND YEAR(data) = $anoAtual";
$result = mysqli_query($conexao_bd, $sql);
if ($result) {
while ($row = $result->fetch_assoc()) {
    //echo ">>>" . $row["paciente"]." | ". $row["data"] . " | " . $row["diaAgenda"] . "<br>";
    $agendamentosFicticios[$row["diaAgenda"]][] = [
        'id'            => $row["id"],
        'horario'       => date("H:i", strtotime($row["horario"])),
        'paciente'      => $row["paciente"],
        'medico'        => $row["medico"],
        'especialidade' => $row["especialidade"],
        'status'        => $row["status"]
    ];
}
}

?>
<?php mediagenda_render_head('MediAgenda - Painel Principal'); ?>
    <style>
        .card-calendario {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--cinza-borda);
            overflow: hidden;
        }

        .calendario-cabecalho {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            border-bottom: 1px solid var(--cinza-borda);
            flex-wrap: wrap;
            gap: 10px;
        }

        .calendario-cabecalho h4 {
            margin: 0;
            color: var(--azul-escuro);
            font-weight: 600;
            text-transform: capitalize;
        }

        .calendario-cabecalho .btn-nav {
            border: 1px solid var(--cinza-borda);
            background: #fff;
            color: var(--texto-escuro);
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .calendario-cabecalho .btn-nav:hover {
            background: var(--azul-claro);
            color: var(--azul-primario);
            border-color: var(--azul-primario);
        }

        .calendario-grade {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: var(--cinza-borda);
            gap: 1px;
        }

        .calendario-grade .dia-semana {
            background: #fafbfc;
            text-align: center;
            padding: 10px 4px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .calendario-grade .dia {
            background: #fff;
            min-height: 120px;
            padding: 8px;
            position: relative;
            transition: background 0.15s;
            display: flex;
            flex-direction: column;
        }

        .calendario-grade .dia:hover {
            background: #fafbfc;
        }

        .calendario-grade .dia.vazio {
            background: #f8f9fa;
        }

        .calendario-grade .dia .numero {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--texto-escuro);
            margin-bottom: 4px;
        }

        .calendario-grade .dia.hoje .numero {
            background: var(--azul-primario);
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Card de agendamento dentro do dia */
        .card-agendamento {
            background: var(--azul-claro);
            border-left: 3px solid var(--azul-primario);
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 3px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.15s;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .card-agendamento:hover {
            background: var(--azul-primario);
            color: #fff;
            transform: translateX(2px);
        }

        /* Estado quando o agendamento foi cancelado */
        .card-agendamento.cancelado {
            background: #f8d7da; /* bootstrap danger bg */
            border-left: 3px solid #dc3545;
            color: #842029;
        }

        .card-agendamento.cancelado:hover {
            background: #f5c2c7;
            color: #842029;
            transform: none;
        }

        .card-agendamento .horario {
            font-weight: 600;
        }

        .card-agendamento .paciente {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .link-mais {
            font-size: 0.72rem;
            color: var(--azul-primario);
            cursor: pointer;
            font-weight: 600;
            margin-top: 2px;
        }

        .link-mais:hover {
            text-decoration: underline;
        }

        /* ==================== MODAL ==================== */
        .modal-detalhe .modal-header {
            background: var(--azul-primario);
            color: #fff;
        }

        .modal-detalhe .modal-header .btn-close {
            filter: invert(1);
        }

        .modal-detalhe .info-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--cinza-borda);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-detalhe .info-item:last-child {
            border-bottom: none;
        }

        .modal-detalhe .info-item i {
            color: var(--azul-primario);
            width: 22px;
            font-size: 1.05rem;
        }

        .modal-detalhe .info-item strong {
            color: #6c757d;
            font-weight: 500;
            margin-right: 8px;
        }
    </style>
    <?php mediagenda_render_topbar($usuarioLogado); ?>
    <?php mediagenda_render_sidebar('principal.php'); ?>
    <main class="conteudo-principal" id="conteudoPrincipal">

        <div class="card-calendario">

            <!-- Cabeçalho do calendário com navegação -->
            <div class="calendario-cabecalho">
                <h4><?php echo $nomeMes ?> <?php echo $anoAtual ?></h4>
                <div class="d-flex gap-2">
                    <a class="btn-nav" href="?mes=<?php echo $mesAnterior ?>&amp;ano=<?php echo $anoAnterior ?>" title="Mês anterior"><i class="fa-solid fa-chevron-left"></i></a>
                    <a class="btn-nav" href="?" title="Hoje">Hoje</a>
                    <a class="btn-nav" href="?mes=<?php echo $proximoMes ?>&amp;ano=<?php echo $proximoAno ?>" title="Próximo mês"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>

            <!-- Grade do calendário -->
            <div class="calendario-grade">
                <!-- Cabeçalho dos dias da semana -->
                <div class="dia-semana">Dom</div>
                <div class="dia-semana">Seg</div>
                <div class="dia-semana">Ter</div>
                <div class="dia-semana">Qua</div>
                <div class="dia-semana">Qui</div>
                <div class="dia-semana">Sex</div>
                <div class="dia-semana">Sáb</div>

                <?php
                // Células vazias antes do dia 1 (para alinhar ao dia da semana correto)
                for ($i = 0; $i < $diaSemanaInicio; $i++) {
                    echo '<div class="dia vazio"></div>';
                }

                // Loop pelos dias do mês
                for ($dia = 1; $dia <= $totalDias; $dia++) {
                    $classeHoje = ($dia === $diaHoje && $mesAtual === $mesHoje && $anoAtual === $anoHoje) ? 'hoje' : '';
                ?>
                    <div class="dia <?php echo $classeHoje ?>">
                        <span class="numero"><?php echo $dia ?></span>

                        <?php
                        /* ============================================================
                           PONTO DE INTEGRAÇÃO COM O BANCO DE DADOS
                           ------------------------------------------------------------
                           TODO: Substituir o array fictício abaixo por uma consulta real.
                           Exemplo de implementação futura:

                           $agendamentosDoDia = buscarAgendamentosDoDia($dia, $mesAtual, $anoAtual);

                           A função deve retornar um array no formato:
                           [
                               [
                                   'id'            => int,
                                   'horario'       => 'HH:MM',
                                   'paciente'      => string,
                                   'medico'        => string,
                                   'especialidade' => string,
                                   'status'        => string
                               ],
                               ...
                           ]
                        ============================================================ */
                        $agendamentosDoDia = isset($agendamentosFicticios[$dia]) ? $agendamentosFicticios[$dia] : array();

                        // Limita exibição a 3 cards; o restante vira "+N mais"
                        $maxExibir  = 3;
                        $totalAgend = count($agendamentosDoDia);
                        $exibir     = array_slice($agendamentosDoDia, 0, $maxExibir);

                        foreach ($exibir as $agend):
                        ?>
                            <!-- ====== Template do card de agendamento (clicável → modal) ====== -->
                            <div class="card-agendamento<?php echo ($agend['status'] === 'Cancelado') ? ' cancelado' : '' ?>"
                                data-id="<?php echo $agend['id'] ?>"
                                data-horario="<?php echo htmlspecialchars($agend['horario']) ?>"
                                data-paciente="<?php echo htmlspecialchars($agend['paciente']) ?>"
                                data-medico="<?php echo htmlspecialchars($agend['medico']) ?>"
                                data-especialidade="<?php echo htmlspecialchars($agend['especialidade']) ?>"
                                data-status="<?php echo htmlspecialchars($agend['status']) ?>"
                                data-data="<?php echo sprintf('%02d/%02d/%d', $dia, $mesAtual, $anoAtual) ?>">
                                <span class="horario"><?php echo htmlspecialchars($agend['horario']) ?></span>
                                <span class="paciente"><?php echo htmlspecialchars($agend['paciente']) ?></span>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($totalAgend > $maxExibir): ?>
                            <span class="link-mais">+ <?php echo $totalAgend - $maxExibir ?> mais</span>
                        <?php endif; ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>

    </main>

    <div class="modal fade modal-detalhe" id="modalAgendamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-calendar-check me-2"></i>Detalhes do Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="info-item">
                        <i class="fa-solid fa-user"></i>
                        <div><strong>Paciente:</strong> <span id="modalPaciente"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-user-doctor"></i>
                        <div><strong>Médico:</strong> <span id="modalMedico"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-stethoscope"></i>
                        <div><strong>Especialidade:</strong> <span id="modalEspecialidade"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-calendar"></i>
                        <div><strong>Data:</strong> <span id="modalData"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-clock"></i>
                        <div><strong>Horário:</strong> <span id="modalHorario"></span></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-circle-info"></i>
                        <div><strong>Status:</strong> <span id="modalStatus"></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnCancelarAgendamento">
                        <i class="fa-solid fa-ban me-1"></i> Cancelar Agendamento
                    </button>
                    <!-- TODO: implementar ação de editar -->
                    <button type="button" class="btn btn-primary"><i class="fa-solid fa-pen me-1"></i> Editar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ==================================================
        // CLIQUE NO CARD DE AGENDAMENTO → ABRE MODAL
        // ==================================================
        var modalAgendamento = new bootstrap.Modal(document.getElementById('modalAgendamento'));
        var agendamentoAtual = {
            id: null,
            paciente: null,
            data: null,
            horario: null
        };

        document.querySelectorAll('.card-agendamento').forEach(function(card) {
            card.addEventListener('click', function() {
                // Guarda os dados do agendamento selecionado para uso no cancelamento
                agendamentoAtual.id = card.dataset.id;
                agendamentoAtual.paciente = card.dataset.paciente;
                agendamentoAtual.data = card.dataset.data;
                agendamentoAtual.horario = card.dataset.horario;

                document.getElementById('modalPaciente').textContent = card.dataset.paciente;
                document.getElementById('modalMedico').textContent = card.dataset.medico;
                document.getElementById('modalEspecialidade').textContent = card.dataset.especialidade;
                document.getElementById('modalData').textContent = card.dataset.data;
                document.getElementById('modalHorario').textContent = card.dataset.horario;
                document.getElementById('modalStatus').textContent = card.dataset.status;
                modalAgendamento.show();
            });
        });

        // ==================================================
        // CANCELAR AGENDAMENTO — confirmação via SweetAlert2
        // ==================================================
        document.getElementById('btnCancelarAgendamento').addEventListener('click', function() {
            Swal.fire({
                title: 'Cancelar agendamento?',
                html: 'Deseja cancelar o agendamento de <strong>' + agendamentoAtual.paciente + '</strong>' +
                    '<br>Data: ' + agendamentoAtual.data + ' às ' + agendamentoAtual.horario + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, cancelar',
                cancelButtonText: 'Voltar'
            }).then(function(result) {
                if (result.isConfirmed) {

                    fetch('cancelar_agendamento.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id=' + agendamentoAtual.id
                        })
                        .then(function(response) {
                            return response.json();
                        })
                        .then(function(dados) {
                            if (!dados.sucesso) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: dados.mensagem || 'Não foi possível cancelar o agendamento.',
                                    confirmButtonColor: '#0d6efd'
                                });
                                return;
                            }

                            // Remove o card do calendário
                            var card = document.querySelector('.card-agendamento[data-id="' + agendamentoAtual.id + '"]');
                            if (card) {
                                card.remove();
                            }

                            modalAgendamento.hide();

                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelado!',
                                text: 'O agendamento foi cancelado com sucesso.',
                                confirmButtonColor: '#0d6efd',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload();
                            });
                        })
                        .catch(function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro de comunicação',
                                text: 'Não foi possível conectar ao servidor. Tente novamente.',
                                confirmButtonColor: '#0d6efd'
                            });
                        });
                }
            });
        });

        // ==================================================
        // TODO: clique no "+ N mais" → abrir modal listando todos os agendamentos do dia
        // (por enquanto exibe apenas um SweetAlert2 informativo)
        // ==================================================
        document.querySelectorAll('.link-mais').forEach(function(link) {
            link.addEventListener('click', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Em breve',
                    text: 'Aqui será exibida a lista completa de agendamentos do dia.',
                    confirmButtonColor: '#0d6efd'
                });
            });
        });
    </script>
    <?php mediagenda_render_footer(); ?>
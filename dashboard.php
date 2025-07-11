<?php
// Arquivo: dashboard.php (SEU CÓDIGO COM O BOTÃO ADICIONADO)
session_start();

// Protege a página contra acesso não autorizado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Inclui os arquivos necessários
$dbconn = require 'db_connect.php';
require_once 'helpers.php'; // Contém as funções format_seconds() e get_call_status()

$domain_name = $_SESSION['domain_name'];

// --- LÓGICA DOS FILTROS ---
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-7 days'));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');
$direcao_filtro = $_GET['direcao'] ?? 'todas';

// --- CONSTRUÇÃO DA CONSULTA DINÂMICA ---
$params = [$domain_name];
$conditions = [];

if (!empty($data_inicio)) {
    $conditions[] = "start_stamp >= $" . (count($params) + 1);
    $params[] = $data_inicio . " 00:00:00";
}
if (!empty($data_fim)) {
    $conditions[] = "start_stamp <= $" . (count($params) + 1);
    $params[] = $data_fim . " 23:59:59";
}
if ($direcao_filtro !== 'todas' && !empty($direcao_filtro)) {
    $conditions[] = "direction = $" . (count($params) + 1);
    $params[] = $direcao_filtro;
}

$where_clause = "WHERE domain_name = $1";
if (count($conditions) > 0) {
    $where_clause .= " AND " . implode(" AND ", $conditions);
}

// --- CONSULTA PARA OS CARDS DO MINI-DASHBOARD ---
$sql_summary = "
    SELECT
        COUNT(*) AS total_chamadas,
        SUM(billsec) AS total_duracao_segundos,
        COUNT(*) FILTER (WHERE status = 'answered') AS chamadas_atendidas,
        COUNT(*) FILTER (WHERE status = 'missed' OR status = 'no_answer') AS chamadas_perdidas,
        COUNT(*) FILTER (WHERE status = 'voicemail') AS chamadas_voicemail,
        COUNT(*) FILTER (WHERE status = 'busy') AS chamadas_ocupado
    FROM v_xml_cdr
    {$where_clause}
";
$summary_result = pg_query_params($dbconn, $sql_summary, $params);
$summary = pg_fetch_assoc($summary_result);

// --- CONSULTA PRINCIPAL PARA A TABELA (COM DADOS DE GRAVAÇÃO) ---
$sql_table = "SELECT xml_cdr_uuid, record_path, record_name, start_stamp, caller_id_number, destination_number, billsec, direction, status, hangup_cause FROM v_xml_cdr {$where_clause} ORDER BY start_stamp DESC;";
$result = pg_query_params($dbconn, $sql_table, $params);

?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard CDR - <?php echo htmlspecialchars($domain_name); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">

    <style>
        /* --- FONTES E VARIÁVEIS GLOBAIS --- */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        :root {
            --font-family-sans-serif: 'Inter', sans-serif;
            /* Light Theme */
            --bg-color: #f6f8fc;
            --card-bg: #ffffff;
            --text-color: #3d4451;
            --text-muted: #8a92a2;
            --border-color: #e2e8f0;
            --primary-color: #4e73df;
            --primary-color-hover: #3b5ac7;
            --table-header-color: #6c757d;
            --table-hover-bg: #f8f9fa;
            /* Card Colors */
            --green: #198754; --red: #dc3545; --cyan: #0dcaf0; --yellow: #ffc107;
        }

        [data-bs-theme="dark"] {
            --bg-color: #161c24;
            --card-bg: #1e2732;
            --text-color: #cbd5e1;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --primary-color: #5d87ff;
            --primary-color-hover: #7b9eff;
            --table-header-color: #94a3b8;
            --table-hover-bg: #334155;
        }

        /* --- ESTILOS BASE --- */
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: var(--font-family-sans-serif);
            font-size: 0.9rem;
        }
        .wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        .card-body {
            padding: 1.5rem;
        }

        /* --- NAVBAR --- */
        .navbar {
            background-color: var(--card-bg);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1);
            padding: .5rem 1.5rem;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color);
        }
        .navbar .domain-text { color: var(--text-muted); }
        .navbar .domain-text strong { color: var(--text-color); }
        .theme-switcher-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.2rem;
            cursor: pointer;
        }
        .theme-switcher-btn:hover {
             color: var(--primary-color);
        }

        /* --- CARDS DE RESUMO --- */
        .summary-card {
            border: 1px solid var(--border-color);
            padding: 1.25rem;
        }
        .summary-card .card-body {
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .summary-card .summary-text {
            text-transform: uppercase;
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .summary-card .summary-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
        }
        .summary-card .summary-icon {
            font-size: 1.5rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
        }
        .summary-card .icon-green { color: var(--green); }
        .summary-card .icon-red { color: var(--red); }
        .summary-card .icon-cyan { color: var(--cyan); }
        .summary-card .icon-yellow { color: var(--yellow); }

        /* --- TABELA E CONTROLES DATATABLES (COM CORREÇÃO) --- */
        #cdrTable thead th {
            font-weight: 600;
            color: var(--table-header-color);
            border-bottom: 2px solid var(--border-color);
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        #cdrTable tbody td {
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }
        #cdrTable tbody tr:hover { background-color: var(--table-hover-bg); }
        .dt-length, .dt-search, .dt-info { color: var(--text-muted); }
        .dt-search input, .dt-length .form-select {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            border-radius: 0.375rem;
        }
        .page-link {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }
        .page-link:hover {
            background-color: var(--table-hover-bg);
        }
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }
        .page-item.disabled .page-link {
            color: var(--text-muted);
            background-color: transparent;
            border-color: var(--border-color);
        }
        
        .badge {
            font-weight: 600;
            padding: 0.4em 0.7em;
            font-size: 0.75rem;
        }

        /* --- PLAYER DE ÁUDIO MINIMALISTA --- */
        .audio-player-container {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            width: 100%;
            max-width: 250px;
        }
        .play-button {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .play-button:hover { background: var(--primary-color-hover); }
        .progress-container {
            width: 100%;
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
            cursor: pointer;
        }
        .progress-bar {
            width: 0;
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        .time-display {
            font-size: 0.75rem;
            color: var(--text-muted);
            min-width: 40px;
            text-align: right;
        }
        audio.audio-element { display: none; }
        /* ANIMAÇÃO DE PULSAÇÃO PARA O BOTÃO */
@keyframes pulse-animation {
    0% {
        box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(var(--bs-primary-rgb), 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0);
    }
}

#btn-principal {
    /* Usa a cor primária do tema para a borda e o texto */
    border-color: var(--primary-color);
    color: var(--primary-color);
    /* Aplica a animação */
    animation: pulse-animation 2.5s infinite;
    border-radius: 0.375rem; /* Garante bordas arredondadas */
}

#btn-principal:hover {
    color: #fff;
    background-color: var(--primary-color);
    animation: none; /* Pausa a animação quando o mouse está em cima */
}
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="wrapper d-flex justify-content-between align-items-center">
             <a class="navbar-brand" href="#"><i class="fa-solid fa-headphones-simple me-2"></i>Dashboard CDR</a>
             <div class="d-flex align-items-center">
                <span class="navbar-text domain-text d-none d-lg-block me-3">
                    Domínio: <strong><?php echo htmlspecialchars($domain_name); ?></strong>
                </span>

                <a href="index.php" id="btn-principal" class="btn btn-outline-primary btn-sm me-3">
    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard Principal
</a>
                <button class="theme-switcher-btn me-3" id="theme-switcher" type="button">
                    <i class="fa-solid"></i>
                </button>
                <a href="logout.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i>Sair</a>
            </div>
        </div>
    </nav>
    
    <div class="wrapper">
        <div class="row">
            <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><div class="text-content"><div class="summary-text">Atendidas</div><div class="summary-value"><?php echo intval($summary['chamadas_atendidas']); ?></div></div><div class="summary-icon icon-green"><i class="fa-solid fa-phone-volume"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><div class="text-content"><div class="summary-text">Não Atendidas</div><div class="summary-value"><?php echo intval($summary['chamadas_perdidas']); ?></div></div><div class="summary-icon icon-red"><i class="fa-solid fa-phone-slash"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><div class="text-content"><div class="summary-text">Voicemail</div><div class="summary-value"><?php echo intval($summary['chamadas_voicemail']); ?></div></div><div class="summary-icon icon-cyan"><i class="fa-solid fa-voicemail"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="card summary-card"><div class="card-body"><div class="text-content"><div class="summary-text">Ocupado</div><div class="summary-value"><?php echo intval($summary['chamadas_ocupado']); ?></div></div><div class="summary-icon icon-yellow"><i class="fa-solid fa-ban"></i></div></div></div></div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fa-solid fa-filter me-2"></i>Filtros e Relatório de Chamadas</div>
            <div class="card-body">
                 <form method="get" action="dashboard.php" class="row g-3 align-items-end mb-4">
                    <div class="col-md-3"><label for="data_inicio" class="form-label fw-bold small">De:</label><input type="date" class="form-control" name="data_inicio" id="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>"></div>
                    <div class="col-md-3"><label for="data_fim" class="form-label fw-bold small">Até:</label><input type="date" class="form-control" name="data_fim" id="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>"></div>
                    <div class="col-md-3"><label for="direcao" class="form-label fw-bold small">Direção:</label><select name="direcao" id="direcao" class="form-select"><option value="todas" <?php if($direcao_filtro == 'todas') echo 'selected'; ?>>Todas</option><option value="inbound" <?php if($direcao_filtro == 'inbound') echo 'selected'; ?>>Entrada</option><option value="outbound" <?php if($direcao_filtro == 'outbound') echo 'selected'; ?>>Saída</option><option value="local" <?php if($direcao_filtro == 'local') echo 'selected'; ?>>Local</option></select></div>
                    <div class="col-md-3"><button type="submit" class="btn w-100" style="background-color: var(--primary-color); color: white;"><i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar</button></div>
                </form>

                <div class="mb-3">
                    <a href="export_csv.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success"><i class="fa-solid fa-file-csv me-2"></i>Exportar para CSV</a>
                </div>
                
                <table id="cdrTable" class="table table-hover dt-responsive" style="width:100%">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Duração</th>
                            <th>Direção</th>
                            <th>Status</th>
                            <th>Gravação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(pg_num_rows($result) > 0): ?>
                            <?php while ($row = pg_fetch_assoc($result)): ?>
                            <?php
                                $status_info = get_call_status($row);
                                $direction_text = ucfirst($row['direction']);
                                if ($row['direction'] == 'inbound') $direction_text = 'Entrada';
                                if ($row['direction'] == 'outbound') $direction_text = 'Saída';
                            ?>
                            <tr>
                                <td data-sort="<?php echo strtotime($row['start_stamp']); ?>"><?php echo date('d/m/Y H:i:s', strtotime($row['start_stamp'])); ?></td>
                                <td><?php echo htmlspecialchars($row['caller_id_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['destination_number']); ?></td>
                                <td><?php echo format_seconds($row['billsec']); ?></td>
                                <td><?php echo $direction_text; ?></td>
                                <td><span class="badge <?php echo $status_info['class']; ?>"><?php echo $status_info['text']; ?></span></td>
                                <td>
                                    <?php if (!empty($row['record_path']) && !empty($row['record_name'])): ?>
                                        <div class="audio-player-container">
                                            <audio class="audio-element" src="download.php?id=<?php echo $row['xml_cdr_uuid']; ?>" preload="metadata"></audio>
                                            <button class="play-button"><i class="fa-solid fa-play"></i></button>
                                            <div class="progress-container"><div class="progress-bar"></div></div>
                                            <div class="time-display">00:00</div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; // Adicionado para fechar o if corretamente ?>
                    </tbody>
                </table>
            </div>
        </div>
        <footer class="text-center text-muted py-4 small">
            Relatório Gerado em <?php echo date('d/m/Y H:i:s'); ?>
        </footer>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>

    <script>
        $(document).ready(function() {
            // --- LÓGICA DO TEMA (CLARO/ESCURO) COM ÍCONE ---
            const themeSwitcher = document.getElementById('theme-switcher');
            const themeIcon = themeSwitcher.querySelector('i');
            const htmlEl = document.documentElement;

            const setTheme = (theme) => {
                htmlEl.setAttribute('data-bs-theme', theme);
                if (theme === 'dark') {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                } else {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                }
                localStorage.setItem('theme', theme);
            };

            const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            setTheme(savedTheme);

            themeSwitcher.addEventListener('click', () => {
                const currentTheme = htmlEl.getAttribute('data-bs-theme');
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });

            // --- LÓGICA DO PLAYER DE ÁUDIO ---
            function setupAudioPlayers() {
                document.querySelectorAll('.audio-player-container').forEach(container => {
                    if (container.dataset.initialized) return;
                    container.dataset.initialized = true;

                    const audio = container.querySelector('.audio-element');
                    const playBtn = container.querySelector('.play-button');
                    const playIcon = playBtn.querySelector('i');
                    const progressBar = container.querySelector('.progress-bar');
                    const progressContainer = container.querySelector('.progress-container');
                    const timeDisplay = container.querySelector('.time-display');

                    const formatTime = (seconds) => {
                        if (isNaN(seconds)) return '00:00';
                        const mins = Math.floor(seconds / 60);
                        const secs = Math.floor(seconds % 60);
                        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    };
                    
                    audio.addEventListener('loadedmetadata', () => { timeDisplay.textContent = formatTime(audio.duration); });
                    playBtn.addEventListener('click', () => {
                        if (audio.paused) {
                            document.querySelectorAll('.audio-element').forEach(otherAudio => {
                                if (otherAudio !== audio) otherAudio.pause();
                            });
                            audio.play();
                        } else { audio.pause(); }
                    });
                    audio.addEventListener('play', () => playIcon.classList.replace('fa-play', 'fa-pause'));
                    audio.addEventListener('pause', () => playIcon.classList.replace('fa-pause', 'fa-play'));
                    audio.addEventListener('timeupdate', () => {
                        progressBar.style.width = `${(audio.currentTime / audio.duration) * 100}%`;
                        timeDisplay.textContent = formatTime(audio.currentTime);
                    });
                    audio.addEventListener('ended', () => {
                        progressBar.style.width = '0%';
                        playIcon.classList.replace('fa-pause', 'fa-play');
                        timeDisplay.textContent = formatTime(audio.duration);
                    });
                    progressContainer.addEventListener('click', (e) => {
                        const width = progressContainer.clientWidth;
                        const clickX = e.offsetX;
                        audio.currentTime = (clickX / width) * audio.duration;
                    });
                });
            }

            // --- INICIALIZAÇÃO DO DATATABLES ---
            $('#cdrTable').DataTable({
                responsive: true,
                language: { url: "//cdn.datatables.net/plug-ins/2.0.8/i18n/pt-BR.json", },
                order: [[ 0, "desc" ]],
                pagingType: "simple_numbers",
                drawCallback: function( settings ) {
                    setupAudioPlayers();
                }
            });
        });
    </script>
</body> 
</html>
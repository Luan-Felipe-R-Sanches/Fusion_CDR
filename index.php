<?php
// Arquivo: index.php (VERSÃO 100% COMPLETA E FINAL)
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
$domain_name = $_SESSION['domain_name'];
$data_inicio_get = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-6 days'));
$data_fim_get = $_GET['data_fim'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Principal - <?php echo htmlspecialchars($domain_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        :root {
            --bs-body-font-family: 'Inter', sans-serif;
            --primary-color-light: #4e73df; --primary-color-dark: #5d87ff;
            --green: #198754; --red: #dc3545; --cyan: #0dcaf0; --yellow: #ffc107;
        }
        [data-bs-theme="light"] {
            --bg-color: #f6f8fc; --card-bg: #ffffff; --text-color: #3d4451;
            --text-muted: #8a92a2; --border-color: #e2e8f0; --primary-color: var(--primary-color-light);
            --bs-primary-rgb: 78, 115, 223;
        }
        [data-bs-theme="dark"] {
            --bg-color: #161c24; --card-bg: #1e2732; --text-color: #cbd5e1;
            --text-muted: #94a3b8; --border-color: #334155; --primary-color: var(--primary-color-dark);
            --bs-primary-rgb: 93, 135, 255;
        }
        body { background-color: var(--bg-color); color: var(--text-color); font-size: 0.9rem; }
        .wrapper { max-width: 1400px; margin: 0 auto; padding: 1.5rem; }
        .card { background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -2px rgba(0,0,0,.05); margin-bottom: 1.5rem; }
        .card-header { background-color: transparent; border-bottom: 1px solid var(--border-color); padding: 1rem 1.5rem; font-weight: 600; color: var(--text-color); }
        .navbar { background-color: var(--card-bg); box-shadow: 0 4px 6px -1px rgba(0,0,0,.05); padding: .5rem 1.5rem; }
        .navbar-brand { font-weight: 700; color: var(--primary-color); }
        .navbar .domain-text { color: var(--text-muted); } .navbar .domain-text strong { color: var(--text-color); }
        .theme-switcher-btn { background: none; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer; }
        .theme-switcher-btn:hover { color: var(--primary-color); }
        .summary-card .summary-text { text-transform: uppercase; font-weight: 600; font-size: 0.75rem; color: var(--text-muted); }
        .summary-card .summary-value { font-size: 1.75rem; font-weight: 700; color: var(--text-color); }
        .summary-card .summary-icon { font-size: 1.5rem; width: 50px; height: 50px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background-color: var(--bg-color); }
        .summary-card .icon-green { color: var(--green); } .summary-card .icon-red { color: var(--red); } .summary-card .icon-cyan { color: var(--cyan); } .summary-card .icon-yellow { color: var(--yellow); }
        .chart-container { position: relative; height: 350px; width: 100%; }

        /* ANIMAÇÃO DE PULSAÇÃO PARA O BOTÃO */
        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(var(--bs-primary-rgb), 0); }
            100% { box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0); }
        }
        #btn-detalhado {
            border-color: var(--primary-color);
            color: var(--primary-color);
            animation: pulse-animation 2.5s infinite;
            border-radius: 0.375rem;
        }
        #btn-detalhado:hover {
            color: #fff;
            background-color: var(--primary-color);
            animation: none; /* Pausa a animação no hover */
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="wrapper d-flex justify-content-between align-items-center">
             <a class="navbar-brand" href="index.php"><i class="fa-solid fa-chart-pie me-2"></i>Dashboard Principal</a>
             <div class="d-flex align-items-center">
                <span class="navbar-text domain-text d-none d-lg-block me-3">Domínio: <strong><?php echo htmlspecialchars($domain_name); ?></strong></span>
                <a href="dashboard.php" id="btn-detalhado" class="btn btn-outline-primary btn-sm me-3"><i class="fa-solid fa-list-ul me-1"></i>Relatório Detalhado</a>
                <button class="theme-switcher-btn me-3" id="theme-switcher" type="button"><i class="fa-solid fa-sun"></i></button>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i>Sair</a>
            </div>
        </div>
    </nav>
    
    <div class="wrapper">
        <div class="card">
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-auto"><label for="data_inicio" class="form-label small fw-bold">Período de:</label><input type="date" class="form-control" name="data_inicio" id="data_inicio" value="<?php echo htmlspecialchars($data_inicio_get); ?>"></div>
                    <div class="col-md-auto"><label for="data_fim" class="form-label small fw-bold">Até:</label><input type="date" class="form-control" name="data_fim" id="data_fim" value="<?php echo htmlspecialchars($data_fim_get); ?>"></div>
                    <div class="col-md-auto ms-md-auto"><button id="update-dashboard" type="button" class="btn btn-primary w-100"><i class="fa-solid fa-sync-alt me-2"></i>Atualizar</button></div>
                </form>
            </div>
        </div>

        <div id="summary-cards" class="row">
            <div class="col-xl-3 col-md-6 mb-4"><div class="card summary-card h-100"><div class="card-body"><div class="text-content"><div class="summary-text">Atendidas</div><div class="summary-value">...</div></div><div class="summary-icon icon-green"><i class="fa-solid fa-phone-volume"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card summary-card h-100"><div class="card-body"><div class="text-content"><div class="summary-text">Não Atendidas</div><div class="summary-value">...</div></div><div class="summary-icon icon-red"><i class="fa-solid fa-phone-slash"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card summary-card h-100"><div class="card-body"><div class="text-content"><div class="summary-text">Voicemail</div><div class="summary-value">...</div></div><div class="summary-icon icon-cyan"><i class="fa-solid fa-voicemail"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card summary-card h-100"><div class="card-body"><div class="text-content"><div class="summary-text">Ocupado</div><div class="summary-value">...</div></div><div class="summary-icon icon-yellow"><i class="fa-solid fa-ban"></i></div></div></div></div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4"><div class="card h-100"><div class="card-header">Chamadas por Dia</div><div class="card-body"><div class="chart-container"><canvas id="dailyCallsChart"></canvas></div></div></div></div>
            <div class="col-lg-4 mb-4"><div class="card h-100"><div class="card-header">Distribuição de Status</div><div class="card-body d-flex align-items-center justify-content-center"><div class="chart-container" style="max-width: 320px;"><canvas id="statusChart"></canvas></div></div></div></div>
        </div>
        
        <footer class="text-center text-muted py-4 small">Dashboard Gerado em <span id="last-updated"></span></footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeSwitcher = document.getElementById('theme-switcher');
        const themeIcon = themeSwitcher.querySelector('i');
        const htmlEl = document.documentElement;

        let dailyChartInstance, statusChartInstance;

        const doughnutTextPlugin = {
            id: 'doughnutText',
            afterDraw(chart, args, options) {
                if (chart.config.type !== 'doughnut') return;
                const { ctx, data } = chart;
                const total = data.datasets[0].data.reduce((a, b) => Number(a) + Number(b), 0);
                if (total === 0) return;
                ctx.save();
                const x = chart.getDatasetMeta(0).data[0].x;
                const y = chart.getDatasetMeta(0).data[0].y;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = `bold 24px ${Chart.defaults.font.family}`;
                ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim();
                ctx.fillText(total, x, y - 8);
                ctx.font = `14px ${Chart.defaults.font.family}`;
                ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim();
                ctx.fillText('Chamadas', x, y + 15);
                ctx.restore();
            }
        };

        const getChartOptions = (isDonut = false) => {
            const isDarkMode = htmlEl.getAttribute('data-bs-theme') === 'dark';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
            const fontColor = isDarkMode ? '#cbd5e1' : '#6c757d';
            Chart.defaults.color = fontColor;
            let options = {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 800 },
                plugins: {
                    legend: { display: isDonut, position: 'bottom', labels: { color: fontColor, padding: 20 } },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#334155' : '#fff', titleColor: isDarkMode ? '#fff' : '#000',
                        bodyColor: isDarkMode ? '#fff' : '#000', borderColor: gridColor, borderWidth: 1, padding: 10,
                        callbacks: {
                            label: (context) => {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => Number(a) + Number(b), 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                    label += `${context.raw} (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    }
                }
            };
            if (!isDonut) {
                options.scales = {
                    y: { beginAtZero: true, ticks: { precision: 0, color: fontColor }, grid: { color: gridColor, drawBorder: false } },
                    x: { ticks: { color: fontColor }, grid: { display: false } }
                };
            }
            return options;
        };
        
        const displayNoDataMessage = (canvasId) => {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = '16px ' + Chart.defaults.font.family;
            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim();
            ctx.fillText('Sem dados para exibir no período.', canvas.width / 2, canvas.height / 2);
            ctx.restore();
        };
        
        const renderDailyChart = (chartData) => {
            const ctx = document.getElementById('dailyCallsChart').getContext('2d');
            if (dailyChartInstance) dailyChartInstance.destroy();
            if (!chartData || chartData.length === 0) { displayNoDataMessage('dailyCallsChart'); return; }
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
            const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
            gradient.addColorStop(0, primaryColor + '99'); gradient.addColorStop(1, primaryColor + '11');
            dailyChartInstance = new Chart(ctx, { type: 'bar', data: {
                labels: chartData.map(item => new Date(item.call_date+'T00:00:00').toLocaleDateString('pt-BR', {day:'2-digit', month:'2-digit'})),
                datasets: [{ label: 'Total de Chamadas', data: chartData.map(item => item.count), backgroundColor: gradient, borderColor: primaryColor, borderWidth: 2, borderRadius: 5, barPercentage: 0.5, categoryPercentage: 0.7 }]
            }, options: getChartOptions() });
        };
        
        const renderStatusChart = (chartData) => {
            const ctx = document.getElementById('statusChart').getContext('2d');
            if (statusChartInstance) statusChartInstance.destroy();
            if (!chartData || chartData.length === 0) { displayNoDataMessage('statusChart'); return; }
            const labels = { answered: 'Atendida', missed: 'Perdida', no_answer: 'Não Atendida', voicemail: 'Voicemail', busy: 'Ocupado', failed: 'Falhou' };
            statusChartInstance = new Chart(ctx, { type: 'doughnut', data: {
                labels: chartData.map(item => labels[item.status] || item.status),
                datasets: [{ data: chartData.map(item => item.count), backgroundColor: ['#198754', '#dc3545', '#0dcaf0', '#ffc107', '#6f42c1', '#6c757d'], borderColor: getComputedStyle(document.documentElement).getPropertyValue('--card-bg').trim(), borderWidth: 4, hoverOffset: 15 }]
            }, options: getChartOptions(true), plugins: [doughnutTextPlugin] });
        };

        const updateDashboard = () => {
            const startDate = document.getElementById('data_inicio').value;
            const endDate = document.getElementById('data_fim').value;
            const apiUrl = `get_dashboard_data.php?data_inicio=${startDate}&data_fim=${endDate}`;
            const updateButton = document.getElementById('update-dashboard');
            updateButton.disabled = true;
            updateButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Atualizando...';
            fetch(apiUrl)
                .then(response => { if (!response.ok) throw new Error(`Erro na rede: ${response.statusText}`); return response.json(); })
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    const summary = data.summary;
                    document.querySelector('#summary-cards .summary-value').textContent = summary.chamadas_atendidas || 0;
                    document.querySelectorAll('#summary-cards .summary-value')[1].textContent = summary.chamadas_perdidas || 0;
                    document.querySelectorAll('#summary-cards .summary-value')[2].textContent = summary.chamadas_voicemail || 0;
                    document.querySelectorAll('#summary-cards .summary-value')[3].textContent = summary.chamadas_ocupado || 0;
                    renderDailyChart(data.dailyChart);
                    renderStatusChart(data.statusChart);
                    document.getElementById('last-updated').textContent = new Date().toLocaleString('pt-BR');
                })
                .catch(error => { console.error('Erro ao carregar o dashboard:', error); alert('Erro ao carregar dados. Verifique o console (F12) para mais detalhes.'); })
                .finally(() => { updateButton.disabled = false; updateButton.innerHTML = '<i class="fa-solid fa-sync-alt me-2"></i>Atualizar'; });
        };
        
        const setTheme = (theme) => {
            htmlEl.setAttribute('data-bs-theme', theme);
            themeIcon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            localStorage.setItem('theme', theme);
        };

        themeSwitcher.addEventListener('click', () => {
            const currentTheme = htmlEl.getAttribute('data-bs-theme');
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            if (dailyChartInstance || statusChartInstance) { updateDashboard(); }
        });
        document.getElementById('update-dashboard').addEventListener('click', updateDashboard);
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        setTheme(savedTheme);
        updateDashboard();
    });
    </script>
</body> 
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASA NEO Space Command Radar</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- jQuery & DataTables CSS/JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <!-- Font Family -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        orbitron: ['Orbitron', 'sans-serif'],
                        rajdhani: ['Rajdhani', 'sans-serif'],
                    },
                    colors: {
                        space: {
                            dark: '#030712',
                            card: 'rgba(15, 23, 42, 0.75)',
                            border: 'rgba(56, 189, 248, 0.2)',
                            neonCyan: '#00f3ff',
                            neonPink: '#ff0055',
                            neonPurple: '#a855f7',
                            neonGold: '#eab308'
                        }
                    }
                }
            }
        }

        let hazardChartInstance = null;

        function initHazardChart() {
            const canvas = document.getElementById('hazardRatioChart');
            if (!canvas) return;

            const ctx = document.getElementById('hazardRatioChart').getContext('2d');
            hazardChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Hazardous', 'Safe'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: [
                            'rgba(244, 63, 94, 0.65)',
                            'rgba(0, 243, 255, 0.65)'
                        ],
                        borderColor: ['#f43f5e', '#00f3ff'],
                        borderWidth: 1.5,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#cbd5e1',
                                font: { family: 'Rajdhani', size: 12 },
                                padding: 12
                            }
                        }
                    }
                }
            });
        }

        function renderTable() {
            // Hancurkan instance DataTables lama sebelum memperbarui baris tabel
            if ($.fn.DataTable.isDataTable('#mainAsteroidTable')) {
                $('#mainAsteroidTable').DataTable().destroy();
            }

            const tbody = document.getElementById('asteroidTableBody');
            tbody.innerHTML = '';

            document.getElementById('logCount').innerText = `${filteredNEOs.length} Items`;

            filteredNEOs.forEach(neo => {
                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-900/60 transition-colors border-b border-slate-800/40";
                
                tr.innerHTML = `
                    <td class="p-3 font-semibold text-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full ${neo.is_hazardous ? 'bg-rose-500 shadow-[0_0_8px_#f43f5e]' : 'bg-cyan-400 shadow-[0_0_8px_#22d3ee]'}"></span>
                            ${neo.name}
                        </div>
                    </td>
                    <td class="p-3 text-cyan-300" data-order="${neo.diameter_m}">${neo.diameter_m.toLocaleString()} m</td>
                    <td class="p-3 text-amber-300" data-order="${neo.distance_km}">${(neo.distance_km / 1000000).toFixed(2)}M km <span class="text-[10px] text-slate-500">(${neo.lunar_distance.toFixed(1)} LD)</span></td>
                    <td class="p-3 text-purple-300" data-order="${neo.velocity_kmh}">${neo.velocity_kmh.toLocaleString()} km/h</td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border ${neo.is_hazardous ? 'bg-rose-500/20 text-rose-400 border-rose-500/40' : 'bg-cyan-500/20 text-cyan-400 border-cyan-500/40'}">
                            ${neo.is_hazardous ? 'HAZARDOUS' : 'SAFE'}
                        </span>
                    </td>
                    <td class="p-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @auth
                                <a href="{{ route('observations.create') }}?jpl_id=${encodeURIComponent(neo.id)}&name=${encodeURIComponent(neo.name)}&diameter_m=${neo.diameter_m}&distance_km=${neo.distance_km}&velocity_kmh=${neo.velocity_kmh}&is_hazardous=${neo.is_hazardous ? 1 : 0}" 
                                   class="px-2.5 py-1 rounded-lg bg-amber-500/15 border border-amber-400/40 text-amber-300 hover:bg-amber-500/30 text-xs transition-all flex items-center gap-1">
                                    <i data-lucide="bookmark-plus" class="w-3.5 h-3.5"></i> Archive
                                </a>
                            @endauth
                            <button onclick="openModal('${neo.id}')" class="px-3 py-1 rounded-lg bg-cyan-500/10 border border-cyan-400/40 text-cyan-300 hover:bg-cyan-500/30 text-xs transition-all flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Inspect
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Inisialisasi DataTables pada tabel utama
            $('#mainAsteroidTable').DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
                columnDefs: [
                    { orderable: false, targets: 5 }
                ],
                language: {
                    search: "Cari Asteroid:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ objek",
                    emptyTable: "Tidak ada objek yang sesuai dengan filter saat ini.",
                    paginate: {
                        previous: "Prev",
                        next: "Next"
                    }
                },
                drawCallback: function () {
                    lucide.createIcons();
                }
            });
        }
       function updateHazardChart() {
                if (!hazardChartInstance) return;
                
                const hazardousCount = activeNEOs.filter(n => n.is_hazardous || n.is_potentially_hazardous_asteroid).length;
                const safeCount = activeNEOs.length - hazardousCount;
                
                hazardChartInstance.data.datasets[0].data = [hazardousCount, safeCount];
                hazardChartInstance.update();
            }

        function applyFiltersAndSort() {
            filteredNEOs = activeNEOs.filter(neo => {
                return selectedHazardFilter === 'all' || 
                    (selectedHazardFilter === 'hazardous' && neo.is_hazardous) ||
                    (selectedHazardFilter === 'safe' && !neo.is_hazardous);
            });

            renderTable();
        }
    </script>

    <style>
        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: #030712;
            color: #f3f4f6;
            overflow-x: hidden;
        }

        /* Glassmorphism Styles */
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(56, 189, 248, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }

        .glass-panel-glow {
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.15);
        }

        .glass-panel:hover {
            border-color: rgba(56, 189, 248, 0.4);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(3, 7, 18, 0.8);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(56, 189, 248, 0.3);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 243, 255, 0.6);
        }

        /* Radar Sweep Effect */
        .radar-sweep {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0deg, transparent 300deg, rgba(0, 243, 255, 0.3) 360deg);
            animation: sweep 4s linear infinite;
        }

        @keyframes sweep {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Glowing Text */
        .glow-cyan {
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.7);
        }
        .glow-red {
            text-shadow: 0 0 10px rgba(255, 0, 85, 0.7);
        }

        /* Pulse Animation */
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.03); }
        }
        .animate-pulse-glow {
            animation: pulseGlow 3s infinite ease-in-out;
        }

        /* Canvas Background Container */
        #spaceCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            pointer-events: auto;
        }

        /* Penyesuaian Tema Gelap untuk DataTables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #94a3b8 !important;
            font-family: monospace;
            font-size: 12px;
            margin-bottom: 10px;
            margin-top: 8px;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background-color: rgba(2, 6, 23, 0.9) !important;
            border: 1px solid rgba(56, 189, 248, 0.35) !important;
            color: #67e8f9 !important;
            border-radius: 8px;
            padding: 4px 10px;
            outline: none;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #cbd5e1 !important;
            border: 1px solid rgba(51, 65, 85, 0.7) !important;
            background: rgba(15, 23, 42, 0.8) !important;
            border-radius: 6px !important;
            margin: 0 2px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(0, 243, 255, 0.2) !important;
            border-color: #00f3ff !important;
            color: #00f3ff !important;
        }
        table.dataTable thead th {
            border-bottom: 1px solid rgba(56, 189, 248, 0.25) !important;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid rgba(30, 41, 59, 0.8) !important;
        }
    </style>
</head>
<body class="min-h-screen relative selection:bg-cyan-500 selection:text-black">

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none"></div>

    <!-- Interactive Space Canvas Background -->
    <canvas id="spaceCanvas"></canvas>

    <!-- App Container -->
    <div class="relative z-10 flex flex-col min-h-screen p-3 md:p-6 max-w-[1600px] mx-auto space-y-6">

    @if (session('status'))
            <div class="glass-panel rounded-xl px-4 py-3 border-emerald-500/40 bg-emerald-950/30 flex items-center justify-between text-xs font-mono text-emerald-300">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                    <span>{{ session('status') }}</span>
                </div>
                <span class="text-[11px] text-emerald-400/70">SESSION SCANS: {{ $scanCount ?? 1 }}x</span>
            </div>
        @endif

        <!-- Header / Command Console Top Bar -->
        <header class="glass-panel rounded-2xl p-4 flex flex-col md:flex-row justify-between items-center gap-4 border-b-2 border-cyan-500/50">
            <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 flex items-center justify-center rounded-full bg-cyan-950/60 border border-cyan-400/50 shadow-lg shadow-cyan-500/20">
                    <i data-lucide="orbit" class="w-7 h-7 text-cyan-400 animate-spin-slow"></i>
                    <div class="absolute inset-0 rounded-full border border-cyan-400/30 animate-ping opacity-25"></div>
                </div>
                <div>
                    <h1 class="font-orbitron text-xl md:text-2xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-sky-200 to-indigo-400 glow-cyan">
                        ASTRONET // NEO RADAR
                    </h1>
                    <p class="text-xs font-mono text-cyan-300/70 tracking-widest uppercase">NASA Deep Space Object Tracking Terminal</p>
                    <p class="text-xs font-mono text-cyan-300/50 tracking-widest uppercase">bilaaz</p>
              
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 md:gap-4 w-full md:w-auto">
                <div class="bg-slate-900/80 px-3 py-1.5 rounded-lg border border-cyan-500/20 text-center">
                    <div class="text-[10px] uppercase font-mono text-slate-400">Tracked NEOs</div>
                    <div id="statTotal" class="font-orbitron font-bold text-cyan-400 text-base">--</div>
                </div>
                <div class="bg-slate-900/80 px-3 py-1.5 rounded-lg border border-rose-500/30 text-center">
                    <div class="text-[10px] uppercase font-mono text-slate-400">Hazardous</div>
                    <div id="statHazardous" class="font-orbitron font-bold text-rose-500 text-base glow-red">--</div>
                </div>
                <div class="bg-slate-900/80 px-3 py-1.5 rounded-lg border border-cyan-500/20 text-center">
                    <div class="text-[10px] uppercase font-mono text-slate-400">Closest Approach</div>
                    <div id="statClosest" class="font-orbitron font-bold text-amber-400 text-base">--</div>
                </div>
                <div class="bg-slate-900/80 px-3 py-1.5 rounded-lg border border-cyan-500/20 text-center">
                    <div class="text-[10px] uppercase font-mono text-slate-400">Max Speed</div>
                    <div id="statMaxSpeed" class="font-orbitron font-bold text-purple-400 text-base">--</div>
                </div>
            </div>

            <!-- Global Action Controls + Form Filter Tanggal (Laravel Request) -->
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('asteroid.index') }}" method="GET" class="flex items-center gap-1.5">
                    <input 
                        type="date" 
                        name="date" 
                        value="{{ $selectedDate }}" 
                        class="px-2.5 py-2 rounded-xl bg-slate-900/90 border border-cyan-500/40 text-cyan-300 text-xs font-mono focus:outline-none focus:border-cyan-400"
                    >
                    <button type="submit" class="px-3 py-2 rounded-xl bg-indigo-500/20 border border-indigo-400/50 text-indigo-300 hover:bg-indigo-500/30 text-xs font-mono transition-all">
                        SCAN DATE
                    </button>
                </form>

                <button id="soundToggleBtn" onclick="toggleAudio()" class="p-2.5 rounded-xl bg-slate-800/80 border border-slate-700 hover:border-cyan-400 text-cyan-400 transition-all hover:shadow-[0_0_10px_rgba(0,243,255,0.3)]" title="Toggle Audio FX">
                    <i data-lucide="volume-2" id="soundIcon" class="w-5 h-5"></i>
                </button>
                <button id="modeToggleBtn" onclick="toggleDataMode()" class="px-3 py-2 rounded-xl {{ $isLive ? 'bg-emerald-500/25 border-emerald-500/50 text-emerald-400 hover:bg-emerald-500/35' : 'bg-amber-500/20 border-amber-500/40 text-amber-400 hover:bg-amber-500/30' }} border text-xs font-mono flex items-center gap-2 transition-all">
                    <i data-lucide="database" class="w-4 h-4"></i>
                    <span id="modeText">{{ $isLive ? 'MODE: NASA LIVE API' : 'MODE: JPL SIMULATOR' }}</span>
                </button>
                <button type="button" onclick="toggleApiBar()" class="px-3 py-2 rounded-xl bg-slate-800/80 border border-amber-500/40 text-amber-300 hover:bg-amber-500/20 text-xs font-mono flex items-center gap-2 transition-all" title="Isi / ubah NASA API Key">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>API KEY</span>
                </button>
                <button onclick="refreshData()" class="p-2.5 rounded-xl bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 hover:bg-cyan-500/30 transition-all hover:scale-105 active:scale-95" title="Refresh Telemetry">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                </button>

                @auth
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-700">
                        <a href="{{ route('observations.index') }}" class="px-3 py-2 rounded-xl bg-amber-500/20 border border-amber-400/50 text-amber-300 hover:bg-amber-500/30 text-xs font-mono font-bold transition-all">
                            ARCHIVE DB
                        </a>
                        <span class="px-2.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-mono">
                            OP: {{ Auth::user()->name }} (Scan #{{ $scanCount ?? 1 }})
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 hover:bg-rose-500/30 text-xs font-mono transition-all">
                                LOGOUT
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 pl-2 border-l border-slate-700">
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-xl bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 hover:bg-cyan-500/30 text-xs font-mono transition-all">
                            LOGIN
                        </a>
                        <a href="{{ route('register') }}" class="px-3 py-2 rounded-xl bg-purple-500/20 border border-purple-400/50 text-purple-300 hover:bg-purple-500/30 text-xs font-mono transition-all">
                            REGISTER
                        </a>
                    </div>
                @endauth
            </div>
        </header>

        <!-- API Key Bar: dibuka lewat tombol API KEY, atau otomatis saat koneksi NASA gagal -->
        <div id="apiKeySection" class="hidden glass-panel rounded-xl p-4 border-amber-500/30 flex flex-col sm:flex-row items-center gap-3">
            <i data-lucide="key" class="w-5 h-5 text-amber-400 shrink-0"></i>
            <input type="text" id="nasaApiKey" value="" autocomplete="off" placeholder="Tempel NASA API Key pribadi (kosong = DEMO_KEY)"
                   onkeydown="if (event.key === 'Enter') { fetchNASAData(); }"
                   class="w-full bg-slate-950/80 border border-slate-700 rounded-lg px-3 py-1.5 text-sm font-mono text-cyan-300 focus:outline-none focus:border-cyan-400">
            <button onclick="fetchNASAData()" class="w-full sm:w-auto px-4 py-1.5 rounded-lg bg-amber-500/20 border border-amber-400/50 text-amber-300 hover:bg-amber-500/30 text-xs font-mono whitespace-nowrap transition-all">
                CONNECT &amp; FETCH LIVE DATA
            </button>
            <button onclick="clearSavedKey()" class="w-full sm:w-auto px-3 py-1.5 rounded-lg bg-slate-700/40 border border-slate-600 text-slate-300 hover:bg-slate-700/70 text-xs font-mono whitespace-nowrap transition-all">
                RESET
            </button>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Panel: Interactive Radar & Visualizer -->
            <div class="glass-panel rounded-2xl p-5 flex flex-col space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="radar" class="w-5 h-5 text-cyan-400"></i>
                        <h2 class="font-orbitron text-lg font-bold text-slate-100">ORBITAL RADAR MAP</h2>
                    </div>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">REAL-TIME PROJECTION</span>
                </div>

                <!-- Circular Radar Canvas Container -->
                <div class="relative w-full aspect-square max-w-[340px] mx-auto flex items-center justify-center my-2">
                    <div class="absolute inset-0 rounded-full border border-cyan-500/20 bg-slate-950/40"></div>
                    <div class="absolute inset-[15%] rounded-full border border-cyan-500/20"></div>
                    <div class="absolute inset-[35%] rounded-full border border-cyan-500/20"></div>
                    <div class="absolute inset-[55%] rounded-full border border-cyan-500/20"></div>
                    <div class="absolute top-0 bottom-0 left-1/2 w-px bg-cyan-500/20"></div>
                    <div class="absolute left-0 right-0 top-1/2 h-px bg-cyan-500/20"></div>
                    <div class="radar-sweep"></div>
                    <div class="absolute w-6 h-6 rounded-full bg-blue-500 border-2 border-cyan-300 shadow-[0_0_12px_#3b82f6] z-10 flex items-center justify-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                    </div>
                    <canvas id="radarCanvas" width="340" height="340" class="absolute inset-0 z-20 cursor-pointer"></canvas>
                </div>

                <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800 text-xs font-mono space-y-1">
                    <div class="flex justify-between text-slate-400">
                        <span>Center: <strong class="text-blue-400">Earth ( Sol-3 )</strong></span>
                        <span>Scope: <strong class="text-cyan-400">0.5 AU Radius</strong></span>
                    </div>
                    <div class="flex items-center justify-around pt-2 text-[11px]">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block shadow-[0_0_5px_#f43f5e]"></span> Potentially Hazardous</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400 inline-block shadow-[0_0_5px_#22d3ee]"></span> Safe Distance</span>
                    </div>
                </div>
            </div>

            <!-- Middle/Right Panel: Interactive Chart & Analysis -->
            <div class="lg:col-span-2 glass-panel rounded-2xl p-5 flex flex-col justify-between space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-purple-400"></i>
                        <h2 class="font-orbitron text-lg font-bold text-slate-100">NEO METRICS ANALYZER</h2>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-900/80 p-1 rounded-xl border border-slate-800 self-stretch sm:self-auto justify-center">
                        <button id="chartMetricSpeed" onclick="setChartMetric('speed')" class="px-3 py-1 rounded-lg text-xs font-mono transition-all bg-purple-600 text-white shadow-md">Velocity (km/h)</button>
                        <button id="chartMetricDistance" onclick="setChartMetric('distance')" class="px-3 py-1 rounded-lg text-xs font-mono text-slate-400 hover:text-white transition-all">Distance (Lunar)</button>
                        <button id="chartMetricDiameter" onclick="setChartMetric('diameter')" class="px-3 py-1 rounded-lg text-xs font-mono text-slate-400 hover:text-white transition-all">Diameter (m)</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Grafik 1: Bar Chart Metrik -->
                    <div class="md:col-span-8 relative w-full h-[280px] bg-slate-950/40 rounded-xl p-3 border border-slate-800/60">
                        <canvas id="neoChart"></canvas>
                    </div>
                    <!-- Grafik 2: Doughnut Chart Rasio Ancaman -->
                    <div class="md:col-span-4 relative w-full h-[280px] bg-slate-950/40 rounded-xl p-3 border border-slate-800/60 flex flex-col items-center justify-between">
                        <span class="text-[11px] font-mono text-cyan-300 tracking-wider uppercase">THREAT RATIO</span>
                        <div class="relative w-full h-[220px] flex items-center justify-center">
                            <canvas id="hazardRatioChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2">
                    <div class="p-3 bg-slate-900/50 rounded-xl border border-cyan-500/10 flex items-center gap-3">
                        <div class="p-2 bg-cyan-500/10 rounded-lg text-cyan-400">
                            <i data-lucide="shield-alert" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-mono text-slate-400">DEFCON STATUS</div>
                            <div class="text-sm font-orbitron font-bold text-cyan-300">LEVEL 4 - NORMAL</div>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-900/50 rounded-xl border border-purple-500/10 flex items-center gap-3">
                        <div class="p-2 bg-purple-500/10 rounded-lg text-purple-400">
                            <i data-lucide="zap" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-mono text-slate-400">FASTEST OBJECT</div>
                            <div id="fastestAsteroidLabel" class="text-sm font-orbitron font-bold text-purple-300">--</div>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-900/50 rounded-xl border border-amber-500/10 flex items-center gap-3">
                        <div class="p-2 bg-amber-500/10 rounded-lg text-amber-400">
                            <i data-lucide="maximize" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-mono text-slate-400">LARGEST DIAMETER</div>
                            <<div id="largestAsteroidLabel" class="text-xs sm:text-sm font-orbitron font-bold break-words whitespace-normal text-white"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asteroids Table Section -->
        <div class="glass-panel rounded-2xl p-5 flex flex-col space-y-4">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="list" class="w-5 h-5 text-cyan-400"></i>
                    <h2 class="font-orbitron text-lg font-bold text-slate-100">DETECTED ASTEROID LOG</h2>
                    <span id="logCount" class="text-xs font-mono text-cyan-400 bg-cyan-950/80 px-2 py-0.5 rounded border border-cyan-800">0 Items</span>
                </div>

                <!-- Quick Filter Status Ancaman -->
                <div class="flex items-center gap-1 bg-slate-900/80 p-1 rounded-xl border border-slate-800 self-start">
                    <button onclick="setHazardFilter('all')" id="btnFilterAll" class="px-3 py-1 rounded-lg text-xs font-mono bg-cyan-500 text-black font-semibold transition-all">ALL</button>
                    <button onclick="setHazardFilter('hazardous')" id="btnFilterHazardous" class="px-3 py-1 rounded-lg text-xs font-mono text-slate-400 hover:text-white transition-all">HAZARDOUS</button>
                    <button onclick="setHazardFilter('safe')" id="btnFilterSafe" class="px-3 py-1 rounded-lg text-xs font-mono text-slate-400 hover:text-white transition-all">SAFE</button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-950/40 p-3">
                <table id="mainAsteroidTable" class="w-full text-left text-xs font-mono border-collapse">
                    <thead>
                        <tr class="bg-slate-900/80 text-cyan-400 border-b border-slate-800 uppercase tracking-wider">
                            <th class="p-3">DESIGNATION / NAME</th>
                            <th class="p-3">EST. DIAMETER</th>
                            <th class="p-3">MISS DISTANCE</th>
                            <th class="p-3">RELATIVE VELOCITY</th>
                            <th class="p-3">HAZARD RATING</th>
                            <th class="p-3 text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="asteroidTableBody" class="divide-y divide-slate-800/60">
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Detailed Modal -->
    <div id="asteroidModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden opacity-0 transition-opacity duration-300">
        <div class="glass-panel glass-panel-glow w-full max-w-2xl rounded-2xl overflow-hidden border border-cyan-500/40 transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
            <div class="p-4 bg-slate-900/90 border-b border-cyan-500/30 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="modalHazardBadge" class="p-2 rounded-lg bg-cyan-500/20 text-cyan-400 border border-cyan-500/40">
                        <i data-lucide="disc" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="modalName" class="font-orbitron text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-white">ASTEROID DESIGNATION</h3>
                        <p id="modalId" class="text-[11px] font-mono text-cyan-400/70">JPL Reference ID: 000000</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/80 transition-all">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-5">
                <div class="relative w-full h-56 bg-slate-950 rounded-xl overflow-hidden border border-slate-800 flex items-center justify-center">
                    <canvas id="modalCanvas" class="w-full h-full cursor-grab active:cursor-grabbing"></canvas>
                    <div class="absolute bottom-2 left-2 text-[10px] font-mono text-slate-500 bg-slate-950/80 px-2 py-0.5 rounded border border-slate-800">
                        Drag to rotate rendering | Interactive View
                    </div>
                    <div id="modalHazardLabel" class="absolute top-2 right-2 px-3 py-1 rounded-full text-xs font-mono font-bold border">
                        SAFE
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-xs">
                    <div class="bg-slate-900/70 p-3 rounded-xl border border-slate-800">
                        <div class="text-slate-400 text-[10px]">EST. DIAMETER</div>
                        <div id="modalDiameter" class="font-bold text-cyan-300 text-sm mt-1">--</div>
                    </div>
                    <div class="bg-slate-900/70 p-3 rounded-xl border border-slate-800">
                        <div class="text-slate-400 text-[10px]">MISS DISTANCE</div>
                        <div id="modalDistance" class="font-bold text-amber-300 text-sm mt-1">--</div>
                    </div>
                    <div class="bg-slate-900/70 p-3 rounded-xl border border-slate-800">
                        <div class="text-slate-400 text-[10px]">RELATIVE VELOCITY</div>
                        <div id="modalVelocity" class="font-bold text-purple-300 text-sm mt-1">--</div>
                    </div>
                    <div class="bg-slate-900/70 p-3 rounded-xl border border-slate-800">
                        <div class="text-slate-400 text-[10px]">LUNAR DISTANCE</div>
                        <div id="modalLD" class="font-bold text-rose-300 text-sm mt-1">--</div>
                    </div>
                </div>

                <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-800 space-y-2">
                    <h4 class="font-orbitron text-xs font-bold text-cyan-400 tracking-wider">TELESCOPIC TELEMETRY SUMMARY</h4>
                    <p id="modalDescription" class="text-xs text-slate-300 leading-relaxed font-sans">
                        Telemetry data indicates a stable trajectory relative to Earth's orbit.
                    </p>
                </div>
            </div>

            <div class="p-4 bg-slate-900/90 border-t border-slate-800 flex justify-end">
                <button onclick="closeModal()" class="px-5 py-2 rounded-xl bg-cyan-500/20 border border-cyan-400/50 text-cyan-300 hover:bg-cyan-500/30 font-mono text-xs transition-all">
                    CLOSE TELEMETRY WINDOW
                </button>
            </div>
        </div>
    </div>

    <!-- Application Script Logic -->
    <script>
        // AUDIO SYNTHESIS
        let audioCtx = null;
        let soundEnabled = true;

        function initAudio() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
        }

        function playSound(type) {
            if (!soundEnabled) return;
            initAudio();
            if (!audioCtx) return;

            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);

            const now = audioCtx.currentTime;

            if (type === 'click') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, now);
                osc.frequency.exponentialRampToValueAtTime(400, now + 0.05);
                gain.gain.setValueAtTime(0.15, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.05);
                osc.start(now);
                osc.stop(now + 0.05);
            } else if (type === 'modal') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(300, now);
                osc.frequency.exponentialRampToValueAtTime(900, now + 0.15);
                gain.gain.setValueAtTime(0.2, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.15);
                osc.start(now);
                osc.stop(now + 0.15);
            } else if (type === 'refresh') {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(200, now);
                osc.frequency.exponentialRampToValueAtTime(600, now + 0.2);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.2);
                osc.start(now);
                osc.stop(now + 0.2);
            }
        }

        function toggleAudio() {
            soundEnabled = !soundEnabled;
            const icon = document.getElementById('soundIcon');
            if (soundEnabled) {
                icon.setAttribute('data-lucide', 'volume-2');
                playSound('click');
            } else {
                icon.setAttribute('data-lucide', 'volume-x');
            }
            lucide.createIcons();
        }

        // JPL SIMULATED DATA
        const simulatedNEOs = [
            {
                id: "3542519",
                name: "(2024 YR4)",
                diameter_m: 62,
                distance_km: 1840000,
                lunar_distance: 4.79,
                velocity_kmh: 48920,
                is_hazardous: true,
                orbit_period_days: 420,
                description: "Recently detected near-Earth asteroid with high orbital uncertainty. Requires precise telescopic tracking for trajectory refinement."
            },
            {
                id: "20099942",
                name: "99942 Apophis (2004 MN4)",
                diameter_m: 370,
                distance_km: 31200000,
                lunar_distance: 81.25,
                velocity_kmh: 107280,
                is_hazardous: true,
                orbit_period_days: 323,
                description: "Famous Potentially Hazardous Asteroid. Will make a historic close approach to Earth in April 2029 within satellite orbit altitude."
            },
            {
                id: "2101955",
                name: "101955 Bennu (1999 RQ36)",
                diameter_m: 492,
                distance_km: 14200000,
                lunar_distance: 36.97,
                velocity_kmh: 101520,
                is_hazardous: true,
                orbit_period_days: 436,
                description: "Carbonaceous asteroid visited by NASA's OSIRIS-REx spacecraft, which returned pristine surface samples back to Earth."
            },
            {
                id: "20004179",
                name: "4179 Toutatis (1989 AC)",
                diameter_m: 2450,
                distance_km: 28900000,
                lunar_distance: 75.26,
                velocity_kmh: 122040,
                is_hazardous: true,
                orbit_period_days: 1461,
                description: "Elongated, chaotic rotator tumbling through space with an eccentric orbit inclined close to the ecliptic plane."
            },
            {
                id: "20000433",
                name: "433 Eros (1898 DQ)",
                diameter_m: 16840,
                distance_km: 22400000,
                lunar_distance: 58.33,
                velocity_kmh: 87480,
                is_hazardous: false,
                orbit_period_days: 643,
                description: "The second-largest Near-Earth Object. Historic target of the NEAR Shoemaker probe which landed on its surface in 2001."
            },
            {
                id: "20003122",
                name: "3122 Florence (1981 ET3)",
                diameter_m: 4500,
                distance_km: 7000000,
                lunar_distance: 18.23,
                velocity_kmh: 55296,
                is_hazardous: true,
                orbit_period_days: 859,
                description: "Large triple-asteroid system featuring two small orbiting moons discovered during radar observations in 2017."
            },
            {
                id: "3843821",
                name: "(2023 DW)",
                diameter_m: 50,
                distance_km: 9400000,
                lunar_distance: 24.47,
                velocity_kmh: 88740,
                is_hazardous: false,
                orbit_period_days: 271,
                description: "Small Apollo-class NEO monitored for future potential approaches; currently classified as low-threat."
            },
            {
                id: "20001620",
                name: "1620 Geographos (1951 RA)",
                diameter_m: 2500,
                distance_km: 19800000,
                lunar_distance: 51.56,
                velocity_kmh: 114120,
                is_hazardous: true,
                orbit_period_days: 508,
                description: "Highly elongated asteroid with a length roughly 2.5 times its width, creating distinct light curve signatures."
            }
        ];

        const serverNEOs = @json($asteroids);
        let activeNEOs = [...serverNEOs];
        let filteredNEOs = [...serverNEOs];
        let isSimulatedMode = {{ $isLive ? 'false' : 'true' }};
        let selectedHazardFilter = 'all';
        let currentChartMetric = 'speed';
        let chartInstance = null;

        // ANIMATED SPACE CANVAS BACKGROUND
        const spaceCanvas = document.getElementById('spaceCanvas');
        const sCtx = spaceCanvas.getContext('2d');

        let stars = [];
        let floatingAsteroids = [];
        let mousePos = { x: -1000, y: -1000 };

        function resizeSpaceCanvas() {
            spaceCanvas.width = window.innerWidth;
            spaceCanvas.height = window.innerHeight;
            initSpaceElements();
        }

        function initSpaceElements() {
            stars = [];
            const count = Math.floor((spaceCanvas.width * spaceCanvas.height) / 3000);
            for (let i = 0; i < count; i++) {
                stars.push({
                    x: Math.random() * spaceCanvas.width,
                    y: Math.random() * spaceCanvas.height,
                    size: Math.random() * 1.5 + 0.3,
                    alpha: Math.random(),
                    speed: Math.random() * 0.01 + 0.002
                });
            }

            floatingAsteroids = [];
            const astCount = 14;
            for (let i = 0; i < astCount; i++) {
                floatingAsteroids.push({
                    x: Math.random() * spaceCanvas.width,
                    y: Math.random() * spaceCanvas.height,
                    vx: (Math.random() - 0.5) * 0.4,
                    vy: (Math.random() - 0.5) * 0.4,
                    size: Math.random() * 18 + 8,
                    angle: Math.random() * Math.PI * 2,
                    vRot: (Math.random() - 0.5) * 0.02,
                    shapePoints: generateAsteroidShape(),
                    color: Math.random() > 0.3 ? '#38bdf8' : '#f43f5e'
                });
            }
        }

        function generateAsteroidShape() {
            const points = [];
            const count = 7;
            for (let i = 0; i < count; i++) {
                const angle = (i / count) * Math.PI * 2;
                const r = 0.7 + Math.random() * 0.6;
                points.push({ angle, r });
            }
            return points;
        }

        function drawSpaceBackground() {
            sCtx.fillStyle = '#030712';
            sCtx.fillRect(0, 0, spaceCanvas.width, spaceCanvas.height);

            // Draw Subtle Nebula Glows
            const grad1 = sCtx.createRadialGradient(
                spaceCanvas.width * 0.2, spaceCanvas.height * 0.3, 50,
                spaceCanvas.width * 0.2, spaceCanvas.height * 0.3, spaceCanvas.width * 0.4
            );
            grad1.addColorStop(0, 'rgba(168, 85, 247, 0.08)');
            grad1.addColorStop(1, 'transparent');
            sCtx.fillStyle = grad1;
            sCtx.fillRect(0, 0, spaceCanvas.width, spaceCanvas.height);

            const grad2 = sCtx.createRadialGradient(
                spaceCanvas.width * 0.8, spaceCanvas.height * 0.7, 50,
                spaceCanvas.width * 0.8, spaceCanvas.height * 0.7, spaceCanvas.width * 0.5
            );
            grad2.addColorStop(0, 'rgba(0, 243, 255, 0.06)');
            grad2.addColorStop(1, 'transparent');
            sCtx.fillStyle = grad2;
            sCtx.fillRect(0, 0, spaceCanvas.width, spaceCanvas.height);

            // Render Stars
            stars.forEach(star => {
                star.alpha += star.speed;
                if (star.alpha > 1 || star.alpha < 0.2) star.speed = -star.speed;
                sCtx.fillStyle = `rgba(255, 255, 255, ${Math.abs(star.alpha)})`;
                sCtx.beginPath();
                sCtx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
                sCtx.fill();
            });

            // Constellation Lines
            sCtx.strokeStyle = 'rgba(56, 189, 248, 0.08)';
            sCtx.lineWidth = 1;
            for (let i = 0; i < stars.length; i += 25) {
                if (stars[i + 1]) {
                    sCtx.beginPath();
                    sCtx.moveTo(stars[i].x, stars[i].y);
                    sCtx.lineTo(stars[i + 1].x, stars[i + 1].y);
                    sCtx.stroke();
                }
            }

            // Floating Interactive Asteroids
            floatingAsteroids.forEach(ast => {
                ast.x += ast.vx;
                ast.y += ast.vy;
                ast.angle += ast.vRot;

                if (ast.x < -50) ast.x = spaceCanvas.width + 50;
                if (ast.x > spaceCanvas.width + 50) ast.x = -50;
                if (ast.y < -50) ast.y = spaceCanvas.height + 50;
                if (ast.y > spaceCanvas.height + 50) ast.y = -50;

                // Mouse Repulsion Effect
                const dx = ast.x - mousePos.x;
                const dy = ast.y - mousePos.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 180) {
                    const force = (180 - dist) / 180;
                    ast.x += (dx / dist) * force * 3;
                    ast.y += (dy / dist) * force * 3;
                }

                sCtx.save();
                sCtx.translate(ast.x, ast.y);
                sCtx.rotate(ast.angle);

                sCtx.beginPath();
                ast.shapePoints.forEach((pt, idx) => {
                    const px = Math.cos(pt.angle) * ast.size * pt.r;
                    const py = Math.sin(pt.angle) * ast.size * pt.r;
                    if (idx === 0) sCtx.moveTo(px, py);
                    else sCtx.lineTo(px, py);
                });
                sCtx.closePath();

                sCtx.fillStyle = 'rgba(15, 23, 42, 0.8)';
                sCtx.fill();
                sCtx.strokeStyle = ast.color;
                sCtx.lineWidth = 1.5;
                sCtx.shadowColor = ast.color;
                sCtx.shadowBlur = 8;
                sCtx.stroke();
                sCtx.restore();
            });

            requestAnimationFrame(drawSpaceBackground);
        }

        window.addEventListener('resize', resizeSpaceCanvas);
        window.addEventListener('mousemove', (e) => {
            mousePos.x = e.clientX;
            mousePos.y = e.clientY;
        });

        // RADAR CANVAS PROJECTION
        const radarCanvas = document.getElementById('radarCanvas');
        const rCtx = radarCanvas.getContext('2d');

        function drawRadar() {
            rCtx.clearRect(0, 0, radarCanvas.width, radarCanvas.height);
            const cx = radarCanvas.width / 2;
            const cy = radarCanvas.height / 2;
            const maxRadius = radarCanvas.width / 2 - 10;

            activeNEOs.forEach((neo, idx) => {
                const maxDistKm = 35000000;
                const normDist = Math.min(neo.distance_km / maxDistKm, 1);
                const r = normDist * maxRadius;

                // Position based on angle index
                const angle = (idx / activeNEOs.length) * Math.PI * 2 - Math.PI / 2;
                const x = cx + Math.cos(angle) * r;
                const y = cy + Math.sin(angle) * r;

                // Orbit Line
                rCtx.beginPath();
                rCtx.arc(cx, cy, r, 0, Math.PI * 2);
                rCtx.strokeStyle = neo.is_hazardous ? 'rgba(244, 63, 94, 0.15)' : 'rgba(56, 189, 248, 0.1)';
                rCtx.lineWidth = 1;
                rCtx.stroke();

                // Object Dot
                rCtx.beginPath();
                rCtx.arc(x, y, neo.is_hazardous ? 4.5 : 3.5, 0, Math.PI * 2);
                rCtx.fillStyle = neo.is_hazardous ? '#f43f5e' : '#00f3ff';
                rCtx.shadowColor = neo.is_hazardous ? '#f43f5e' : '#00f3ff';
                rCtx.shadowBlur = 10;
                rCtx.fill();

                // Label
                rCtx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                rCtx.font = '9px Orbitron';
                rCtx.fillText(neo.name.split(' ')[0], x + 6, y + 3);
            });
        }

        // CHART METRICS VISUALIZER
        function initChart() {
            const ctx = document.getElementById('neoChart').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Metric Value',
                        data: [],
                        backgroundColor: [],
                        borderColor: [],
                        borderWidth: 1.5,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { family: 'Orbitron' },
                            bodyFont: { family: 'Rajdhani' },
                            borderColor: 'rgba(56, 189, 248, 0.3)',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#94a3b8', font: { family: 'Rajdhani', size: 11 } },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        },
                        y: {
                            ticks: { color: '#94a3b8', font: { family: 'Rajdhani', size: 11 } },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        }
                    }
                }
            });
        }

        function updateChartData() {
            if (!chartInstance) return;

            // Pakai activeNEOs supaya semua data tampil, bukan yang ter-filter
            const labels = activeNEOs.map(n => n.name);
            const data = [];
            const bgColors = [];
            const borderColors = [];

            activeNEOs.forEach(neo => {
                if (currentChartMetric === 'speed') data.push(neo.velocity_kmh);
                else if (currentChartMetric === 'distance') data.push(neo.lunar_distance);
                else if (currentChartMetric === 'diameter') data.push(neo.diameter_m);

                if (neo.is_hazardous || neo.is_potentially_hazardous_asteroid) {
                    bgColors.push('rgba(244, 63, 94, 0.4)');
                    borderColors.push('#f43f5e');
                } else {
                    bgColors.push('rgba(0, 243, 255, 0.4)');
                    borderColors.push('#00f3ff');
                }
            });

            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].data = data;
            chartInstance.data.datasets[0].backgroundColor = bgColors;
            chartInstance.data.datasets[0].borderColor = borderColors;
            chartInstance.update();
        }

        function setChartMetric(metric) {
            playSound('click');
            currentChartMetric = metric;

            const buttons = {
                speed: 'chartMetricSpeed',
                distance: 'chartMetricDistance',
                diameter: 'chartMetricDiameter'
            };
            Object.entries(buttons).forEach(([key, id]) => {
                const btn = document.getElementById(id);
                if (!btn) return;
                btn.className = key === metric
                    ? 'px-3 py-1 rounded-lg text-xs font-mono transition-all bg-purple-600 text-white shadow-md'
                    : 'px-3 py-1 rounded-lg text-xs font-mono text-slate-400 hover:text-white transition-all';
            });

            updateChartData();
        }

        // DATA RENDERERS AND STATS
        function updateStats() {
            document.getElementById('statTotal').innerText = activeNEOs.length;
    
            const hazardousCount = activeNEOs.filter(n => n.is_hazardous || n.is_potentially_hazardous_asteroid).length;
            document.getElementById('statHazardous').innerText = hazardousCount;

            const closest = [...activeNEOs].sort((a, b) => a.distance_km - b.distance_km)[0];
            document.getElementById('statClosest').innerText = closest ? `${closest.lunar_distance.toFixed(1)} LD` : '--';

            const fastest = [...activeNEOs].sort((a, b) => b.velocity_kmh - a.velocity_kmh)[0];
            document.getElementById('statMaxSpeed').innerText = fastest ? `${(fastest.velocity_kmh / 1000).toFixed(1)}k km/h` : '--';

            const largest = [...activeNEOs].sort((a, b) => b.diameter_m - a.diameter_m)[0];
            document.getElementById('fastestAsteroidLabel').innerText = fastest ? fastest.name : '--';
            document.getElementById('largestAsteroidLabel').innerText = largest ? largest.name : '--';
        }

        // FILTER & SORT LOGIC
        function setHazardFilter(status) {
            playSound('click');
            selectedHazardFilter = status;

            ['All', 'Hazardous', 'Safe'].forEach(f => {
                const btn = document.getElementById(`btnFilter${f}`);
                if (f.toLowerCase() === status) {
                    btn.className = "px-3 py-1 rounded-lg text-xs font-mono bg-cyan-500 text-black font-semibold transition-all";
                } else {
                    btn.className = "px-3 py-1 rounded-lg text-xs font-mono text-slate-400 hover:text-white transition-all";
                }
            });

            applyFiltersAndSort();
        }


        // TOAST NOTIFICATION (pengganti alert)
        function showToast(message, type = 'info', duration = 4500) {
            const styles = {
                success: { box: 'border-emerald-500/50 bg-emerald-950/90 text-emerald-300', icon: 'check-circle-2' },
                error:   { box: 'border-rose-500/50 bg-rose-950/90 text-rose-300', icon: 'alert-triangle' },
                warning: { box: 'border-amber-500/50 bg-amber-950/90 text-amber-300', icon: 'info' },
                info:    { box: 'border-cyan-500/50 bg-slate-900/95 text-cyan-300', icon: 'info' }
            };
            const s = styles[type] || styles.info;

            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-start gap-3 max-w-sm px-4 py-3 rounded-xl border shadow-lg backdrop-blur text-xs font-mono transition-all duration-300 opacity-0 translate-x-4 ${s.box}`;
            toast.innerHTML = `<i data-lucide="${s.icon}" class="w-4 h-4 mt-0.5 shrink-0"></i><span class="leading-relaxed"></span>`;
            toast.querySelector('span').textContent = message;
            toast.style.cursor = 'pointer';
            toast.title = 'Klik untuk menutup';
            toast.addEventListener('click', () => toast.remove());
            container.appendChild(toast);
            lucide.createIcons();

            requestAnimationFrame(() => toast.classList.remove('opacity-0', 'translate-x-4'));
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-4');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // MODE TOGGLE & NASA API INTEGRATION
        const scanDate = @json($selectedDate);

        function setModeUI(simulated) {
            isSimulatedMode = simulated;
            const base = 'px-3 py-2 rounded-xl border text-xs font-mono flex items-center gap-2 transition-all ';
            document.getElementById('modeText').innerText = simulated ? 'MODE: JPL SIMULATOR' : 'MODE: NASA LIVE API';
            document.getElementById('modeToggleBtn').className = base + (simulated
                ? 'bg-amber-500/20 border-amber-500/40 text-amber-400 hover:bg-amber-500/30'
                : 'bg-emerald-500/25 border-emerald-500/50 text-emerald-400 hover:bg-emerald-500/35');
        }

        // --- API KEY: tampil/sembunyi terpisah dari mode, dan disimpan di browser ---
        const API_KEY_STORAGE = 'astronet_nasa_api_key';
        function getSavedKey() { try { return localStorage.getItem(API_KEY_STORAGE) || ''; } catch (e) { return ''; } }
        function saveKey(k) { try { if (k) localStorage.setItem(API_KEY_STORAGE, k); else localStorage.removeItem(API_KEY_STORAGE); } catch (e) {} }
        function openApiBar(focus = false) {
            document.getElementById('apiKeySection').classList.remove('hidden');
            if (focus) document.getElementById('nasaApiKey').focus();
        }
        function toggleApiBar() {
            playSound('click');
            const bar = document.getElementById('apiKeySection');
            bar.classList.toggle('hidden');
            if (!bar.classList.contains('hidden')) document.getElementById('nasaApiKey').focus();
        }
        function clearSavedKey() {
            saveKey('');
            document.getElementById('nasaApiKey').value = '';
            showToast('API key tersimpan dihapus. Akan memakai DEMO_KEY.', 'info');
        }

        function toggleDataMode() {
            playSound('click');

            if (!isSimulatedMode) {
                // Live -> Simulator
                setModeUI(true);
                activeNEOs = [...simulatedNEOs];
                updateAllViews();
                showToast('Beralih ke mode JPL Simulator (data offline).', 'info');
            } else {
                // Simulator -> Live: buka input API key lalu coba konek (UI baru berubah kalau berhasil)
                openApiBar();
                fetchNASAData();
            }
        }

        async function fetchNASAData() {
            playSound('refresh');
            const typedKey = document.getElementById('nasaApiKey').value.trim();
            const apiKey = typedKey || 'DEMO_KEY';
            const url = `https://api.nasa.gov/neo/rest/v1/feed?start_date=${scanDate}&end_date=${scanDate}&api_key=${encodeURIComponent(apiKey)}`;

            showToast('Menghubungkan ke NASA NeoWS...', 'info', 2000);

            try {
                const response = await fetch(url);

                if (!response.ok) {
                    const err = new Error('HTTP ' + response.status);
                    err.status = response.status;
                    throw err;
                }

                const data = await response.json();
                const list = data.near_earth_objects?.[scanDate] ?? [];

                if (list.length === 0) {
                    showToast(`NASA terhubung, tetapi tidak ada data NEO untuk ${scanDate}.`, 'warning');
                    return;
                }

                activeNEOs = list.map(item => {
                    const approach = item.close_approach_data?.[0] ?? {};
                    return {
                        id: String(item.id),
                        name: item.name,
                        diameter_m: Math.round(item.estimated_diameter.meters.estimated_diameter_max),
                        distance_km: Math.round(parseFloat(approach.miss_distance?.kilometers ?? 0)),
                        lunar_distance: parseFloat(approach.miss_distance?.lunar ?? 0),
                        velocity_kmh: Math.round(parseFloat(approach.relative_velocity?.kilometers_per_hour ?? 0)),
                        is_hazardous: !!item.is_potentially_hazardous_asteroid,
                        description: 'Telemetry fetched live from NASA Near Earth Object Web Service (NeoWS).'
                    };
                });

                saveKey(typedKey);
                setModeUI(false);
                updateAllViews();
                showToast(`Terhubung ke NASA NeoWS — ${activeNEOs.length} objek dimuat untuk ${scanDate}.`, 'success');
            } catch (err) {
                console.warn('NASA API Error, falling back to simulated data', err);

                let reason = 'Tidak dapat menjangkau api.nasa.gov (cek koneksi internet).';
                if (err.status === 429) reason = 'Batas request API key tercapai (DEMO_KEY sangat terbatas). Coba API key pribadi.';
                else if (err.status === 403 || err.status === 401) reason = 'API key tidak valid atau ditolak NASA.';
                else if (err.status) reason = `NASA API mengembalikan error (${err.status}).`;

                setModeUI(true);
                activeNEOs = [...simulatedNEOs];
                updateAllViews();
                showToast(`Gagal terhubung ke NASA. ${reason} Kembali ke JPL Simulator.`, 'error', 7000);
                openApiBar(true); // kolom key tetap terbuka supaya bisa langsung diisi
            }
        }

        function refreshData() {
            playSound('refresh');
            if (!isSimulatedMode) {
                fetchNASAData();
            } else {
                updateAllViews();
            }
        }

        function updateAllViews() {
            updateStats();
            drawRadar();
            updateChartData();
            updateHazardChart();
            applyFiltersAndSort();
        }

        // MODAL & 3D INTERACTIVE ASTEROID VISUALIZER
        let modalAnimId = null;
        let modalAngle = 0;

        function openModal(id) {
            playSound('modal');
            const neo = activeNEOs.find(n => n.id === id);
            if (!neo) return;

            document.getElementById('modalName').innerText = neo.name;
            document.getElementById('modalId').innerText = `JPL Reference ID: ${neo.id}`;
            document.getElementById('modalDiameter').innerText = `${neo.diameter_m.toLocaleString()} m`;
            document.getElementById('modalDistance').innerText = `${(neo.distance_km / 1000000).toFixed(2)}M km`;
            document.getElementById('modalVelocity').innerText = `${neo.velocity_kmh.toLocaleString()} km/h`;
            document.getElementById('modalLD').innerText = `${neo.lunar_distance.toFixed(1)} LD`;
            document.getElementById('modalDescription').innerText = neo.description;

            const badge = document.getElementById('modalHazardLabel');
            if (neo.is_hazardous) {
                badge.innerText = 'POTENTIALLY HAZARDOUS';
                badge.className = 'absolute top-2 right-2 px-3 py-1 rounded-full text-xs font-mono font-bold border bg-rose-500/20 text-rose-400 border-rose-500/50 shadow-[0_0_10px_#f43f5e]';
            } else {
                badge.innerText = 'SAFE DISTANCE';
                badge.className = 'absolute top-2 right-2 px-3 py-1 rounded-full text-xs font-mono font-bold border bg-cyan-500/20 text-cyan-400 border-cyan-500/50 shadow-[0_0_10px_#22d3ee]';
            }

            const modal = document.getElementById('asteroidModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.firstElementChild.classList.remove('scale-95');
                modal.firstElementChild.classList.add('scale-100');
            }, 10);

            startModalCanvas(neo);
        }

        function closeModal() {
            playSound('click');
            const modal = document.getElementById('asteroidModal');
            modal.firstElementChild.classList.remove('scale-100');
            modal.firstElementChild.classList.add('scale-95');
            modal.classList.add('opacity-0');

            if (modalAnimId) cancelAnimationFrame(modalAnimId);

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function startModalCanvas(neo) {
            const mCanvas = document.getElementById('modalCanvas');
            const mCtx = mCanvas.getContext('2d');
            mCanvas.width = mCanvas.parentElement.clientWidth;
            mCanvas.height = mCanvas.parentElement.clientHeight;

            const shapePoints = generateAsteroidShape();

            function render() {
                mCtx.fillStyle = '#030712';
                mCtx.fillRect(0, 0, mCanvas.width, mCanvas.height);

                const cx = mCanvas.width / 2;
                const cy = mCanvas.height / 2;

                // Wireframe Grid Background
                mCtx.strokeStyle = 'rgba(56, 189, 248, 0.08)';
                mCtx.lineWidth = 1;
                for (let x = 0; x < mCanvas.width; x += 30) {
                    mCtx.beginPath();
                    mCtx.moveTo(x, 0);
                    mCtx.lineTo(x, mCanvas.height);
                    mCtx.stroke();
                }
                for (let y = 0; y < mCanvas.height; y += 30) {
                    mCtx.beginPath();
                    mCtx.moveTo(0, y);
                    mCtx.lineTo(mCanvas.width, y);
                    mCtx.stroke();
                }

                // Render Rotating Wireframe Asteroid
                mCtx.save();
                mCtx.translate(cx, cy);
                mCtx.rotate(modalAngle);

                const size = 60;
                mCtx.beginPath();
                shapePoints.forEach((pt, idx) => {
                    const px = Math.cos(pt.angle) * size * pt.r;
                    const py = Math.sin(pt.angle) * size * pt.r;
                    if (idx === 0) mCtx.moveTo(px, py);
                    else mCtx.lineTo(px, py);
                });
                mCtx.closePath();

                mCtx.fillStyle = 'rgba(15, 23, 42, 0.9)';
                mCtx.fill();
                mCtx.strokeStyle = neo.is_hazardous ? '#f43f5e' : '#00f3ff';
                mCtx.lineWidth = 2;
                mCtx.shadowColor = neo.is_hazardous ? '#f43f5e' : '#00f3ff';
                mCtx.shadowBlur = 15;
                mCtx.stroke();

                // Inner Scanlines
                mCtx.strokeStyle = neo.is_hazardous ? 'rgba(244, 63, 94, 0.3)' : 'rgba(0, 243, 255, 0.3)';
                mCtx.lineWidth = 1;
                shapePoints.forEach(pt => {
                    mCtx.beginPath();
                    mCtx.moveTo(0, 0);
                    mCtx.lineTo(Math.cos(pt.angle) * size * pt.r, Math.sin(pt.angle) * size * pt.r);
                    mCtx.stroke();
                });

                mCtx.restore();

                modalAngle += 0.015;
                modalAnimId = requestAnimationFrame(render);
            }

            if (modalAnimId) cancelAnimationFrame(modalAnimId);
            render();
        }

        // INITIALIZATION ON LOAD
        window.onload = function() {
            lucide.createIcons();
            resizeSpaceCanvas();
            drawSpaceBackground();
            initChart();
            initHazardChart();
            updateAllViews();

            const savedKey = getSavedKey();
            if (savedKey) document.getElementById('nasaApiKey').value = savedKey;

            if (!isSimulatedMode) {
                showToast(`Terhubung ke NASA NeoWS — ${activeNEOs.length} objek dimuat untuk ${scanDate}.`, 'success');
            } else if (savedKey) {
                fetchNASAData(); // ada key tersimpan: langsung coba NASA Live
            } else {
                showToast('Mode JPL Simulator aktif (data offline). Klik tombol API KEY untuk memasukkan key, atau MODE untuk mencoba NASA Live.', 'warning', 6000);
            }
        };
    </script>
</body>
</html>
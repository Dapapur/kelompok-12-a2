<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Observasi Asteroid - ASTRONET NEO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <style>
        body { font-family: 'Rajdhani', sans-serif; background-color: #030712; color: #f3f4f6; }
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(56, 189, 248, 0.25);
        }
        /* Styling untuk konten HTML hasil editor WYSIWYG */
        .wysiwyg-content ul { list-style-type: disc; padding-left: 1.25rem; }
        .wysiwyg-content ol { list-style-type: decimal; padding-left: 1.25rem; }
        .wysiwyg-content strong, .wysiwyg-content b { color: #38bdf8; }
        .wysiwyg-content blockquote { border-left: 3px solid #a855f7; padding-left: 0.75rem; color: #cbd5e1; font-style: italic; }

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
<body class="min-h-screen p-4 md:p-8 max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="glass-panel rounded-2xl p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b-2 border-cyan-500/50">
        <div>
            <h1 class="font-orbitron text-xl md:text-2xl font-bold text-cyan-300 tracking-wider flex items-center gap-2">
                <i data-lucide="database" class="w-6 h-6 text-cyan-400"></i>
                NEO OBSERVATION ARCHIVE
            </h1>
            <p class="text-xs font-mono text-slate-400 mt-1">
                Basis Data Observasi & Catatan Analisis Mitigasi • Operator: <span class="text-emerald-400">{{ Auth::user()->name }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('asteroid.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800/80 border border-slate-700 hover:border-cyan-400 text-slate-300 text-xs font-mono flex items-center gap-1.5 transition-all">
                <i data-lucide="radar" class="w-4 h-4 text-cyan-400"></i> Kembali ke Radar
            </a>
            <a href="{{ route('observations.export.pdf') }}" class="px-3.5 py-2 rounded-xl bg-rose-500/20 border border-rose-400/50 text-rose-300 hover:bg-rose-500/30 text-xs font-mono font-bold flex items-center gap-1.5 transition-all">
                <i data-lucide="file-text" class="w-4 h-4"></i> Export PDF
            </a>
            <a href="{{ route('observations.export.excel') }}" class="px-3.5 py-2 rounded-xl bg-emerald-500/20 border border-emerald-400/50 text-emerald-300 hover:bg-emerald-500/30 text-xs font-mono font-bold flex items-center gap-1.5 transition-all">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Export Excel
            </a>
            <a href="{{ route('observations.create') }}" class="px-4 py-2 rounded-xl bg-cyan-500/20 border border-cyan-400/60 text-cyan-300 hover:bg-cyan-500/30 text-xs font-mono font-bold flex items-center gap-1.5 transition-all">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Observasi Baru
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="glass-panel rounded-xl px-4 py-3 border-emerald-500/40 bg-emerald-950/30 flex items-center gap-2 text-xs font-mono text-emerald-300">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Tabel Arsip Observasi -->
    <div class="glass-panel rounded-2xl p-5">
        <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-950/50">
            <table id="archiveTable" class="w-full text-left text-xs font-mono border-collapse">
                <thead>
                    <tr class="bg-slate-900/90 text-cyan-400 border-b border-slate-800 uppercase">
                        <th class="p-3">Asteroid & Tanggal</th>
                        <th class="p-3">Citra Teleskop</th>
                        <th class="p-3">Parameter Orbit</th>
                        <th class="p-3">Status & Prioritas</th>
                        <th class="p-3">Catatan Analisis (WYSIWYG)</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($observations as $obs)
                        <tr class="hover:bg-slate-900/50 transition-colors align-top">
                            <td class="p-3">
                                @if ($obs->telescope_image)
                                    <button type="button"
                                        onclick="openImageModal('{{ asset('storage/' . $obs->telescope_image) }}', '{{ addslashes($obs->name) }}')"
                                        title="Klik untuk pratinjau citra"
                                        class="group block focus:outline-none">
                                        <img src="{{ asset('storage/' . $obs->telescope_image) }}" alt="{{ $obs->name }}"
                                            class="w-20 h-16 object-cover rounded-lg border border-cyan-500/40 group-hover:scale-105 group-hover:border-cyan-400 transition-all cursor-zoom-in">
                                    </button>
                                @else
                                    <div class="w-20 h-16 rounded-lg border border-dashed border-slate-700 flex items-center justify-center text-[10px] text-slate-500 text-center px-1">
                                        No Image
                                    </div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-sm text-cyan-200">{{ $obs->name }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">JPL ID: {{ $obs->jpl_id ?: '-' }}</div>
                                <div class="text-[11px] text-purple-300 mt-0.5">Tgl: {{ $obs->observation_date->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-500 mt-1">Oleh: {{ $obs->user->name ?? 'Operator' }}</div>
                            </td>
                            <td class="p-3 space-y-1">
                                <div>Diameter: <span class="text-cyan-300">{{ number_format($obs->diameter_m) }} m</span></div>
                                <div>Jarak: <span class="text-amber-300">{{ number_format($obs->distance_km) }} km</span></div>
                                <div>Kecepatan: <span class="text-purple-300">{{ number_format($obs->velocity_kmh) }} km/h</span></div>
                            </td>
                            <td class="p-3 space-y-2">
                                <div>
                                    @if ($obs->is_hazardous)
                                        <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-400 border border-rose-500/40 font-bold text-[10px]">HAZARDOUS</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/40 font-bold text-[10px]">SAFE</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-amber-300 text-[10px]">
                                        PRIORITY: {{ $obs->priority_level }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-3 max-w-md">
                                <div class="wysiwyg-content font-sans text-xs text-slate-300 leading-relaxed bg-slate-900/60 p-2.5 rounded-lg border border-slate-800">
                                    {!! $obs->analysis_notes ?: '<span class="text-slate-500 italic">Belum ada catatan analisis.</span>' !!}
                                </div>
                            </td>
                            <td class="p-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('observations.edit', $obs->id) }}" class="px-2.5 py-1.5 rounded-lg bg-amber-500/15 border border-amber-500/40 text-amber-300 hover:bg-amber-500/30 flex items-center gap-1 transition-all">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                                    </a>
                                    <form action="{{ route('observations.destroy', $obs->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus arsip asteroid {{ $obs->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/15 border border-rose-500/40 text-rose-300 hover:bg-rose-500/30 flex items-center gap-1 transition-all">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Pratinjau Citra Teleskop-->
    <div id="imageLightboxModal" onclick="closeImageModal()"
        class="fixed inset-0 z-50 hidden bg-slate-950/90 backdrop-blur-md flex flex-col justify-between p-4 sm:p-6">
        
        <!-- Bar Atas Modal -->
        <div class="flex items-center justify-between max-w-6xl w-full mx-auto text-slate-200" onclick="event.stopPropagation()">
            <div class="flex items-center gap-2.5">
                <i data-lucide="image" class="w-5 h-5 text-cyan-400"></i>
                <span id="lightboxTitle" class="font-orbitron text-sm sm:text-base font-bold tracking-wide text-cyan-300">
                    CITRA TELESKOP
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a id="lightboxDownload" href="#" download
                    class="px-3 py-1.5 rounded-lg bg-slate-800/90 border border-slate-700 hover:border-cyan-400 text-xs font-mono text-slate-200 flex items-center gap-1.5 transition-all">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-cyan-400"></i> Unduh
                </a>
                <button type="button" onclick="closeImageModal()"
                    class="p-2 rounded-lg bg-rose-500/20 border border-rose-500/40 text-rose-300 hover:bg-rose-500/30 transition-all"
                    title="Tutup (Esc)">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Area Gambar Utama -->
        <div class="flex-1 flex items-center justify-center my-4 overflow-hidden">
            <img id="lightboxImage" src="" alt="Pratinjau Citra Teleskop" onclick="event.stopPropagation()"
                class="max-h-[82vh] max-w-[92vw] object-contain rounded-xl border border-cyan-500/30 shadow-[0_0_40px_rgba(0,243,255,0.15)]">
        </div>

        <!-- Petunjuk Bawah -->
        <div class="text-center text-[11px] font-mono text-slate-400">
            Klik area gelap di luar gambar atau tekan <span class="text-cyan-300">ESC</span> untuk menutup
        </div>
    </div>

    <script>
        function openImageModal(imageUrl, title) {
            const modal = document.getElementById('imageLightboxModal');
            const img = document.getElementById('lightboxImage');
            const titleEl = document.getElementById('lightboxTitle');
            const downloadLink = document.getElementById('lightboxDownload');

            img.src = imageUrl;
            titleEl.textContent = title || 'CITRA TELESKOP';
            downloadLink.href = imageUrl;

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageLightboxModal');
            const img = document.getElementById('lightboxImage');

            modal.classList.add('hidden');
            img.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });

        
        $(document).ready(function () {
            $('#archiveTable').DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 25],
                columnDefs: [
                    { orderable: false, targets: [1, 4, 5] }
                ],
                language: {
                    search: "Cari Arsip:",
                    lengthMenu: "Tampilkan _MENU_ arsip",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ arsip",
                    emptyTable: "Belum ada data asteroid yang diarsipkan.",
                    paginate: {
                        previous: "Prev",
                        next: "Next"
                    }
                },
                drawCallback: function () {
                    lucide.createIcons();
                }
            });
        });
    </script>
</body>
</html>
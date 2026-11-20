<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Observasi Asteroid - ASTRONET NEO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- CKEditor 5 Classic CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Rajdhani', sans-serif; background-color: #030712; color: #f3f4f6; }
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(56, 189, 248, 0.25);
        }
        /* Penyesuaian tema gelap untuk CKEditor 5 */
        .ck-editor__editable {
            min-height: 180px;
            background-color: #090d16 !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
            font-family: sans-serif;
            font-size: 14px;
        }
        .ck-toolbar {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        .ck-button { color: #cbd5e1 !important; }
        .ck-button:hover, .ck-button.ck-on { background-color: #1e293b !important; color: #38bdf8 !important; }
    </style>
</head>
<body class="min-h-screen p-4 md:p-8 max-w-3xl mx-auto">

    <div class="glass-panel rounded-2xl p-6 md:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h1 class="font-orbitron text-xl font-bold text-cyan-300">ARCHIVE NEW ASTEROID</h1>
                <p class="text-xs font-mono text-slate-400">Simpan Parameter Orbit & Catatan Analisis Mitigasi</p>
            </div>
            <a href="{{ route('observations.index') }}" class="text-xs font-mono text-cyan-400 hover:underline">← Kembali ke Arsip</a>
        </div>

        @if ($errors->any())
            <div class="p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs font-mono space-y-1">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('observations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 font-mono text-xs">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-cyan-300 mb-1">NAMA / DESIGNATION ASTEROID *</label>
                    <input type="text" name="name" value="{{ old('name', $prefill['name']) }}" required placeholder="Contoh: (2024 YR4)"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-cyan-200 focus:outline-none focus:border-cyan-400">
                </div>
                <div>
                    <label class="block text-cyan-300 mb-1">JPL REFERENCE ID (OPSIONAL)</label>
                    <input type="text" name="jpl_id" value="{{ old('jpl_id', $prefill['jpl_id']) }}" placeholder="Contoh: 3542519"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-cyan-200 focus:outline-none focus:border-cyan-400">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-cyan-300 mb-1">EST. DIAMETER (METER) *</label>
                    <input type="number" name="diameter_m" value="{{ old('diameter_m', $prefill['diameter_m']) }}" required min="1"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-cyan-200 focus:outline-none focus:border-cyan-400">
                </div>
                <div>
                    <label class="block text-cyan-300 mb-1">JARAK KE BUMI (KM) *</label>
                    <input type="number" name="distance_km" value="{{ old('distance_km', $prefill['distance_km']) }}" required min="1"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-cyan-200 focus:outline-none focus:border-cyan-400">
                </div>
                <div>
                    <label class="block text-cyan-300 mb-1">KECEPATAN (KM/H) *</label>
                    <input type="number" name="velocity_kmh" value="{{ old('velocity_kmh', $prefill['velocity_kmh']) }}" required min="1"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-cyan-200 focus:outline-none focus:border-cyan-400">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div>
                    <label class="block text-cyan-300 mb-1">TANGGAL OBSERVASI *</label>
                    <input type="date" name="observation_date" value="{{ old('observation_date', $prefill['observation_date']) }}" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-cyan-200 focus:outline-none focus:border-cyan-400">
                </div>
                <div>
                    <label class="block text-cyan-300 mb-1">PRIORITAS PENGAWASAN *</label>
                    <select name="priority_level" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-cyan-200 focus:outline-none focus:border-cyan-400">
                        <option value="LOW" {{ old('priority_level') == 'LOW' ? 'selected' : '' }}>LOW</option>
                        <option value="NORMAL" {{ old('priority_level', 'NORMAL') == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                        <option value="HIGH" {{ old('priority_level') == 'HIGH' ? 'selected' : '' }}>HIGH</option>
                        <option value="CRITICAL" {{ old('priority_level') == 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label class="flex items-center gap-2.5 cursor-pointer bg-slate-950 border border-rose-500/30 px-3.5 py-2.5 rounded-xl">
                        <input type="checkbox" name="is_hazardous" value="1" {{ old('is_hazardous', $prefill['is_hazardous']) ? 'checked' : '' }}
                            class="rounded bg-slate-900 border-slate-700 text-rose-500">
                        <span class="text-rose-400 font-bold">POTENTIALLY HAZARDOUS</span>
                    </label>
                </div>
            </div>

            <!-- Editor WYSIWYG -->
            <div>
                <label class="block text-cyan-300 mb-1.5">CATATAN ANALISIS & MITIGASI (WYSIWYG EDITOR)</label>
                <textarea id="analysis_notes" name="analysis_notes">{{ old('analysis_notes') }}</textarea>
            </div>

            <!-- Upload Citra Teleskop -->
            <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-4 space-y-3">
                <label class="block text-cyan-300">CITRA TELESKOP / BUKTI VISUAL (JPG, PNG, WEBP • MAKS 2MB)</label>
                <input type="file" name="telescope_image" id="telescope_image" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event)"
                    class="block w-full text-xs text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/20 file:text-cyan-300 hover:file:bg-cyan-500/30 cursor-pointer">
                <div id="imagePreviewContainer" class="hidden pt-2">
                    <p class="text-[11px] text-slate-400 mb-1.5">Pratinjau Citra:</p>
                    <img id="imagePreview" src="#" alt="Preview Citra Teleskop" class="max-h-48 rounded-lg border border-cyan-500/40 object-cover">
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('observations.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-500/20 border border-cyan-400/60 text-cyan-300 hover:bg-cyan-500/30 font-orbitron font-bold tracking-wider">
                    SIMPAN KE DATABASE
                </button>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
        ClassicEditor
            .create(document.querySelector('#analysis_notes'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
            })
            .catch(error => console.error(error));

            function previewImage(event) {
            const file = event.target.files[0];
            const container = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');

            if (file) {
                preview.src = URL.createObjectURL(file);
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
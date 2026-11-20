<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Login - ASTRONET NEO RADAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Rajdhani', sans-serif; background-color: #030712; color: #f3f4f6; }
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(56, 189, 248, 0.25);
            box-shadow: 0 0 25px rgba(0, 243, 255, 0.12);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-cyan-950/30 via-slate-950 to-black">

    <div class="glass-panel w-full max-w-md rounded-2xl p-6 md:p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-full bg-cyan-950/80 border border-cyan-400/50 flex items-center justify-center text-cyan-400">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <h1 class="font-orbitron text-xl font-bold text-cyan-300 tracking-wider">OPERATOR ACCESS</h1>
            <p class="text-xs font-mono text-slate-400">ASTRONET // NEO COMMAND TERMINAL</p>
        </div>

        @if ($errors->any())
            <div class="p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs font-mono">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-mono text-cyan-300 mb-1">OPERATOR EMAIL</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="test@example.com"
                    class="w-full bg-slate-950/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-mono text-cyan-200 focus:outline-none focus:border-cyan-400">
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-300 mb-1">ACCESS PASSCODE</label>
                <input type="password" name="password" required
                    placeholder="••••••••"
                    class="w-full bg-slate-950/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-mono text-cyan-200 focus:outline-none focus:border-cyan-400">
            </div>

            <div class="flex items-center justify-between text-xs font-mono text-slate-400">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-cyan-500">
                    <span>Pertahankan Sesi</span>
                </label>
                <a href="{{ route('asteroid.index') }}" class="text-cyan-400 hover:underline">← Kembali ke Radar</a>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-cyan-500/20 border border-cyan-400/60 text-cyan-300 hover:bg-cyan-500/30 font-orbitron text-xs font-bold tracking-wider transition-all">
                AUTHENTICATE SESSION
            </button>
        </form>

        <div class="pt-4 border-t border-slate-800 text-center text-xs font-mono text-slate-400">
            Belum memiliki kredensial?
            <a href="{{ route('register') }}" class="text-cyan-400 hover:underline font-semibold">Daftar Operator Baru</a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
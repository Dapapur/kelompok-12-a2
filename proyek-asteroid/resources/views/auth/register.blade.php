<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Operator - ASTRONET NEO RADAR</title>
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
            <div class="w-12 h-12 mx-auto rounded-full bg-purple-950/80 border border-purple-400/50 flex items-center justify-center text-purple-400">
                <i data-lucide="user-plus" class="w-6 h-6"></i>
            </div>
            <h1 class="font-orbitron text-xl font-bold text-purple-300 tracking-wider">NEW OPERATOR CLEARANCE</h1>
            <p class="text-xs font-mono text-slate-400">ASTRONET // REGISTRATION TERMINAL</p>
        </div>

        @if ($errors->any())
            <div class="p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs font-mono space-y-1">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-mono text-cyan-300 mb-1">NAMA LENGKAP / CALLSIGN</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    placeholder="Contoh: Cmdr. Bilal"
                    class="w-full bg-slate-950/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-mono text-cyan-200 focus:outline-none focus:border-cyan-400">
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-300 mb-1">EMAIL</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    placeholder="operator@astronet.space"
                    class="w-full bg-slate-950/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-mono text-cyan-200 focus:outline-none focus:border-cyan-400">
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-300 mb-1">KATA SANDI (MIN. 6 KARAKTER)</label>
                <input type="password" name="password" required
                    class="w-full bg-slate-950/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-mono text-cyan-200 focus:outline-none focus:border-cyan-400">
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-300 mb-1">KONFIRMASI KATA SANDI</label>
                <input type="password" name="password_confirmation" required
                    class="w-full bg-slate-950/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm font-mono text-cyan-200 focus:outline-none focus:border-cyan-400">
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-500/20 border border-purple-400/60 text-purple-300 hover:bg-purple-500/30 font-orbitron text-xs font-bold tracking-wider transition-all">
                CREATE OPERATOR ACCOUNT
            </button>
        </form>

        <div class="pt-4 border-t border-slate-800 text-center text-xs font-mono text-slate-400">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-cyan-400 hover:underline font-semibold">Login di sini</a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
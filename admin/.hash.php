<?php
$generated_hash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password_to_hash'])) {
    // Generate a secure bcrypt hash
    $generated_hash = password_hash($_POST['password_to_hash'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hasher - StreamHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e2e8f0; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-neutral-900/80 border border-white/10 p-8 rounded-2xl shadow-2xl backdrop-blur-xl">
        <div class="text-center mb-8">
            <div class="inline-flex bg-gradient-to-br from-emerald-500 to-teal-400 p-3 rounded-xl shadow-lg mb-4">
                <i class="ph-fill ph-key text-white text-3xl leading-none"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Generate Hash</h1>
            <p class="text-slate-400 text-sm mt-1">Create a secure hash for login.php</p>
        </div>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">New Password</label>
                <div class="relative">
                    <i class="ph ph-lock-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="password_to_hash" required class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 pl-10 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-shadow" placeholder="Enter plain text password">
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-2.5 rounded-lg transition-colors shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                Generate Hash Code
            </button>
        </form>

        <?php if ($generated_hash): ?>
            <div class="mt-6 p-4 bg-neutral-950 border border-emerald-500/30 rounded-lg">
                <p class="text-sm text-slate-400 mb-2">Copy this hash and paste it into <strong>$admin_pass</strong> in login.php:</p>
                <code class="block w-full break-all text-emerald-400 text-sm font-mono bg-black/50 p-2 rounded border border-white/5 selection:bg-emerald-500/30">
                    <?php echo htmlspecialchars($generated_hash); ?>
                </code>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
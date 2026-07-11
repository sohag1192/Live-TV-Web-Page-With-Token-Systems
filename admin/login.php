<?php
session_start();

// --- ADMIN CREDENTIALS ---
$admin_user = 'Sohag';
$admin_pass = 'Sohag1192@#';

$error = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - StreamHub</title>
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
            <div class="inline-flex bg-gradient-to-br from-indigo-500 to-cyan-400 p-3 rounded-xl shadow-lg mb-4">
                <i class="ph-fill ph-lock-key text-white text-3xl leading-none"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Admin Access</h1>
            <p class="text-slate-400 text-sm mt-1">Login to manage StreamHub channels</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg text-sm text-center mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                <div class="relative">
                    <i class="ph ph-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="username" required class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 pl-10 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                <div class="relative">
                    <i class="ph ph-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="password" name="password" required class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 pl-10 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow">
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition-colors shadow-[0_0_15px_rgba(79,70,229,0.3)]">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
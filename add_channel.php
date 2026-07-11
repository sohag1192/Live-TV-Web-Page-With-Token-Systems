<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$jsonFile = __DIR__ . '/appdata/channel.json';
$message = '';
$msgType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $channel_id = trim($_POST['channel_id'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $desc       = trim($_POST['desc'] ?? '');
    $logo       = trim($_POST['logo'] ?? '');
    $url_suffix = trim($_POST['url_suffix'] ?? '');

    if (!empty($channel_id) && !empty($name) && !empty($url_suffix)) {
        // Read existing data
        $channels = [];
        if (file_exists($jsonFile) && is_readable($jsonFile)) {
            $content = file_get_contents($jsonFile);
            if ($content) {
                $channels = json_decode($content, true) ?? [];
            }
        }

        // Check if ID already exists
        if (isset($channels[$channel_id])) {
            $message = "Channel ID '{$channel_id}' already exists! Please use a unique ID.";
            $msgType = 'error';
        } else {
            // Add new channel
            $channels[$channel_id] = [
                'name' => $name,
                'desc' => $desc,
                'logo' => $logo,
                'url_suffix' => $url_suffix
            ];

            // Save back to file
            // Ensure appdata folder exists
            if (!is_dir(__DIR__ . '/appdata')) {
                mkdir(__DIR__ . '/appdata', 0777, true);
            }

            $saved = file_put_contents($jsonFile, json_encode($channels, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);

            if ($saved !== false) {
                $message = "Channel '{$name}' added successfully!";
                $msgType = 'success';
            } else {
                $message = "Failed to save channel. Please check file permissions for appdata/channel.json";
                $msgType = 'error';
            }
        }
    } else {
        $message = "Channel ID, Name, and URL Suffix are required fields.";
        $msgType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Channel - StreamHub Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e2e8f0; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <nav class="sticky top-0 z-50 bg-neutral-950/80 backdrop-blur-xl border-b border-white/10 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-indigo-500 to-cyan-400 p-2 rounded-xl">
                    <i class="ph-fill ph-gear text-white text-xl leading-none"></i>
                </div>
                <span class="font-bold text-xl text-white">Admin Panel</span>
            </div>
            
            <div class="flex gap-3">
                <a href="index.php" class="flex items-center gap-2 px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-white text-sm font-semibold rounded-lg transition-colors border border-white/5">
                    <i class="ph ph-house text-lg"></i> View Site
                </a>
                <form method="POST" action="" class="m-0">
                    <input type="hidden" name="logout" value="1">
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm font-semibold rounded-lg transition-colors border border-red-500/20">
                        <i class="ph ph-sign-out text-lg"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="flex-grow p-4 md:p-8 flex items-start justify-center">
        <div class="max-w-2xl w-full bg-neutral-900/70 border border-white/10 p-6 md:p-8 rounded-2xl shadow-2xl backdrop-blur-xl mt-4 md:mt-10">
            
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                <i class="ph-fill ph-plus-circle text-indigo-400"></i> Add New Channel
            </h2>

            <?php if ($message): ?>
                <div class="px-4 py-3 rounded-lg text-sm font-medium mb-6 flex items-center gap-2 <?php echo $msgType === 'success' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border border-red-500/20 text-red-400'; ?>">
                    <i class="ph-fill <?php echo $msgType === 'success' ? 'ph-check-circle' : 'ph-warning-circle'; ?> text-lg"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Channel ID (Unique Key) *</label>
                        <input type="text" name="channel_id" placeholder="e.g. gtv_live" required class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="text-xs text-slate-500 mt-1 block">No spaces, use underscores.</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Channel Name *</label>
                        <input type="text" name="name" placeholder="e.g. GTV Live HD" required class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Stream URL Suffix *</label>
                    <input type="text" name="url_suffix" placeholder="e.g. gtv_sports" required class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-xs text-slate-500 mt-1 block">This goes after <code>stream.php?stream=</code></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                    <input type="text" name="desc" placeholder="e.g. Live Sports & Entertainment" class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Logo Filename / URL</label>
                    <input type="text" name="logo" placeholder="e.g. gtv.png" class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-xs text-slate-500 mt-1 block">Place image inside <code>appdata/</code> folder or paste full image URL.</span>
                </div>

                <div class="pt-4 border-t border-white/10">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition-colors shadow-[0_0_15px_rgba(79,70,229,0.3)]">
                        <i class="ph-bold ph-floppy-disk text-lg"></i> Save Channel
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
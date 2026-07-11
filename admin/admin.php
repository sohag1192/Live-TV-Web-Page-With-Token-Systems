<?php
session_start();

// --- 1. AUTHENTICATION CHECK ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// --- 2. LOGOUT HANDLER ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// --- 3. CONFIGURATION & AUTO-CREATE DIR/FILE ---
$appdataDir = __DIR__ . '/appdata';
$jsonFile = $appdataDir . '/channel.json';
$message = '';
$msgType = '';

// Attempt to create directory if it doesn't exist
if (!is_dir($appdataDir)) {
    @mkdir($appdataDir, 0777, true);
    @chmod($appdataDir, 0777); // Try to set writable permissions
}

// Attempt to create the JSON file if it doesn't exist
if (is_dir($appdataDir) && !file_exists($jsonFile)) {
    @file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT));
    @chmod($jsonFile, 0666);
}

// Check if the system is actually writable
$isWritable = false;
if (file_exists($jsonFile) && is_writable($jsonFile)) {
    $isWritable = true;
} elseif (!file_exists($jsonFile) && is_writable($appdataDir)) {
    $isWritable = true;
}

// Load existing channels
$channels = [];
if (file_exists($jsonFile) && is_readable($jsonFile)) {
    $content = file_get_contents($jsonFile);
    if ($content) {
        $channels = json_decode($content, true) ?? [];
    }
}

// --- 4. FORM SUBMISSION HANDLER (ADD, EDIT, DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isWritable) {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        // --- DELETE LOGIC ---
        $delete_id = $_POST['delete_id'] ?? '';
        if (isset($channels[$delete_id])) {
            unset($channels[$delete_id]);
            $message = "Channel '{$delete_id}' deleted successfully!";
            $msgType = 'success';
        }
    } elseif ($action === 'save') {
        // --- ADD / EDIT LOGIC ---
        $original_id = trim($_POST['original_id'] ?? ''); 
        $channel_id  = trim($_POST['channel_id'] ?? '');
        $name        = trim($_POST['name'] ?? '');
        $desc        = trim($_POST['desc'] ?? '');
        $logo        = trim($_POST['logo'] ?? '');
        $url_suffix  = trim($_POST['url_suffix'] ?? '');

        if (!empty($channel_id) && !empty($name) && !empty($url_suffix)) {
            if ($original_id !== '' && $original_id !== $channel_id && isset($channels[$channel_id])) {
                $message = "Cannot rename ID. The Channel ID '{$channel_id}' already exists!";
                $msgType = 'error';
            } 
            elseif ($original_id === '' && isset($channels[$channel_id])) {
                $message = "Channel ID '{$channel_id}' already exists! Use a unique ID.";
                $msgType = 'error';
            } else {
                if ($original_id !== '' && $original_id !== $channel_id) {
                    unset($channels[$original_id]);
                }

                $channels[$channel_id] = [
                    'name' => $name,
                    'desc' => $desc,
                    'logo' => $logo,
                    'url_suffix' => $url_suffix
                ];

                $message = ($original_id === '') ? "Channel added successfully!" : "Channel updated successfully!";
                $msgType = 'success';
            }
        } else {
            $message = "Channel ID, Name, and URL Suffix are required fields.";
            $msgType = 'error';
        }
    }

    // Save modifications to JSON file
    if ($msgType === 'success') {
        $saved = @file_put_contents($jsonFile, json_encode($channels, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($saved === false) {
            $message = "Failed to save to channel.json. Check file permissions.";
            $msgType = 'error';
        } else {
            header("Location: admin.php?msg=" . urlencode($message) . "&type=" . $msgType);
            exit;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isWritable) {
    $message = "Action blocked. The system does not have Write Permissions.";
    $msgType = 'error';
}

// Check for redirect messages
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = $_GET['msg'];
    $msgType = $_GET['type'];
}

// --- 5. SETUP EDIT MODE VARS ---
$editMode = false;
$editData = ['id' => '', 'name' => '', 'desc' => '', 'logo' => '', 'url_suffix' => ''];

if (isset($_GET['edit']) && isset($channels[$_GET['edit']])) {
    $editMode = true;
    $editData = $channels[$_GET['edit']];
    $editData['id'] = $_GET['edit'];
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamHub Admin - Manage Channels</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e2e8f0; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-neutral-950/80 backdrop-blur-xl border-b border-white/10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-indigo-500 to-cyan-400 p-2 rounded-xl">
                    <i class="ph-fill ph-gear text-white text-xl leading-none"></i>
                </div>
                <span class="font-bold text-xl text-white">StreamHub Admin</span>
            </div>
            
            <div class="flex gap-3">
                <a href="index.php" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-white text-sm font-semibold rounded-lg transition-colors border border-white/5">
                    <i class="ph ph-house text-lg"></i> View Site
                </a>
                <a href="?logout=1" class="flex items-center gap-2 px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm font-semibold rounded-lg transition-colors border border-red-500/20">
                    <i class="ph ph-sign-out text-lg"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl mx-auto w-full p-4 md:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- ================= PERMISSION WARNING BANNER ================= -->
        <?php if (!$isWritable): ?>
            <div class="lg:col-span-12 bg-red-950/50 border-l-4 border-red-500 p-5 rounded-r-xl shadow-lg">
                <div class="flex items-start gap-4">
                    <i class="ph-fill ph-warning-octagon text-red-400 text-3xl mt-0.5"></i>
                    <div>
                        <h3 class="text-red-400 font-bold text-lg mb-1">Directory Permission Error!</h3>
                        <p class="text-red-200 text-sm mb-3">
                            The server does not have write permissions to auto-create or save data. Channels cannot be saved until this is fixed.
                        </p>
                        <div class="bg-black/40 p-3 rounded-lg border border-red-500/20 font-mono text-xs text-red-100">
                            <strong>Path:</strong> <?php echo $appdataDir; ?><br><br>
                            <strong>How to fix:</strong><br>
                            1. Go to your File Manager or SSH.<br>
                            2. Manually create a folder named <code>appdata</code> in the same directory as this file.<br>
                            3. Change the permissions (CHMOD) of the <code>appdata</code> folder to <code>777</code> or <code>775</code>.
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- ============================================================= -->

        <!-- Flash Message -->
        <?php if ($message): ?>
            <div class="lg:col-span-12 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 <?php echo $msgType === 'success' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border border-red-500/20 text-red-400'; ?>">
                <i class="ph-fill <?php echo $msgType === 'success' ? 'ph-check-circle' : 'ph-warning-circle'; ?> text-xl"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- LEFT/TOP: Add/Edit Form -->
        <div class="lg:col-span-4">
            <div class="bg-neutral-900/70 border border-white/10 p-6 rounded-2xl shadow-xl backdrop-blur-xl sticky top-24">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2 pb-4 border-b border-white/5">
                    <?php if ($editMode): ?>
                        <i class="ph-fill ph-pencil-simple text-amber-400"></i> Edit Channel
                    <?php else: ?>
                        <i class="ph-fill ph-plus-circle text-indigo-400"></i> Add New Channel
                    <?php endif; ?>
                </h2>

                <form method="POST" action="admin.php" class="space-y-4">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="original_id" value="<?php echo htmlspecialchars($editData['id']); ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Channel ID (Key) *</label>
                        <input type="text" name="channel_id" value="<?php echo htmlspecialchars($editData['id']); ?>" required <?php echo !$isWritable ? 'disabled' : ''; ?> class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm disabled:opacity-50">
                        <span class="text-[10px] text-slate-500 mt-1 block">Unique key. No spaces (e.g., gtv_live).</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Channel Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($editData['name']); ?>" required <?php echo !$isWritable ? 'disabled' : ''; ?> class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm disabled:opacity-50">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Stream URL Suffix *</label>
                        <input type="text" name="url_suffix" value="<?php echo htmlspecialchars($editData['url_suffix']); ?>" required <?php echo !$isWritable ? 'disabled' : ''; ?> class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm disabled:opacity-50">
                        <span class="text-[10px] text-slate-500 mt-1 block">Goes after <code>stream.php?stream=</code></span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
                        <input type="text" name="desc" value="<?php echo htmlspecialchars($editData['desc']); ?>" <?php echo !$isWritable ? 'disabled' : ''; ?> class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm disabled:opacity-50">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Logo URL / Path</label>
                        <input type="text" name="logo" value="<?php echo htmlspecialchars($editData['logo']); ?>" <?php echo !$isWritable ? 'disabled' : ''; ?> class="w-full bg-neutral-950 border border-white/10 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm disabled:opacity-50">
                    </div>

                    <div class="pt-4 mt-2 flex gap-3">
                        <button type="submit" <?php echo !$isWritable ? 'disabled' : ''; ?> class="flex-grow flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl transition-colors shadow-[0_0_15px_rgba(79,70,229,0.3)] disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="ph-bold ph-floppy-disk text-lg"></i> <?php echo $editMode ? 'Update' : 'Save'; ?>
                        </button>
                        
                        <?php if ($editMode): ?>
                            <a href="admin.php" class="flex items-center justify-center bg-neutral-800 hover:bg-neutral-700 text-white font-bold px-4 py-2.5 rounded-xl transition-colors border border-white/10">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT/BOTTOM: Channel List -->
        <div class="lg:col-span-8">
            <div class="bg-neutral-900/70 border border-white/10 rounded-2xl shadow-xl backdrop-blur-xl overflow-hidden flex flex-col h-full">
                
                <div class="p-5 border-b border-white/5 bg-neutral-800/40 flex justify-between items-center">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <i class="ph-fill ph-list-bullets text-cyan-400"></i> All Channels
                        <span class="bg-indigo-500/20 text-indigo-300 text-xs py-0.5 px-2 rounded-full ml-2"><?php echo count($channels); ?></span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-950/50 text-slate-400 text-xs uppercase tracking-wider">
                                <th class="p-4 font-semibold border-b border-white/5">Logo</th>
                                <th class="p-4 font-semibold border-b border-white/5">Details</th>
                                <th class="p-4 font-semibold border-b border-white/5">URL Suffix</th>
                                <th class="p-4 font-semibold border-b border-white/5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                            <?php if (empty($channels)): ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-500">
                                        <i class="ph ph-empty text-4xl mb-2 block"></i>
                                        No channels found. Add one from the left panel.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($channels as $key => $channel): ?>
                                    <tr class="hover:bg-white/[0.02] transition-colors <?php echo ($editMode && $editData['id'] === $key) ? 'bg-indigo-500/10' : ''; ?>">
                                        <td class="p-4 align-middle">
                                            <div class="w-10 h-10 rounded-lg bg-neutral-950 ring-1 ring-white/10 flex items-center justify-center p-1 overflow-hidden">
                                                <?php if (!empty($channel['logo'])): ?>
                                                    <img src="appdata/<?php echo htmlspecialchars($channel['logo']); ?>" alt="logo" class="w-full h-full object-contain" onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <i class="ph ph-television-simple text-slate-500 text-lg"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="font-bold text-white mb-0.5"><?php echo htmlspecialchars($channel['name']); ?></div>
                                            <div class="text-xs text-slate-500 font-mono bg-neutral-950 inline-block px-1.5 py-0.5 rounded border border-white/5">ID: <?php echo htmlspecialchars($key); ?></div>
                                        </td>
                                        <td class="p-4 align-middle font-mono text-xs text-cyan-400 truncate max-w-[150px]">
                                            <?php echo htmlspecialchars($channel['url_suffix']); ?>
                                        </td>
                                        <td class="p-4 align-middle text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="?edit=<?php echo urlencode($key); ?>" class="p-2 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 rounded-lg transition-colors border border-amber-500/20 <?php echo !$isWritable ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>" title="Edit">
                                                    <i class="ph-bold ph-pencil-simple text-base"></i>
                                                </a>
                                                
                                                <form method="POST" action="admin.php" class="m-0" onsubmit="return confirm('Are you sure you want to delete this channel?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($key); ?>">
                                                    <button type="submit" <?php echo !$isWritable ? 'disabled' : ''; ?> class="p-2 bg-red-500/10 text-red-400 hover:bg-red-500/20 rounded-lg transition-colors border border-red-500/20 disabled:opacity-50 disabled:cursor-not-allowed" title="Delete">
                                                        <i class="ph-bold ph-trash text-base"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
<?php
/**
 * StreamHub - Modern Live Streaming Portal
 * Upgraded Edition: Cinematic Live TV Background & Optimized PHP
 * Feature Added: Auto Landscape on Fullscreen
 */

// --- CONFIGURATION ---
const STREAM_BASE_URL = 'stream.php?stream=';
$jsonFile = __DIR__ . '/appdata/channel.json';
$counterFile = __DIR__ . '/channel_hits.json';

// --- DATA INITIALIZATION ---
$streams = [];
$hitsData = [];
$hasError = false;
$errorMessage = "";

// --- LOAD STREAMS ---
if (file_exists($jsonFile) && is_readable($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $streams = json_decode($jsonContent, true) ?? [];
} else {
    $hasError = true;
    $errorMessage = "No channels available. Please ensure appdata/channel.json exists and is readable.";
}

$defaultStream = $streams ? array_key_first($streams) : null;

// --- HELPERS ---
function encryptStreamKey(string $key): string {
    return strtr(base64_encode($key), '+/=', '-_,');
}

function decryptStreamKey(?string $encryptedKey): ?string {
    if (!$encryptedKey) return null;
    return base64_decode(strtr($encryptedKey, '-_,', '+/='), true);
}

// --- LOGIC ---
$encryptedStream = filter_input(INPUT_GET, 'stream', FILTER_DEFAULT);
$decryptedKey = decryptStreamKey($encryptedStream);

// Validate selected stream
$selectedStream = ($decryptedKey && isset($streams[$decryptedKey])) ? $decryptedKey : $defaultStream;

// --- PER-CHANNEL HIT COUNTER LOGIC ---
if ($selectedStream && !$hasError) {
    if (file_exists($counterFile) && is_readable($counterFile)) {
        $hitsData = json_decode(file_get_contents($counterFile), true) ?? [];
    }

    // Increment hit counter
    $hitsData[$selectedStream] = ($hitsData[$selectedStream] ?? 0) + 1;

    // Save with exclusive lock
    if (is_writable($counterFile) || is_writable(dirname($counterFile))) {
        file_put_contents($counterFile, json_encode($hitsData, JSON_PRETTY_PRINT), LOCK_EX);
    }
}

// --- CALCULATE STATS FOR UI ---
$current = $selectedStream ? $streams[$selectedStream] : null;
$streamUrl = $current ? STREAM_BASE_URL . $current['url_suffix'] : '';

$currentChannelHits = $hitsData[$selectedStream] ?? 0;
$totalSiteHits = array_sum($hitsData);
$totalChannels = count($streams);

$pageTitle = $current ? htmlspecialchars($current['name']) . " - StreamHub" : "StreamHub - Live Channels";
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #050505;
            color: #e2e8f0;
        }

        /* --- NEW: CINEMATIC LIVE TV AMBIENT BACKGROUND --- */
        @keyframes ambientTVGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .bg-tv-ambient {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: linear-gradient(-45deg, #050505, #1e1b4b, #312e81, #0f172a, #050505);
            background-size: 400% 400%;
            animation: ambientTVGlow 20s ease infinite;
            z-index: -3;
        }

        /* --- NEW: TV SCANLINES & NOISE EFFECTS --- */
        .scanlines {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%);
            background-size: 100% 4px;
            pointer-events: none;
            z-index: -1;
            opacity: 0.4;
        }

        .tv-noise {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.05;
            pointer-events: none;
            z-index: -2;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; border: 2px solid #0a0a0a; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        .aspect-video-ratio { position: relative; padding-bottom: 56.25%; height: 0; }
        .aspect-video-ratio iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

        .cinematic-glow { box-shadow: 0 0 80px -20px rgba(99, 102, 241, 0.4); }
        
        .channel-card { transition: all 0.25s ease-out; }
        .channel-card:hover:not(.active) { transform: translateX(6px); background-color: rgba(255, 255, 255, 0.05); }
        
        @keyframes eq {
            0% { height: 4px; }
            50% { height: 14px; }
            100% { height: 4px; }
        }
        .eq-bar { animation: eq 1s ease-in-out infinite; }
        .eq-bar:nth-child(1) { animation-delay: 0.0s; }
        .eq-bar:nth-child(2) { animation-delay: 0.2s; }
        .eq-bar:nth-child(3) { animation-delay: 0.4s; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500/30">

    <div class="bg-tv-ambient"></div>
    <div class="tv-noise"></div>
    <div class="scanlines"></div>

    <nav class="sticky top-0 z-50 bg-neutral-950/80 backdrop-blur-xl border-b border-white/10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition-opacity">
                <div class="bg-gradient-to-br from-indigo-500 to-cyan-400 p-2 rounded-xl shadow-lg shadow-indigo-500/30">
                    <i class="ph-fill ph-television-simple text-white text-xl leading-none"></i>
                </div>
                <span class="font-extrabold text-2xl tracking-tight text-white">
                    Stream<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">Hub</span>
                </span>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap justify-end">
                <div class="hidden sm:flex items-center gap-2 bg-neutral-900/80 border border-white/10 px-3 py-1.5 rounded-full shadow-inner">
                    <i class="ph-bold ph-monitor-play text-cyan-400 text-sm"></i>
                    <span class="text-[11px] font-bold text-slate-300 uppercase tracking-widest mt-px"><?php echo $totalChannels; ?> Channels</span>
                </div>
                
                <div class="flex items-center gap-3 bg-neutral-900/80 border border-white/10 px-4 py-1.5 rounded-full shadow-inner" title="Live Server Status">
                    <div class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]"></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow p-4 md:p-6 lg:p-8 relative z-10">
        <?php if ($hasError || !$current): ?>
            <div class="max-w-3xl mx-auto mt-20 p-8 bg-neutral-900/70 border border-red-500/30 rounded-2xl text-center backdrop-blur-xl">
                <i class="ph-fill ph-warning-circle text-red-400 text-5xl mb-4"></i>
                <h2 class="text-xl font-bold text-white mb-2">Stream Error</h2>
                <p class="text-slate-400"><?php echo htmlspecialchars($errorMessage ?: "The selected stream could not be found."); ?></p>
            </div>
        <?php else: ?>
            <div class="max-w-7xl mx-auto grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">
                
                <div class="xl:col-span-8 flex flex-col gap-6">
                    <div class="relative rounded-2xl overflow-hidden ring-1 ring-white/20 cinematic-glow bg-black">
                        <div class="aspect-video-ratio">
                            <iframe
                                id="streamPlayer"
                                data-stream-src="<?php echo htmlspecialchars($streamUrl); ?>"
                                allow="autoplay; encrypted-media; fullscreen"
                                allowfullscreen
                                class="w-full h-full border-0"
                            ></iframe>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-neutral-900/70 p-6 rounded-2xl border border-white/10 backdrop-blur-xl shadow-2xl gap-4">
                        <div class="flex-grow">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                 <h1 class="text-2xl font-bold text-white tracking-tight"><?php echo htmlspecialchars($current['name']); ?></h1>
                                 <div class="flex gap-2">
                                     <span class="px-2.5 py-1 bg-indigo-500/20 text-indigo-300 text-[10px] font-bold uppercase rounded-md border border-indigo-500/30 shadow-sm">HD 1080p</span>
                                     <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase rounded-md border border-emerald-500/30 shadow-sm flex items-center gap-1.5" title="Views on this channel">
                                         <i class="ph-fill ph-eye text-sm"></i> <?php echo number_format($currentChannelHits); ?>
                                     </span>
                                 </div>
                            </div>
                            <p class="text-slate-400 text-sm flex items-center gap-2 font-medium">
                                <i class="ph-fill ph-info text-slate-500"></i> <?php echo htmlspecialchars($current['desc'] ?? 'Live Broadcast'); ?>
                            </p>
                        </div>
                        
                        <div class="flex shrink-0 gap-3">
                            <button onclick="window.location.href='1.apk'" class="flex items-center gap-2 px-4 py-2.5 bg-neutral-800/80 hover:bg-neutral-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm ring-1 ring-white/10 hover:ring-white/20 focus:outline-none">
                                <i class="ph-bold ph-android-logo text-lg text-emerald-400"></i>
                                TV App
                            </button>
                            <button onclick="document.getElementById('streamPlayer').src = document.getElementById('streamPlayer').src" class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600/80 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-[0_0_15px_rgba(79,70,229,0.3)] ring-1 ring-indigo-400/50 focus:outline-none">
                                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4 flex flex-col h-full">
                    <div class="bg-neutral-900/70 rounded-2xl border border-white/10 shadow-2xl backdrop-blur-xl flex flex-col h-full max-h-[calc(100vh-8rem)]">
                        
                        <div class="shrink-0 p-4 border-b border-white/10 bg-neutral-800/40 rounded-t-2xl">
                            <h3 class="text-slate-200 text-xs font-bold uppercase tracking-[0.1em] flex items-center gap-2.5">
                                <div class="bg-indigo-500/20 p-1.5 rounded-md border border-indigo-500/20">
                                    <i class="ph-bold ph-list-dashes text-indigo-400 text-sm"></i>
                                </div>
                                All Live Channels
                            </h3>
                        </div>

                        <div class="flex-grow overflow-y-auto p-3 space-y-1.5">
                            <?php foreach ($streams as $key => $stream): 
                                $isActive = ($key === $selectedStream);
                                $encryptedKey = encryptStreamKey($key);
                                $channelHits = $hitsData[$key] ?? 0;
                            ?>
                                <a href="?stream=<?php echo urlencode($encryptedKey); ?>" 
                                   class="channel-card group flex items-center gap-4 p-3 rounded-xl border border-transparent relative overflow-hidden <?php echo $isActive ? 'active bg-gradient-to-r from-indigo-600/30 to-cyan-600/10 border-indigo-500/40 shadow-lg' : 'hover:bg-neutral-800/50'; ?>">
                                    
                                    <?php if ($isActive): ?>
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-400 shadow-[0_0_12px_rgba(129,140,248,0.9)] rounded-l-xl"></div>
                                    <?php endif; ?>

                                    <div class="w-12 h-12 shrink-0 rounded-lg <?php echo $isActive ? 'bg-indigo-950/80 ring-1 ring-indigo-400/60 shadow-[0_0_10px_rgba(99,102,241,0.3)]' : 'bg-neutral-800 ring-1 ring-white/10 group-hover:ring-white/20'; ?> flex items-center justify-center transition-all overflow-hidden p-1 z-10">
                                        <?php if (!empty($stream['logo'])): ?>
                                            <img src="appdata/<?php echo htmlspecialchars($stream['logo']); ?>" alt="Logo" class="w-full h-full object-contain">
                                        <?php elseif ($isActive): ?>
                                            <i class="ph-fill ph-play text-indigo-400 text-xl drop-shadow-[0_0_8px_rgba(129,140,248,0.8)]"></i>
                                        <?php else: ?>
                                            <i class="ph ph-television-simple text-slate-400 text-xl group-hover:text-slate-200 transition-colors"></i>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex-grow min-w-0 z-10">
                                        <h4 class="font-semibold text-sm truncate <?php echo $isActive ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> transition-colors">
                                            <?php echo htmlspecialchars($stream['name']); ?>
                                        </h4>
                                        <p class="text-[11px] uppercase tracking-wider truncate mt-0.5 <?php echo $isActive ? 'text-indigo-300 font-bold' : 'text-slate-500'; ?>">
                                            <?php echo $isActive ? 'Watching Now' : htmlspecialchars($stream['desc'] ?? ''); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="flex flex-col items-end gap-1.5 shrink-0 z-10">
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1 font-semibold bg-neutral-950/60 px-2 py-1 rounded-md border border-white/10">
                                            <i class="ph-fill ph-eye"></i> <?php echo number_format($channelHits); ?>
                                        </span>
                                        
                                        <?php if ($isActive): ?>
                                            <div class="flex items-end gap-[2px] h-3.5 px-1 pb-0.5">
                                                <div class="w-1 bg-indigo-400 rounded-t-sm eq-bar shadow-[0_0_5px_rgba(129,140,248,0.8)]"></div>
                                                <div class="w-1 bg-indigo-400 rounded-t-sm eq-bar shadow-[0_0_5px_rgba(129,140,248,0.8)]"></div>
                                                <div class="w-1 bg-indigo-400 rounded-t-sm eq-bar shadow-[0_0_5px_rgba(129,140,248,0.8)]"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="mt-auto border-t border-white/10 bg-neutral-950/90 backdrop-blur-xl relative z-10">
        <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-sm text-slate-500 font-medium">
                StreamHub Core v2.0
            </div>
            
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-300 bg-neutral-900/60 px-3 py-1.5 rounded-lg border border-white/10">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Systems Operational
                </div>
                
                <div class="flex items-center gap-2 bg-indigo-950/50 text-indigo-200 px-3.5 py-1.5 rounded-lg text-xs font-bold border border-indigo-500/30 shadow-[0_0_10px_rgba(79,70,229,0.2)]">
                    <i class="ph-bold ph-chart-line-up text-indigo-400 text-base"></i>
                    <span>Total Platform Views: <?php echo number_format($totalSiteHits); ?></span>
                </div>
                
                <img src="https://hitscounter.dev/api/hit?url=http%3A%2F%2F10.6.6.2%2F&label=&icon=graph-up&color=%236366f1&message=&style=flat&tz=Asia%2FDhaka" alt="Stats" class="opacity-60 hover:opacity-100 transition-opacity rounded h-6">
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const iframe = document.getElementById('streamPlayer');
        if (iframe) {
            const streamSrc = iframe.getAttribute('data-stream-src');
            if (streamSrc) {
                // Slight delay ensures the CSS animations load smoothly before the iframe requests data
                setTimeout(() => { iframe.src = streamSrc; }, 150);
            }
        }

        // --- FULLSCREEN AUTO LANDSCAPE LOGIC ---
        const handleFullscreenChange = async () => {
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
            
            if (isFullscreen) {
                // Lock orientation to landscape when entering fullscreen
                if (screen.orientation && screen.orientation.lock) {
                    try {
                        await screen.orientation.lock('landscape');
                    } catch (error) {
                        console.warn('Orientation lock failed:', error);
                    }
                }
            } else {
                // Unlock orientation when exiting fullscreen
                if (screen.orientation && screen.orientation.unlock) {
                    screen.orientation.unlock();
                }
            }
        };

        // Event listeners for different browsers
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', handleFullscreenChange);
    });
    </script>
</body>
</html>
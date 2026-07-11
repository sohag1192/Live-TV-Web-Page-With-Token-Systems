<?php
/**
 * StreamHub - Modern Live Streaming Portal
 * Optimized Version with Per-Channel Hit Counter
 * UI/UX Upgraded Edition - Clean Version
 */

declare(strict_types=1); // Enforce strict typing for better reliability

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
    if ($jsonContent !== false) {
        $streams = json_decode($jsonContent, true) ?? [];
    }
}

if (empty($streams)) {
    $hasError = true;
    $errorMessage = "No channels available. Please ensure appdata/channel.json exists, is readable, and contains valid JSON.";
}

// Set the first stream as default if no valid stream is requested
$defaultStream = !empty($streams) ? array_key_first($streams) : null;

// --- HELPERS ---
// Note: Base64 is encoding, not encryption. Renamed for accuracy.
function encodeStreamKey(string $key): string {
    return strtr(base64_encode($key), '+/=', '-_,');
}

function decodeStreamKey(?string $encodedKey): ?string {
    if (!$encodedKey) return null;
    $decoded = base64_decode(strtr($encodedKey, '-_,', '+/='), true);
    return $decoded !== false ? $decoded : null;
}

// --- LOGIC ---
$encodedStream = filter_input(INPUT_GET, 'stream', FILTER_DEFAULT);
$decodedKey = decodeStreamKey($encodedStream);

// Check if the decoded key exists in our stream list
$selectedStream = ($decodedKey && isset($streams[$decodedKey])) ? $decodedKey : $defaultStream;

// --- PER-CHANNEL HIT COUNTER LOGIC ---
if ($selectedStream && !$hasError) {
    // Read current stats safely
    if (file_exists($counterFile) && is_readable($counterFile)) {
        $counterContent = file_get_contents($counterFile);
        if ($counterContent !== false) {
            $hitsData = json_decode($counterContent, true) ?? [];
        }
    }

    // Increment the current selected channel
    $hitsData[$selectedStream] = ($hitsData[$selectedStream] ?? 0) + 1;

    // Save back to JSON file safely using an exclusive lock
    if (is_writable($counterFile) || (!file_exists($counterFile) && is_writable(dirname($counterFile)))) {
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
            background-color: #0a0a0a;
            background-image: 
                radial-gradient(ellipse at 0% 0%, rgba(153, 27, 27, 0.15) 0px, transparent 50%),
                radial-gradient(ellipse at 100% 100%, rgba(88, 28, 135, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
        }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; border: 2px solid #0a0a0a; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        .aspect-video-ratio { position: relative; padding-bottom: 56.25%; height: 0; }
        .aspect-video-ratio iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

        .cinematic-glow { box-shadow: 0 0 60px -15px rgba(59, 130, 246, 0.2); }
        
        .channel-card { transition: all 0.25s ease-out; }
        .channel-card:hover:not(.active) { transform: translateX(6px); background-color: rgba(255, 255, 255, 0.03); }
        
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
<body class="text-slate-300 min-h-screen flex flex-col antialiased selection:bg-indigo-500/30">

    <nav class="sticky top-0 z-50 bg-neutral-950/70 backdrop-blur-xl border-b border-white/5 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition-opacity">
                <div class="bg-gradient-to-br from-indigo-500 to-cyan-400 p-2 rounded-xl shadow-lg shadow-indigo-500/20">
                    <i class="ph-fill ph-television-simple text-white text-xl leading-none"></i>
                </div>
                <span class="font-extrabold text-2xl tracking-tight text-white">
                    Stream<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">Hub</span>
                </span>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap justify-end">
                <div class="hidden sm:flex items-center gap-2 bg-neutral-900/80 border border-white/5 px-3 py-1.5 rounded-full shadow-inner">
                    <i class="ph-bold ph-monitor-play text-cyan-400 text-sm"></i>
                    <span class="text-[11px] font-bold text-slate-300 uppercase tracking-widest mt-px"><?php echo (int)$totalChannels; ?> Channels</span>
                </div>
                
                <div class="flex items-center gap-3 bg-neutral-900/80 border border-white/5 px-4 py-1.5 rounded-full shadow-inner">
                    <div class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]"></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow p-4 md:p-6 lg:p-8">
        <?php if ($hasError || !$current): ?>
            <div class="max-w-3xl mx-auto mt-20 p-8 bg-neutral-900/50 border border-red-500/20 rounded-2xl text-center backdrop-blur-md">
                <i class="ph-fill ph-warning-circle text-red-400 text-5xl mb-4"></i>
                <h2 class="text-xl font-bold text-white mb-2">Stream Error</h2>
                <p class="text-slate-400"><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php else: ?>
            <div class="max-w-7xl mx-auto grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">
                
                <div class="xl:col-span-8 flex flex-col gap-6">
                    <div class="relative rounded-2xl overflow-hidden ring-1 ring-white/10 cinematic-glow bg-black">
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

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-neutral-900/50 p-6 rounded-2xl border border-white/5 backdrop-blur-md shadow-lg gap-4">
                        <div class="flex-grow">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                 <h1 class="text-2xl font-bold text-white tracking-tight"><?php echo htmlspecialchars($current['name']); ?></h1>
                                 <div class="flex gap-2">
                                     <span class="px-2.5 py-1 bg-indigo-500/10 text-indigo-400 text-[10px] font-bold uppercase rounded-md border border-indigo-500/20 shadow-sm">HD 1080p</span>
                                     <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-bold uppercase rounded-md border border-emerald-500/20 shadow-sm flex items-center gap-1.5" title="Views on this channel">
                                         <i class="ph-fill ph-eye text-sm"></i> <?php echo number_format((float)$currentChannelHits); ?>
                                     </span>
                                 </div>
                            </div>
                            <p class="text-slate-400 text-sm flex items-center gap-2 font-medium">
                                <i class="ph-fill ph-info text-slate-500"></i> <?php echo htmlspecialchars($current['desc']); ?>
                            </p>
                        </div>
                        
                        <div class="flex shrink-0 gap-3">
                            <button onclick="window.location.href='1.apk'" class="flex items-center gap-2 px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm ring-1 ring-white/5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="ph-bold ph-android-logo text-lg text-emerald-400"></i>
                                TV App
                            </button>
                        </div>
                        <div class="flex shrink-0 gap-3">
                            <button onclick="document.getElementById('streamPlayer').src = document.getElementById('streamPlayer').src" class="flex items-center gap-2 px-4 py-2.5 bg-neutral-800 hover:bg-neutral-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm ring-1 ring-white/5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4 flex flex-col h-full">
                    <div class="bg-neutral-900/50 rounded-2xl border border-white/5 shadow-lg backdrop-blur-md flex flex-col h-full max-h-[calc(100vh-8rem)]">
                        
                        <div class="shrink-0 p-4 border-b border-white/5 bg-neutral-800/30 rounded-t-2xl">
                            <h3 class="text-slate-300 text-xs font-bold uppercase tracking-[0.1em] flex items-center gap-2.5">
                                <div class="bg-indigo-500/20 p-1.5 rounded-md">
                                    <i class="ph-bold ph-list-dashes text-indigo-400 text-sm"></i>
                                </div>
                                All Live Channels
                            </h3>
                        </div>

                        <div class="flex-grow overflow-y-auto p-3 space-y-1.5">
                            <?php foreach ($streams as $key => $stream): 
                                $isActive = ($key === $selectedStream);
                                $encodedKey = encodeStreamKey((string)$key);
                                $channelHits = $hitsData[$key] ?? 0;
                            ?>
                                <a href="?stream=<?php echo urlencode($encodedKey); ?>" 
                                   class="channel-card group flex items-center gap-4 p-3 rounded-xl border border-transparent relative overflow-hidden <?php echo $isActive ? 'active bg-gradient-to-r from-indigo-600/20 to-cyan-600/5 border-indigo-500/30 shadow-md' : ''; ?>">
                                    
                                    <?php if ($isActive): ?>
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.8)] rounded-l-xl"></div>
                                    <?php endif; ?>

                                    <div class="w-12 h-12 shrink-0 rounded-lg <?php echo $isActive ? 'bg-indigo-950 ring-1 ring-indigo-500/50' : 'bg-neutral-800/80 ring-1 ring-white/5 group-hover:ring-white/10'; ?> flex items-center justify-center transition-all overflow-hidden p-1">
                                        <?php if (!empty($stream['logo'])): ?>
                                            <img src="appdata/<?php echo htmlspecialchars($stream['logo']); ?>" alt="Logo" class="w-full h-full object-contain">
                                        <?php elseif ($isActive): ?>
                                            <i class="ph-fill ph-play text-indigo-400 text-xl drop-shadow-[0_0_8px_rgba(129,140,248,0.5)]"></i>
                                        <?php else: ?>
                                            <i class="ph ph-television-simple text-slate-400 text-xl group-hover:text-slate-200 transition-colors"></i>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex-grow min-w-0">
                                        <h4 class="font-semibold text-sm truncate <?php echo $isActive ? 'text-white' : 'text-slate-300 group-hover:text-white'; ?> transition-colors">
                                            <?php echo htmlspecialchars($stream['name']); ?>
                                        </h4>
                                        <p class="text-[11px] uppercase tracking-wider truncate mt-0.5 <?php echo $isActive ? 'text-indigo-400 font-bold' : 'text-slate-500'; ?>">
                                            <?php echo $isActive ? 'Watching Now' : htmlspecialchars($stream['desc']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1 font-semibold bg-neutral-950/50 px-2 py-1 rounded-md border border-white/5">
                                            <i class="ph-fill ph-eye"></i> <?php echo number_format((float)$channelHits); ?>
                                        </span>
                                        
                                        <?php if ($isActive): ?>
                                            <div class="flex items-end gap-[2px] h-3.5 px-1 pb-0.5">
                                                <div class="w-1 bg-indigo-400 rounded-t-sm eq-bar"></div>
                                                <div class="w-1 bg-indigo-400 rounded-t-sm eq-bar"></div>
                                                <div class="w-1 bg-indigo-400 rounded-t-sm eq-bar"></div>
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

    <footer class="mt-auto border-t border-white/5 bg-neutral-950/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-sm text-slate-500 font-medium">
            </div>
            
            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 bg-neutral-900/50 px-3 py-1.5 rounded-lg border border-white/5">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Systems Operational
                </div>
                
                <div class="flex items-center gap-2 bg-indigo-950/30 text-indigo-200 px-3.5 py-1.5 rounded-lg text-xs font-bold border border-indigo-500/20 shadow-sm">
                    <i class="ph-bold ph-chart-line-up text-indigo-400 text-base"></i>
                    <span>Total Platform Views: <?php echo number_format((float)$totalSiteHits); ?></span>
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
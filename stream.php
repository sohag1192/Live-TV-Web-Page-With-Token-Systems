<?php
/**
 * Generates secure tokens for HLS streaming with CDNBye P2P acceleration
 */

// ============================================================================
// CONFIGURATION SETTINGS
// ============================================================================
$flussonic = 'http://youserverip';  // Flussonic server address
$key = 'sohag';                          // Secret key from flussonic.conf (KEEP SECRET!)
$lifetime = 300 * 3;                     // Token validity: 3 hours (900 seconds)
$desync = 300;                           // Time sync tolerance: 5 minutes

// ============================================================================
// REQUEST PARAMETERS
// ============================================================================
$stream = $_GET['stream'] ?? '';         // Stream name from query string (?stream=channel_name)
$type = $_GET['type'] ?? '';             // Optional stream type parameter
$ipaddr = 'no_check_ip';                 // IP checking disabled for flexibility

// ============================================================================
// TOKEN GENERATION
// ============================================================================
$starttime = time() - $desync;           // Token start time with desync buffer
$endtime = $starttime + $lifetime;       // Token expiration time
$salt = bin2hex(openssl_random_pseudo_bytes(16)); // Random salt for security

// Create hash string and generate SHA1 token
$hashsrt = $stream . $ipaddr . $starttime . $endtime . $key . $salt;
$hash = sha1($hashsrt);

// Format final token and streaming URL
$token = $hash . '-' . $salt . '-' . $endtime . '-' . $starttime;
$link = $flussonic . '/' . $stream . '/index.m3u8?token=' . $token . '&autoplay=true';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player</title>
    
    <!-- ========================================================================== -->
    <!-- STYLES -->
    <!-- ========================================================================== -->
    <style type="text/css" media="screen">
        body {
            background-color: #000;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        
        #player {
            width: 100%;
            height: 100vh;
        }
    </style>
    <link rel="stylesheet" href="styles.css?v=1.1">

    <!-- ========================================================================== -->
    <!-- EXTERNAL LIBRARIES -->
    <!-- ========================================================================== -->
    <!-- jQuery Library for Enhanced Functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <!-- Clappr Video Player (Latest Version) -->
    <script src="https://cdn.jsdelivr.net/clappr/latest/clappr.min.js"></script>
    
    <!-- Enhanced HLS Playback Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/@clappr/hlsjs-playback@1.0.1/dist/hlsjs-playback.min.js"></script>
    
    <!-- Quality Level Selector Plugin -->
    <script src="https://cdn.jsdelivr.net/clappr.level-selector/latest/level-selector.min.js"></script>
    
    <!-- CDNBye P2P Engine for HLS -->
    <script src="//cdn.jsdelivr.net/npm/@swarmcloud/hls/p2p-engine.min.js"></script>
</head>

<body style="margin:0px;padding:0px;overflow:hidden">
    <!-- ========================================================================== -->
    <!-- VIDEO PLAYER CONTAINER -->
    <!-- ========================================================================== -->
    <div id="player"></div>

    <!-- ========================================================================== -->
    <!-- P2P STREAMING INITIALIZATION -->
    <!-- ========================================================================== -->
    <script>
        function getURLParameterByName(name) {
            const url = window.location.href;
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }
        var p2pConfig = {
            swFile: 'sw.js',
            live: true,
            trackerZone: 'eu',
            token: "TVdxQdDHg",
            maxPeerConnections: 20,
            useHttpRange: true,
            announceLocation: 'eu'
        };


        var player = new Clappr.Player({
            source: '<?php echo $link ?>',
            parentId: "#player",
            width: "100%",
            height: "100%",
            autoPlay: true,
            
            plugins: [HlsjsPlayback, LevelSelector],
            mimeType: "application/x-mpegURL",
            mediacontrol: { 
                seekbar: "#ff0000",
                buttons: "#eee"
            },
            
            hlsjsConfig: {
                debug: false,
                enableWorker: true,
                lowLatencyMode: true,
                backBufferLength: 90,
                maxBufferLength: 30,
                maxMaxBufferLength: 600,
                maxBufferSize: 60 * 1000 * 1000,
                maxBufferHole: 0.5,
                highBufferWatchdogPeriod: 2,
                nudgeOffset: 0.1,
                nudgeMaxRetry: 3,
                maxFragLookUpTolerance: 0.25,
                liveSyncDurationCount: 3,
                liveMaxLatencyDurationCount: 10,
                enableSoftwareAES: true,
                manifestLoadingTimeOut: 10000,
                manifestLoadingMaxRetry: 1,
                manifestLoadingRetryDelay: 1000,
                levelLoadingTimeOut: 10000,
                levelLoadingMaxRetry: 4,
                levelLoadingRetryDelay: 1000,
                fragLoadingTimeOut: 20000,
                fragLoadingMaxRetry: 6,
                fragLoadingRetryDelay: 1000,
                startFragPrefetch: true,
                testBandwidth: true,
                progressive: false,
                xhrSetup: function(xhr, url) {
                    xhr.withCredentials = false;
                }
            },
            
            levelSelectorConfig: {
                title: 'Quality',
                labels: {
                    2: 'High',
                    1: 'Med', 
                    0: 'Low'
                },
                labelCallback: function(playbackLevel, customLabel) {
                    return customLabel + ' (' + playbackLevel.level.height + 'p)';
                }
            },
            
            playback: {
                preload: 'metadata',
                recycleVideo: false,
                playInline: true,
                crossOrigin: 'anonymous'
            }
        });

        function initializeP2PStreaming() {
            P2PEngineHls.tryRegisterServiceWorker(p2pConfig).then(() => {
                p2pConfig.hlsjsInstance = player.core.getCurrentPlayback()?._hls;
                var engine = new P2PEngineHls(p2pConfig);
                player.play();
            }).catch(error => {
                player.play();
            });
        }
        player.on('ready', function() {
            initializeP2PStreaming();
        });

        player.on('error', function(error) {
            console.error('Player error:', error);
        });
    </script>
</body>
</html>

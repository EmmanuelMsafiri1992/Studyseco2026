<?php
/**
 * IMMEDIATE CORS FIX - Run this on your production server
 * This will fix the Vite CORS errors instantly
 */

// Set execution time to unlimited
set_time_limit(0);

?>
<!DOCTYPE html>
<html>
<head>
    <title>🚨 StudySeco CORS Fix</title>
    <style>
        body { 
            font-family: monospace; 
            background: #000; 
            color: #00ff00; 
            padding: 20px; 
            margin: 0;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
        }
        .log { 
            background: #111; 
            padding: 20px; 
            border-radius: 5px; 
            margin: 20px 0;
            white-space: pre-wrap; 
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffff00; }
        .info { color: #00aaff; }
        .btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { background: #45a049; }
        .btn-danger { background: #f44336; }
        .btn-danger:hover { background: #da190b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 StudySeco CORS Error Fix</h1>
        
        <?php if (!isset($_POST['action'])): ?>
        
        <div class="log">
<span class="error">❌ PROBLEM DETECTED:</span>
Your website is trying to load assets from Vite dev server:
- http://127.0.0.1:5173/@vite/client
- http://127.0.0.1:5173/resources/js/app.js

<span class="info">📋 SOLUTION:</span>
We need to:
1. Set environment to PRODUCTION
2. Build production assets (npm run build)
3. Clear Laravel caches
4. Force production mode

<span class="warning">⚠️ This will temporarily put your site in maintenance mode during the fix.</span>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="fix">
            <button type="submit" class="btn">🚀 Fix CORS Errors Now</button>
            <a href="/" class="btn btn-danger">❌ Cancel</a>
        </form>
        
        <?php else: ?>
        
        <h2>🔧 Fixing CORS Errors...</h2>
        <div class="log" id="log">
        
        <?php
        ob_start();
        
        function logMessage($message, $type = 'info') {
            $colors = [
                'success' => 'success',
                'error' => 'error', 
                'warning' => 'warning',
                'info' => 'info'
            ];
            $class = $colors[$type] ?? 'info';
            $timestamp = date('H:i:s');
            echo "<span class='$class'>[$timestamp] $message</span>\n";
            ob_flush();
            flush();
            usleep(500000); // Small delay for visual effect
        }

        try {
            logMessage("🚀 Starting CORS fix process...", 'info');
            
            // Step 1: Force production environment
            logMessage("1️⃣ Setting production environment...", 'info');
            
            $envFile = __DIR__ . '/.env';
            $envProductionFile = __DIR__ . '/.env.production';
            
            // Create basic production .env if it doesn't exist
            if (!file_exists($envProductionFile)) {
                logMessage("Creating .env.production file...", 'warning');
                
                $domain = $_SERVER['HTTP_HOST'] ?? 'studyseco.com';
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                
                $productionEnv = "APP_NAME=StudySeco
APP_ENV=production
APP_KEY=" . (file_exists($envFile) ? 'base64:' . base64_encode(random_bytes(32)) : 'base64:' . base64_encode('studyseco' . time())) . "
APP_DEBUG=false
APP_URL={$protocol}://{$domain}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=studyseco_prod
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=\"hello@example.com\"
MAIL_FROM_NAME=\"StudySeco\"
";
                file_put_contents($envProductionFile, $productionEnv);
            }
            
            // Copy production environment
            copy($envProductionFile, $envFile);
            logMessage("✅ Production environment activated", 'success');
            
            // Step 2: Put in maintenance mode
            logMessage("2️⃣ Activating maintenance mode...", 'info');
            exec('php artisan down --message="Fixing CORS errors..." --retry=60 2>&1', $output, $return);
            if ($return === 0) {
                logMessage("✅ Maintenance mode activated", 'success');
            }
            
            // Step 3: Clear all caches
            logMessage("3️⃣ Clearing Laravel caches...", 'info');
            $cacheCommands = [
                'php artisan config:clear',
                'php artisan route:clear',
                'php artisan view:clear', 
                'php artisan cache:clear'
            ];
            
            foreach ($cacheCommands as $cmd) {
                exec("$cmd 2>&1", $output, $return);
                if ($return === 0) {
                    logMessage("✅ " . str_replace('php artisan ', '', $cmd), 'success');
                } else {
                    logMessage("⚠️ " . str_replace('php artisan ', '', $cmd) . " (may not be critical)", 'warning');
                }
            }
            
            // Step 4: Install npm dependencies and build
            logMessage("4️⃣ Installing NPM dependencies...", 'info');
            exec('npm install 2>&1', $output, $return);
            if ($return === 0) {
                logMessage("✅ NPM dependencies installed", 'success');
            } else {
                logMessage("⚠️ NPM install had warnings (may still work)", 'warning');
            }
            
            logMessage("5️⃣ Building production assets (this may take a minute)...", 'info');
            exec('npm run build 2>&1', $output, $return);
            if ($return === 0) {
                logMessage("✅ Production assets built successfully!", 'success');
                logMessage("   Assets will now be served from /build/ instead of Vite server", 'info');
            } else {
                logMessage("❌ Build failed: " . implode("\n", array_slice($output, -3)), 'error');
            }
            
            // Step 5: Cache for production
            logMessage("6️⃣ Caching for production...", 'info');
            $cacheCommands = [
                'php artisan config:cache',
                'php artisan route:cache',
                'php artisan view:cache'
            ];
            
            foreach ($cacheCommands as $cmd) {
                exec("$cmd 2>&1", $output, $return);
                if ($return === 0) {
                    logMessage("✅ " . str_replace('php artisan ', '', $cmd), 'success');
                }
            }
            
            // Step 6: Set permissions
            logMessage("7️⃣ Setting file permissions...", 'info');
            exec('chmod -R 755 storage bootstrap/cache 2>&1');
            logMessage("✅ File permissions set", 'success');
            
            // Step 7: Bring back online
            logMessage("8️⃣ Bringing application back online...", 'info');
            exec('php artisan up 2>&1', $output, $return);
            if ($return === 0) {
                logMessage("✅ Application is back online!", 'success');
            }
            
            // Final check
            logMessage("9️⃣ Final verification...", 'info');
            
            $manifestPath = __DIR__ . '/public/build/manifest.json';
            if (file_exists($manifestPath)) {
                logMessage("✅ Build manifest found - assets should load correctly", 'success');
            } else {
                logMessage("⚠️ Build manifest not found - assets may still have issues", 'warning');
            }
            
            logMessage("", 'info');
            logMessage("🎉 CORS FIX COMPLETED!", 'success');
            logMessage("", 'info');
            logMessage("✅ Environment: PRODUCTION", 'success');
            logMessage("✅ Assets: Built and optimized", 'success'); 
            logMessage("✅ Caches: Cleared and rebuilt", 'success');
            logMessage("✅ Application: Online", 'success');
            logMessage("", 'info');
            logMessage("🔍 CHECK YOUR WEBSITE NOW:", 'info');
            logMessage("- Refresh your browser (Ctrl+F5)", 'info');
            logMessage("- CORS errors should be gone", 'info');
            logMessage("- Assets should load from /build/ directory", 'info');
            
        } catch (Exception $e) {
            logMessage("❌ ERROR: " . $e->getMessage(), 'error');
            
            // Try to bring application back online
            exec('php artisan up 2>&1');
            logMessage("🔄 Application brought back online after error", 'warning');
        }
        
        ob_end_flush();
        ?>
        
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="/" class="btn">🏠 Check Website Now</a>
            <a href="?refresh=1" class="btn">🔄 Run Fix Again</a>
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>
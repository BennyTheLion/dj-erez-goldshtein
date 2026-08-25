<?php
require_once __DIR__ . '/auth.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!verifyCsrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'בקשה לא תקינה, רענן את הדף ונסה שוב']);
    exit;
}

$existing = glob(VIDEOS_DIR . '*.mp4');
if ($existing !== false && count($existing) >= MAX_VIDEOS) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'הגעת למגבלה של ' . MAX_VIDEOS . ' סרטונים. יש למחוק סרטון קיים לפני העלאת סרטון חדש.'
    ]);
    exit;
}

if (empty($_FILES['video'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'שגיאה בהעלאת הקובץ']);
    exit;
}

if ($_FILES['video']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['video']['error'] === UPLOAD_ERR_FORM_SIZE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'הקובץ גדול מדי. הגודל המרבי הוא ' . (int)(MAX_VIDEO_SIZE / 1024 / 1024) . 'MB'
    ]);
    exit;
}

if ($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'שגיאה בהעלאת הקובץ']);
    exit;
}

$file = $_FILES['video'];

if ($file['size'] > MAX_VIDEO_SIZE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'הקובץ גדול מדי. הגודל המרבי הוא ' . (int)(MAX_VIDEO_SIZE / 1024 / 1024) . 'MB'
    ]);
    exit;
}

$handle = fopen($file['tmp_name'], 'rb');
$header = $handle ? fread($handle, 12) : '';
if ($handle) {
    fclose($handle);
}
// MP4 files store an "ftyp" box starting at byte offset 4
$isMp4Signature = strlen($header) === 12 && substr($header, 4, 4) === 'ftyp';

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!$isMp4Signature || $mime !== 'video/mp4' || $ext !== 'mp4') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ניתן להעלות קבצי MP4 בלבד']);
    exit;
}

$filename = 'vid_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.mp4';
$destination = VIDEOS_DIR . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'שגיאה בשמירת הקובץ']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'הסרטון הועלה בהצלחה', 'filename' => $filename]);

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

$existing = glob(IMAGES_DIR . '*.webp');
if ($existing !== false && count($existing) >= MAX_IMAGES) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'הגעת למגבלה של ' . MAX_IMAGES . ' תמונות. יש למחוק תמונה קיימת לפני העלאת תמונה חדשה.'
    ]);
    exit;
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'שגיאה בהעלאת הקובץ']);
    exit;
}

$file = $_FILES['image'];

if ($file['size'] > MAX_IMAGE_SIZE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'הקובץ גדול מדי. הגודל המרבי הוא ' . (int)(MAX_IMAGE_SIZE / 1024 / 1024) . 'MB'
    ]);
    exit;
}

$handle = fopen($file['tmp_name'], 'rb');
$header = $handle ? fread($handle, 12) : '';
if ($handle) {
    fclose($handle);
}
$isWebp = strlen($header) === 12 && substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP';

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!$isWebp || $mime !== 'image/webp' || $ext !== 'webp') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ניתן להעלות קבצי WEBP בלבד']);
    exit;
}

$filename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.webp';
$destination = IMAGES_DIR . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'שגיאה בשמירת הקובץ']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'התמונה הועלתה בהצלחה', 'filename' => $filename]);

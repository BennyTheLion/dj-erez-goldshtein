<?php
require_once __DIR__ . '/auth.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: [];

if (!verifyCsrf($data['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'בקשה לא תקינה, רענן את הדף ונסה שוב']);
    exit;
}

$filename = basename((string)($data['filename'] ?? ''));
$path = IMAGES_DIR . $filename;

if ($filename === '' || !preg_match('/^[A-Za-z0-9_\-]+\.webp$/', $filename) || !file_exists($path)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'הקובץ לא נמצא']);
    exit;
}

unlink($path);
echo json_encode(['success' => true, 'message' => 'התמונה נמחקה']);

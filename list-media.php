<?php
header('Content-Type: application/json; charset=utf-8');

$imagesDir = __DIR__ . '/Images/gallery/';
$videosDir = __DIR__ . '/Videos/';

$images = [];
if (is_dir($imagesDir)) {
    $files = glob($imagesDir . '*.webp') ?: [];
    natsort($files);
    foreach ($files as $f) {
        $images[] = 'Images/gallery/' . basename($f);
    }
}

$videos = [];
if (is_dir($videosDir)) {
    $files = glob($videosDir . '*.mp4') ?: [];
    natsort($files);
    foreach ($files as $f) {
        $videos[] = 'Videos/' . basename($f);
    }
}

echo json_encode(['images' => array_values($images), 'videos' => array_values($videos)]);

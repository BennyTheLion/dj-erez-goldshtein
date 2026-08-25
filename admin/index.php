<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$images = glob(IMAGES_DIR . '*.webp') ?: [];
natsort($images);
$videos = glob(VIDEOS_DIR . '*.mp4') ?: [];
natsort($videos);

$imageCount = count($images);
$videoCount = count($videos);
$token = csrfToken();
$maxImageMb = (int)(MAX_IMAGE_SIZE / 1024 / 1024);
$maxVideoMb = (int)(MAX_VIDEO_SIZE / 1024 / 1024);
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ניהול גלריה | DJ Erez Goldshtein</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #fff;
            padding: 2rem 5%;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        h1 {
            background: linear-gradient(135deg, #ff0080, #ff8c00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logout-link {
            color: #fff;
            text-decoration: none;
            padding: 0.6rem 1.5rem;
            border: 2px solid rgba(255, 0, 128, 0.5);
            border-radius: 50px;
            transition: all 0.3s;
        }
        .logout-link:hover {
            background: rgba(255, 0, 128, 0.2);
        }
        section {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 0, 128, 0.2);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        h2 {
            color: #ff8c00;
            margin-bottom: 0.5rem;
        }
        .count {
            color: #ccc;
            margin-bottom: 1.5rem;
        }
        .hint {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        .upload-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        input[type="file"] {
            color: #ccc;
        }
        button {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #ff0080, #ff8c00);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .message {
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: none;
        }
        .message.success {
            display: block;
            background: rgba(0, 200, 100, 0.15);
            border: 1px solid rgba(0, 200, 100, 0.4);
            color: #7dffb0;
        }
        .message.error {
            display: block;
            background: rgba(255, 0, 0, 0.15);
            border: 1px solid rgba(255, 0, 0, 0.4);
            color: #ff8080;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.2rem;
        }
        .item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 4 / 3;
        }
        .item img,
        .item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .item .delete-btn {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(200, 0, 0, 0.85);
            border: none;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
            line-height: 1;
            padding: 0;
        }
        .empty {
            color: #888;
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>ניהול גלריה</h1>
        <a href="logout.php" class="logout-link">התנתק</a>
    </header>

    <section>
        <h2>תמונות (WEBP)</h2>
        <p class="count" id="imageCount"><?= $imageCount ?> מתוך <?= MAX_IMAGES ?></p>
        <p class="hint">קבצי WEBP בלבד, עד <?= $maxImageMb ?>MB לתמונה. במגבלה של <?= MAX_IMAGES ?> תמונות יש למחוק תמונה קיימת לפני העלאת חדשה.</p>
        <div class="message" id="imageMessage"></div>
        <div class="upload-row">
            <input type="file" id="imageInput" accept=".webp,image/webp">
            <button id="imageUploadBtn">העלה תמונה</button>
        </div>
        <div class="grid" id="imageGrid">
            <?php if (empty($images)): ?>
                <p class="empty">אין תמונות עדיין</p>
            <?php else: foreach ($images as $path): $name = basename($path); ?>
                <div class="item" data-filename="<?= htmlspecialchars($name) ?>">
                    <img src="../Images/gallery/<?= htmlspecialchars($name) ?>" alt="">
                    <button class="delete-btn" data-type="image" data-filename="<?= htmlspecialchars($name) ?>">&times;</button>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </section>

    <section>
        <h2>סרטונים (MP4)</h2>
        <p class="count" id="videoCount"><?= $videoCount ?> מתוך <?= MAX_VIDEOS ?></p>
        <p class="hint">קבצי MP4 בלבד, עד <?= $maxVideoMb ?>MB לסרטון. במגבלה של <?= MAX_VIDEOS ?> סרטונים יש למחוק סרטון קיים לפני העלאת חדש.</p>
        <div class="message" id="videoMessage"></div>
        <div class="upload-row">
            <input type="file" id="videoInput" accept=".mp4,video/mp4">
            <button id="videoUploadBtn">העלה סרטון</button>
        </div>
        <div class="grid" id="videoGrid">
            <?php if (empty($videos)): ?>
                <p class="empty">אין סרטונים עדיין</p>
            <?php else: foreach ($videos as $path): $name = basename($path); ?>
                <div class="item" data-filename="<?= htmlspecialchars($name) ?>">
                    <video src="../Videos/<?= htmlspecialchars($name) ?>#t=0.5" muted preload="metadata"></video>
                    <button class="delete-btn" data-type="video" data-filename="<?= htmlspecialchars($name) ?>">&times;</button>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </section>

    <script>
        const csrfToken = <?= json_encode($token) ?>;
        const MAX_IMAGES = <?= MAX_IMAGES ?>;
        const MAX_VIDEOS = <?= MAX_VIDEOS ?>;

        function showMessage(el, text, isError) {
            el.textContent = text;
            el.className = 'message ' + (isError ? 'error' : 'success');
        }

        function hideMessage(el) {
            el.className = 'message';
        }

        // Image upload
        const imageInput = document.getElementById('imageInput');
        const imageUploadBtn = document.getElementById('imageUploadBtn');
        const imageMessage = document.getElementById('imageMessage');
        const imageGrid = document.getElementById('imageGrid');
        const imageCount = document.getElementById('imageCount');

        imageUploadBtn.addEventListener('click', async () => {
            hideMessage(imageMessage);
            const file = imageInput.files[0];
            if (!file) {
                showMessage(imageMessage, 'יש לבחור קובץ', true);
                return;
            }
            const formData = new FormData();
            formData.append('image', file);
            formData.append('csrf', csrfToken);

            imageUploadBtn.disabled = true;
            imageUploadBtn.textContent = 'מעלה...';

            try {
                const res = await fetch('upload-image.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    showMessage(imageMessage, result.message, false);
                    location.reload();
                } else {
                    showMessage(imageMessage, result.message, true);
                }
            } catch (err) {
                showMessage(imageMessage, 'שגיאה בהעלאה', true);
            } finally {
                imageUploadBtn.disabled = false;
                imageUploadBtn.textContent = 'העלה תמונה';
            }
        });

        // Video upload
        const videoInput = document.getElementById('videoInput');
        const videoUploadBtn = document.getElementById('videoUploadBtn');
        const videoMessage = document.getElementById('videoMessage');
        const videoGrid = document.getElementById('videoGrid');
        const videoCount = document.getElementById('videoCount');

        videoUploadBtn.addEventListener('click', async () => {
            hideMessage(videoMessage);
            const file = videoInput.files[0];
            if (!file) {
                showMessage(videoMessage, 'יש לבחור קובץ', true);
                return;
            }
            const formData = new FormData();
            formData.append('video', file);
            formData.append('csrf', csrfToken);

            videoUploadBtn.disabled = true;
            videoUploadBtn.textContent = 'מעלה...';

            try {
                const res = await fetch('upload-video.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    showMessage(videoMessage, result.message, false);
                    location.reload();
                } else {
                    showMessage(videoMessage, result.message, true);
                }
            } catch (err) {
                showMessage(videoMessage, 'שגיאה בהעלאה', true);
            } finally {
                videoUploadBtn.disabled = false;
                videoUploadBtn.textContent = 'העלה סרטון';
            }
        });

        // Delete handling (event delegation)
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.delete-btn');
            if (!btn) return;

            const type = btn.dataset.type;
            const filename = btn.dataset.filename;
            if (!confirm('האם למחוק את הקובץ?')) return;

            const endpoint = type === 'image' ? 'delete-image.php' : 'delete-video.php';
            btn.disabled = true;

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ filename, csrf: csrfToken })
                });
                const result = await res.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                    btn.disabled = false;
                }
            } catch (err) {
                alert('שגיאה במחיקה');
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>

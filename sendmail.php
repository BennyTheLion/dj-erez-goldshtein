<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// בדיקה שהבקשה היא POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// הגנת spam - honeypot: שדה נסתר שרק בוטים ממלאים
if (!empty($_POST['website'])) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'ההודעה נשלחה בהצלחה!']);
    exit;
}

// קבלת הנתונים מהטופס
$name = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$phone = isset($_POST['phone']) ? strip_tags(trim($_POST['phone'])) : '';
$message = isset($_POST['message']) ? strip_tags(trim($_POST['message'])) : '';

// ולידציה
$errors = [];

if (empty($name)) {
    $errors[] = 'שם הוא שדה חובה';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'אימייל לא תקין';
}

if (empty($phone)) {
    $errors[] = 'טלפון הוא שדה חובה';
}

if (empty($message)) {
    $errors[] = 'הודעה היא שדה חובה';
}

// אם יש שגיאות
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'נא למלא את כל השדות',
        'errors' => $errors
    ]);
    exit;
}

// הגדרות אימייל
$to = 'erezgdj@gmail.com';
$subject = 'פנייה חדשה מהאתר - ' . $name;

// בניית תוכן האימייל
$email_content = "
<html dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background-color: #ffffff; border-radius: 10px; padding: 30px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #ff0080, #ff8c00); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; margin: -30px -30px 20px -30px; }
        .header h2 { margin: 0; }
        .info-row { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; color: #ff0080; margin-bottom: 5px; }
        .info-value { color: #333; }
        .message-box { background-color: #f9f9f9; padding: 15px; border-right: 4px solid #ff0080; border-radius: 5px; }
        .footer { text-align: center; color: #888; margin-top: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🎵 פנייה חדשה מהאתר</h2>
        </div>
        
        <div class='info-row'>
            <div class='info-label'>שם מלא:</div>
            <div class='info-value'>" . htmlspecialchars($name) . "</div>
        </div>
        
        <div class='info-row'>
            <div class='info-label'>אימייל:</div>
            <div class='info-value'><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>
        </div>
        
        <div class='info-row'>
            <div class='info-label'>טלפון:</div>
            <div class='info-value'><a href='tel:" . htmlspecialchars($phone) . "'>" . htmlspecialchars($phone) . "</a></div>
        </div>
        
        <div class='info-row'>
            <div class='info-label'>פרטי האירוע:</div>
            <div class='message-box'>" . nl2br(htmlspecialchars($message)) . "</div>
        </div>
        
        <div class='footer'>
            <p>מייל זה נשלח מטופס יצירת הקשר באתר DJ Erez Goldshtein</p>
            <p>תאריך: " . date('d/m/Y H:i') . "</p>
        </div>
    </div>
</body>
</html>
";

// כותרות האימייל
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: DJ Erez Website <erezgdj@gmail.com>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion()
];

// שליחת האימייל
$mail_sent = mail($to, $subject, $email_content, implode("\r\n", $headers));

// שליחת מייל אוטומטי ללקוח
if ($mail_sent) {
    $customer_subject = 'תודה על פנייתך - DJ Erez Goldshtein';
    $customer_message = "
    <html dir='rtl'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
            .container { background-color: #ffffff; border-radius: 10px; padding: 30px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #ff0080, #ff8c00); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; margin: -30px -30px 20px -30px; }
            .content { line-height: 1.8; color: #333; }
            .highlight { background-color: #fff3cd; padding: 15px; border-right: 4px solid #ff8c00; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🎵 תודה על פנייתך!</h2>
            </div>
            
            <div class='content'>
                <p>שלום " . htmlspecialchars($name) . ",</p>
                
                <p>תודה שפנית אלי! קיבלתי את פרטי האירוע שלך ואחזור אליך בהקדם האפשרי.</p>
                
                <div class='highlight'>
                    <strong>פרטי הקשר שלי:</strong><br>
                    📱 טלפון: <a href='tel:0522648094'>052-264-8094</a><br>
                    ✉️ אימייל: <a href='mailto:erezgdj@gmail.com'>erezgdj@gmail.com</a>
                </div>
                
                <p>אם יש לך שאלות נוספות או שברצונך לדבר איתי ישירות, אל תהסס לצלצל!</p>
                
                <p>מצפה לשמוע ממך,<br>
                <strong>ארז גולדשטיין</strong><br>
                DJ Erez Goldshtein</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $customer_headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: DJ Erez Goldshtein <erezgdj@gmail.com>',
        'Reply-To: erezgdj@gmail.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    mail($email, $customer_subject, $customer_message, implode("\r\n", $customer_headers));
}

// החזרת תשובה
if ($mail_sent) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'ההודעה נשלחה בהצלחה!'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'שגיאה בשליחת המייל. אנא נסה שוב.'
    ]);
}
?>
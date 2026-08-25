<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$locked = !empty($_SESSION['login_lockout_until']) && $_SESSION['login_lockout_until'] > time();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = getDb()->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = $user['id'];
        unset($_SESSION['login_attempts'], $_SESSION['login_lockout_until']);

        $update = getDb()->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
        $update->execute([$user['id']]);

        header('Location: index.php');
        exit;
    }

    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_lockout_until'] = time() + 300;
        $error = 'יותר מדי ניסיונות כושלים. נסה שוב בעוד 5 דקות.';
    } else {
        $error = 'שם משתמש או סיסמה שגויים';
    }
} elseif ($locked) {
    $error = 'יותר מדי ניסיונות כושלים. נסה שוב מאוחר יותר.';
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>כניסת מנהל | DJ Erez Goldshtein</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 0, 128, 0.3);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ff0080, #ff8c00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #ff8c00; }
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 0, 128, 0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #ff0080;
        }
        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ff0080, #ff8c00);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
        }
        .error {
            background: rgba(255, 0, 0, 0.15);
            border: 1px solid rgba(255, 0, 0, 0.4);
            color: #ff8080;
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>כניסת מנהל</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">שם משתמש</label>
                <input type="text" id="username" name="username" required autocomplete="username" <?= $locked ? 'disabled' : '' ?>>
            </div>
            <div class="form-group">
                <label for="password">סיסמה</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" <?= $locked ? 'disabled' : '' ?>>
            </div>
            <button type="submit" <?= $locked ? 'disabled' : '' ?>>התחבר</button>
        </form>
    </div>
</body>
</html>

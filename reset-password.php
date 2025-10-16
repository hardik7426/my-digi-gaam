<?php
require 'db.php';
$message = '';
$message_type = '';

if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'] || !isset($_SESSION['reset_phone'])) {
    // If OTP is not verified, send them away
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $message = "કૃપા કરીને બંને પાસવર્ડ ખાના ભરો.";
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "બંને પાસવર્ડ મેળ ખાતા નથી.";
        $message_type = 'error';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $phone_number = $_SESSION['reset_phone'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE phone_number = ?");
        $stmt->bind_param("ss", $hashed_password, $phone_number);
        
        if ($stmt->execute()) {
            unset($_SESSION['otp'], $_SESSION['reset_phone'], $_SESSION['otp_verified'], $_SESSION['otp_time']);
            $message = "તમારો પાસવર્ડ સફળતાપૂર્વક બદલાઈ ગયો છે. હવે તમે લોગીન કરી શકો છો.";
            $message_type = 'success';
        } else {
            $message = "પાસવર્ડ બદલવામાં ભૂલ આવી.";
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>નવો પાસવર્ડ સેટ કરો - માય ડિજી ગામ</title>
    <style>
        /* Copy all styles from login.php here, including success message styles */
        :root { --bg-image-path: url('assets/images/index.jpeg'); --primary-color: #3182ce; --secondary-color: #38b2ac; --text-color: #1a202c; --light-text: #718096; --error-color: #c53030; --error-bg: #fed7d7; --success-color: #2f855a; --success-bg: #c6f6d5; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; overflow: hidden; }
        .background-overlay { position: fixed; top: -5%; left: -5%; width: 110%; height: 110%; background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), var(--bg-image-path); background-size: cover; background-position: center; z-index: -1; animation: zoomAndPan 25s infinite alternate; }
        .auth-container { width: 100%; max-width: 420px; padding: 40px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 15px 40px rgba(0,0,0,0.2); animation: fadeIn 1s ease-out; }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h1 { font-size: 2.2rem; color: var(--text-color); font-weight: 700; }
        .auth-header i { color: var(--primary-color); }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--light-text); }
        .input-group input { width: 100%; padding: 14px 14px 14px 45px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; font-family: 'Noto Sans Gujarati', sans-serif; }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.3s; }
        button:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .message { text-align: center; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; }
        .message.error { color: var(--error-color); background-color: var(--error-bg); animation: shake 0.5s; }
        .message.success { color: var(--success-color); background-color: var(--success-bg); }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
        @keyframes zoomAndPan { from { transform: scale(1); } to { transform: scale(1.1); } }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 50% { transform: translateX(6px); } 75% { transform: translateX(-6px); } }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    <div class="auth-container">
        <div class="auth-header"><h1><i class="fa-solid fa-shield-halved"></i> નવો પાસવર્ડ</h1></div>
        
        <?php if(!empty($message)): ?>
            <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($message_type !== 'success'): ?>
        <form action="reset-password.php" method="post">
            <div class="input-group">
                <input type="password" name="password" placeholder="નવો પાસવર્ડ" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="નવો પાસવર્ડ કન્ફર્મ કરો" required>
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <button type="submit">પાસવર્ડ બદલો</button>
        </form>
        <?php else: ?>
            <div class="login-link">
                <a href="login.php">લોગીન પેજ પર પાછા જાઓ</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
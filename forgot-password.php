<?php
require 'db.php';
$message = '';
$message_type = 'error';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = trim($_POST['phone_number']);

    if(empty($phone_number)) {
        $message = "કૃપા કરીને તમારો મોબાઈલ નંબર દાખલ કરો.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
        $stmt->bind_param("s", $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['reset_phone'] = $phone_number;
            $_SESSION['otp_time'] = time();

            // !!! --- OTP SIMULATION --- !!!
            // In a real application, you would replace this alert with your SMS gateway API code.
            echo "<script>alert('Your OTP is: " . $otp . "'); window.location.href='verify-otp.php';</script>";
            exit();
        } else {
            $message = "આ મોબાઈલ નંબર સાથે કોઈ એકાઉન્ટ જોડાયેલું નથી.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>પાસવર્ડ ભૂલી ગયા - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg-image-path: url('assets/images/index.jpeg');
            --primary-color: #3182ce;
            --secondary-color: #38b2ac;
            --text-color: #1a202c;
            --light-text: #718096;
            --error-color: #c53030;
            --error-bg: #fed7d7;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; overflow: hidden; }
        .background-overlay { position: fixed; top: -5%; left: -5%; width: 110%; height: 110%; background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), var(--bg-image-path); background-size: cover; background-position: center; z-index: -1; animation: zoomAndPan 25s infinite alternate; }
        .auth-container { width: 100%; max-width: 420px; padding: 40px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 15px 40px rgba(0,0,0,0.2); animation: fadeIn 1s ease-out; }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h1 { font-size: 2.2rem; color: var(--text-color); font-weight: 700; }
        .auth-header i { color: var(--primary-color); }
        .auth-header p { color: var(--light-text); margin-top: 5px; }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--light-text); }
        .input-group input { width: 100%; padding: 14px 14px 14px 45px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; font-family: 'Noto Sans Gujarati', sans-serif; }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.3s; }
        button:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .auth-switch { text-align: center; margin-top: 20px; color: var(--light-text); }
        .auth-switch a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
        .message.error { text-align: center; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; color: var(--error-color); background-color: var(--error-bg); animation: shake 0.5s; }
        @keyframes zoomAndPan { from { transform: scale(1); } to { transform: scale(1.1); } }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 50% { transform: translateX(6px); } 75% { transform: translateX(-6px); } }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    <div class="auth-container">
        <div class="auth-header">
            <h1><i class="fa-solid fa-key"></i> પાસવર્ડ રીસેટ</h1>
            <p>OTP મેળવવા તમારો નંબર દાખલ કરો</p>
        </div>
        
        <?php if(!empty($message)): ?>
            <p class="message error"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form action="forgot-password.php" method="post">
            <div class="input-group">
                <input type="text" name="phone_number" placeholder="મોબાઈલ નંબર" required pattern="[0-9]{10}" title="કૃપા કરીને 10-અંકનો મોબાઈલ નંબર દાખલ કરો">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <button type="submit">OTP મોકલો</button>
        </form>
        <p class="auth-switch">યાદ આવ્યું? <a href="login.php">લોગીન કરો</a></p>
    </div>
</body>
</html>
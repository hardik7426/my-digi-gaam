<?php
require 'db.php';
$message = '';
$message_type = 'error';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone_number = trim($_POST['phone_number']);

    if (empty($username) || empty($password) || empty($phone_number)) {
        $message = "કૃપા કરીને બધા જરૂરી ખાના ભરો.";
    } elseif ($password !== $confirm_password) {
        $message = "પાસવર્ડ અને કન્ફર્મ પાસવર્ડ મેળ ખાતા નથી.";
    } else {
        // Check if username or phone number already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone_number = ?");
        $stmt->bind_param("ss", $username, $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "આ વપરાશકર્તાનામ અથવા મોબાઈલ નંબર પહેલેથી જ રજીસ્ટર્ડ છે.";
        } else {
            // All good, generate OTP and store data in session
            $otp = rand(100000, 999999);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $_SESSION['signup_data'] = [
                'username' => $username,
                'password' => $hashed_password,
                'phone_number' => $phone_number,
                'otp' => $otp
            ];
            
            // !!! --- OTP SIMULATION --- !!!
            // In a real app, you would send the OTP via SMS here using your gateway's API.
            // For now, we'll show it in an alert for testing.
            echo "<script>
                alert('તમારો OTP છે: " . $otp . "');
                window.location.href = 'verify-signup.php';
            </script>";
            exit();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>સાઇન અપ - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --bg-image-path: url('assets/images/index.jpeg'); --primary-color: #3182ce; --secondary-color: #38b2ac; --text-color: #1a202c; --light-text: #718096; --error-color: #c53030; --error-bg: #fed7d7; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; overflow: hidden; }
        .background-overlay { position: fixed; top: -5%; left: -5%; width: 110%; height: 110%; background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), var(--bg-image-path); background-size: cover; background-position: center; z-index: -1; animation: zoomAndPan 25s infinite alternate; }
        .auth-container { width: 100%; max-width: 420px; padding: 40px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 15px 40px rgba(0,0,0,0.2); animation: fadeIn 1s ease-out; }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h1 { font-size: 2.5rem; color: var(--text-color); font-weight: 700; }
        .auth-header i { color: var(--primary-color); }
        .auth-header p { color: var(--light-text); }
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--light-text); transition: color 0.3s; }
        .input-group input { width: 100%; padding: 14px 14px 14px 45px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; font-family: 'Noto Sans Gujarati', sans-serif; transition: border-color 0.3s, box-shadow 0.3s; }
        .input-group input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2); }
        .input-group input:focus + i { color: var(--primary-color); }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.3s; }
        button:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .auth-switch { text-align: center; margin-top: 20px; color: var(--light-text); }
        .auth-switch a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
        .message { text-align: center; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; }
        .message.error { color: var(--error-color); background-color: var(--error-bg); animation: shake 0.5s; }
        @keyframes zoomAndPan { from { transform: scale(1); } to { transform: scale(1.1); } }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 50% { transform: translateX(6px); } 75% { transform: translateX(-6px); } }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    <div class="auth-container">
        <div class="auth-header">
            <h1><i class="fa-solid fa-user-plus"></i> એકાઉન્ટ બનાવો</h1>
            <p>ડિજિટલ ગામ પરિવારમાં જોડાઓ</p>
        </div>
        
        <?php if(!empty($message)): ?>
            <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form id="signupForm" action="signup.php" method="post">
            <div class="input-group">
                <input type="text" name="username" placeholder="વપરાશકર્તાનામ" required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="input-group">
                <input type="text" name="phone_number" placeholder="મોબાઈલ નંબર" required pattern="[0-9]{10}" title="કૃપા કરીને 10-અંકનો મોબાઈલ નંબર દાખલ કરો">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="પાસવર્ડ" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="પાસવર્ડ કન્ફર્મ કરો" required>
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <button type="submit">OTP મેળવો</button>
        </form>
        <p class="auth-switch">પહેલેથી જ એકાઉન્ટ છે? <a href="login.php">અહીં લોગીન કરો</a></p>
    </div>
</body>
</html>
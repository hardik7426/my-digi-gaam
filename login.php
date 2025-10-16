<?php
require 'db.php';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST['username'])) || empty(trim($_POST['password']))) {
        $message = "કૃપા કરીને વપરાશકર્તાનામ અને પાસવર્ડ દાખલ કરો.";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $fixed_admin_user = 'admin';
        $fixed_admin_pass = 'password123';
        if ($username === $fixed_admin_user && $password === $fixed_admin_pass) {
            $_SESSION['user_id'] = 0;
            $_SESSION['username'] = $fixed_admin_user;
            $_SESSION['role'] = 'admin';
            header("Location: admin/index.php");
            exit();
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: " . ($user['role'] == 'admin' ? 'admin/index.php' : 'index.php'));
                    exit();
                } else { $message = "તમે દાખલ કરેલો પાસવર્ડ ખોટો છે."; }
            } else { $message = "આ નામનો કોઈ વપરાશકર્તા મળ્યો નથી."; }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>લોગીન - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            /* === ફક્ત અહીં ઇમેજનું નામ બદલો === */
            --bg-image-path: url('assets/images/index.jpeg');
            
            --primary-color: #3182ce;
            --secondary-color: #38b2ac;
            --text-color: #1a202c;
            --light-text: #718096;
            --error-color: #c53030;
            --error-bg: #fed7d7;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Noto Sans Gujarati', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            overflow: hidden; /* Prevents scrollbars from background animation */
        }
        .background-overlay {
            position: fixed;
            top: -5%; left: -5%;
            width: 110%; height: 110%;
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), var(--bg-image-path);
            background-size: cover;
            background-position: center;
            z-index: -1;
            animation: zoomAndPan 25s infinite alternate;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease-out;
        }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h1 { font-size: 2.5rem; color: var(--text-color); font-weight: 700; }
        .auth-header i { color: var(--primary-color); }
        .auth-header p { color: var(--light-text); }
        
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--light-text); transition: color 0.3s; }
        .input-group input {
            width: 100%;
            padding: 14px 14px 14px 45px; /* Space for icon */
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Noto Sans Gujarati', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
        }
        .input-group input:focus + i { color: var(--primary-color); }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            position: relative;
        }
        button:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        button.loading { background: #9fadba; cursor: not-allowed; }
        button .spinner { display: none; }
        button.loading .button-text { visibility: hidden; }
        button.loading .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 1s linear infinite; position: absolute; top: 50%; left: 50%; margin-top: -10px; margin-left: -10px; }

        .auth-switch { text-align: center; margin-top: 20px; color: var(--light-text); }
        .auth-switch a { color: var(--primary-color); font-weight: 600; text-decoration: none; }
        
        .message.error { text-align: center; padding: 12px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; color: var(--error-color); background-color: var(--error-bg); animation: shake 0.5s; }
        
        /* Animations */
        @keyframes zoomAndPan { from { transform: scale(1); } to { transform: scale(1.1); } }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-6px); }
        }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    <div class="auth-container">
        <div class="auth-header">
            <h1><i class="fa-solid fa-mobile-screen-button"></i> માય ડિજી ગામ</h1>
            <p>તમારું સ્વાગત છે!</p>
        </div>
        
        <?php if(!empty($message)): ?>
            <p class="message error"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form id="loginForm" action="login.php" method="post">
            <div class="input-group">
                <input type="text" name="username" placeholder="વપરાશકર્તાનામ" required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="પાસવર્ડ" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <button type="submit">
                <span class="button-text">લોગીન કરો</span>
                <span class="spinner"></span>
            </button>
        </form>
        <p class="auth-switch">એકાઉન્ટ નથી? <a href="signup.php">નવું બનાવો</a></p>
    </div>

    <script>
        // Button loading animation on form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            
            // Basic client-side validation
            const username = this.querySelector('input[name="username"]').value;
            const password = this.querySelector('input[name="password"]').value;

            if (username.trim() === '' || password.trim() === '') {
                // If fields are empty, don't show loader, let HTML 'required' handle it
                return;
            }

            button.classList.add('loading');
            button.disabled = true;
        });
    </script>
</body>
</html>
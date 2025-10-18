<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}
$user_id = $_SESSION['user_id'];

// "Delete All" Logic
if (isset($_GET['delete_all'])) {
    $conn->query("DELETE FROM messenger WHERE user_id = $user_id");
    // Also delete appointments
    $conn->query("DELETE FROM appointments WHERE user_id = $user_id");
    header('Location: messenger.php');
    exit();
}

// Mark all user's messages as read
$conn->query("UPDATE messenger SET is_read_by_user = 1 WHERE user_id = $user_id AND sender_type = 'admin'");
$_SESSION['unread_messages'] = 0; // Reset notification count in session

// Fetch all appointments for this user to group messages
$appointments = $conn->query("SELECT a.*, d.name as doctor_name 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = $user_id 
    ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>મેસેન્જર - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: #ffffff; --card-bg: rgba(255, 255, 255, 0.92); --primary-text: #1a202c; --secondary-text: #718096; --accent-color-1: #3182ce; --accent-color-2: #38b2ac; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .content-container { max-width: 900px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .delete-all-btn { display: block; width: 100%; text-align: center; padding: 12px; background-color: #e53e3e; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 20px; }
        .appointment-thread { background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; margin-bottom: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .thread-header { padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.1); }
        .thread-header h2 { font-size: 1.3rem; }
        .thread-header p { color: var(--secondary-text); }
        .chat-history { padding: 20px; max-height: 400px; overflow-y: auto; }
        .message { margin-bottom: 15px; }
        .message.admin { text-align: left; }
        .message.user { text-align: right; }
        .message .msg-bubble { display: inline-block; padding: 10px 15px; border-radius: 20px; max-width: 80%; }
        .message.admin .msg-bubble { background-color: #e2e8f0; }
        .message.user .msg-bubble { background-color: var(--accent-color-1); color: white; }
        .message .msg-time { font-size: 0.8rem; color: var(--secondary-text); margin-top: 5px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-bell"></i> મેસેન્જર</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <a href="messenger.php?delete_all=1" class="delete-all-btn" onclick="return confirm('શું તમે ચોક્કસ બધી હિસ્ટ્રી ડિલીટ કરવા માંગો છો?');">બધી હિસ્ટ્રી ડિલીટ કરો</a>

            <?php while($app = $appointments->fetch_assoc()): ?>
                <div class="appointment-thread">
                    <div class="thread-header">
                        <h2>ડો. <?php echo htmlspecialchars($app['doctor_name']); ?></h2>
                        <p>રિક્વેસ્ટ તારીખ: <?php echo date('d M Y', strtotime($app['booking_date'])); ?> | સ્થિતિ: <?php echo htmlspecialchars($app['status']); ?></p>
                    </div>
                    <div class="chat-history">
                        <?php
                        $app_id = $app['id'];
                        $messages = $conn->query("SELECT * FROM messenger WHERE appointment_id = $app_id ORDER BY timestamp ASC");
                        while($msg = $messages->fetch_assoc()):
                        ?>
                        <div class="message <?php echo $msg['sender_type']; ?>">
                            <div class="msg-bubble"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                            <div class="msg-time"><?php echo date('d M, h:i A', strtotime($msg['timestamp'])); ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php if($appointments->num_rows === 0): ?>
                <p style="text-align:center; color: white; font-size: 1.2rem; background: var(--card-bg); padding: 30px; border-radius: 12px;">તમારી પાસે હજી કોઈ મેસેજ નથી.</p>
            <?php endif; ?>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>
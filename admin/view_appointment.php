<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_appointments.php'); exit();
}

$app_id = (int)$_GET['id'];
$message_status = '';

// Handle Admin Response
if (isset($_POST['send_message'])) {
    $user_id = $_POST['user_id'];
    $message_text = trim($_POST['message']);
    $new_status = $_POST['status'];

    // 1. Update appointment status
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $app_id);
    $stmt->execute();
    $stmt->close();

    // 2. Send message to user only if message is not empty
    if (!empty($message_text)) {
        $stmt = $conn->prepare("INSERT INTO messenger (appointment_id, user_id, sender_type, message, is_read_by_user) VALUES (?, ?, 'admin', ?, 0)");
        $stmt->bind_param("iis", $app_id, $user_id, $message_text);
        $stmt->execute();
        $stmt->close();
    }
    
    $message_status = "જવાબ મોકલી દેવામાં આવ્યો છે.";
}

// Fetch appointment details
$stmt = $conn->prepare("SELECT a.*, d.name as doctor_name, u.username as user_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.id = ?");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch message history
$messages = $conn->query("SELECT * FROM messenger WHERE appointment_id = $app_id ORDER BY timestamp ASC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>એપોઇન્ટમેન્ટ વિગત</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: rgba(255, 255, 255, 0.8); --card-bg: rgba(255, 255, 255, 0.7); --primary-text: #1a202c; --secondary-text: #4a5568; --accent-color-1: #3182ce; --danger-color: #e53e3e; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.6)), url('../assets/images/index.jpeg'); background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); backdrop-filter: blur(10px); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--primary-text); }
        .main-header i { margin-right: 12px; color: var(--accent-color-1); }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .admin-container { max-width: 900px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .detail-section, .message-section { background: var(--card-bg); backdrop-filter: blur(10px); border-radius: 12px; padding: 30px 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(255, 255, 255, 0.5); margin-bottom: 30px; }
        h3 { margin-bottom: 20px; font-size: 1.4rem; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .info-item { color: var(--secondary-text); line-height: 1.6; }
        .info-item strong { color: var(--primary-text); font-weight: 600; }
        textarea, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; }
        button[type="submit"] { padding: 12px 30px; background-color: var(--accent-color-1); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 600; }
        .chat-history { max-height: 300px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 20px; background: rgba(255,255,255,0.5); }
        .message { margin-bottom: 10px; }
        .message.admin { text-align: right; }
        .message .msg-bubble { display: inline-block; padding: 10px 15px; border-radius: 20px; max-width: 70%; }
        .message.user .msg-bubble { background-color: #e2e8f0; }
        .message.admin .msg-bubble { background-color: var(--accent-color-1); color: white; }
        .message .msg-time { font-size: 0.8rem; color: var(--secondary-text); margin-top: 5px; }
        .message.admin .msg-time { text-align: right; }
        .message.user .msg-time { text-align: left; }
        .message.success { color: #2f855a; background-color: #c6f6d5; padding: 15px; margin-top: 20px; border-radius: 8px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-calendar-check"></i> એપોઇન્ટમેન્ટ વિગત</h1>
        <a href="manage_appointments.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> બધી રિક્વેસ્ટ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="admin-container">
            <div class="detail-section">
                <h3>દર્દીની વિગતો</h3>
                <div class="info-grid">
                    <div class="info-item"><strong>દર્દી:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></div>
                    <div class="info-item"><strong>ડોક્ટર:</strong> <?php echo htmlspecialchars($appointment['doctor_name']); ?></div>
                    <div class="info-item"><strong>ઉંમર:</strong> <?php echo htmlspecialchars($appointment['age']); ?></div>
                    <div class="info-item"><strong>જાતિ:</strong> <?php echo htmlspecialchars($appointment['gender']); ?></div>
                    <div class="info-item"><strong>સંપર્ક:</strong> <?php echo htmlspecialchars($appointment['contact_number']); ?></div>
                    <div class="info-item"><strong>સ્થળ:</strong> <?php echo htmlspecialchars($appointment['location']); ?></div>
                    <div class="info-item"><strong>DOB:</strong> <?php echo date('d-m-Y', strtotime($appointment['dob'])); ?></div>
                    <div class="info-item"><strong>બુકિંગ તારીખ:</strong> <?php echo date('d-m-Y', strtotime($appointment['booking_date'])); ?></div>
                </div>
                <hr style="margin: 20px 0; border: 1px solid #e2e8f0;">
                <div class="info-item"><strong>સમસ્યા:</strong><br><?php echo nl2br(htmlspecialchars($appointment['problem_description'])); ?></div>
            </div>
            
            <div class="message-section">
                <h3>મેસેજ હિસ્ટ્રી</h3>
                <div class="chat-history">
                    <?php while($msg = $messages->fetch_assoc()): ?>
                        <div class="message <?php echo $msg['sender_type']; ?>">
                            <div class="msg-bubble"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                            <div class="msg-time"><?php echo date('d M, h:i A', strtotime($msg['timestamp'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <h3>જવાબ આપો / સ્ટેટસ બદલો</h3>
                <form action="view_appointment.php?id=<?php echo $app_id; ?>" method="post">
                    <input type="hidden" name="user_id" value="<?php echo $appointment['user_id']; ?>">
                    
                    <label for="status">સ્ટેટસ અપડેટ કરો</label>
                    <select id="status" name="status">
                        <option value="Pending" <?php if($appointment['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Approved" <?php if($appointment['status'] == 'Approved') echo 'selected'; ?>>Approve</option>
                        <option value="Rejected" <?php if($appointment['status'] == 'Rejected') echo 'selected'; ?>>Reject</option>
                    </select>
                    
                    <label for="message">નવો મેસેજ મોકલો (દા.ત. 'તમારી એપોઇન્ટમેન્ટ બુક થઈ ગઈ છે')</label>
                    <textarea id="message" name="message" rows="4" placeholder="યુઝરને મેસેજ લખો..."></textarea>
                    
                    <button type="submit" name="send_message">મેસેજ મોકલો</button>
                </form>
                <?php if ($message_status) echo "<p class='message success' style='margin-top: 15px;'>$message_status</p>"; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>
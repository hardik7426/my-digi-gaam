<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_digi_gaam";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// --- નવો નોટિફિકેશન કોડ અહીંથી શરૂ થાય છે ---
if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin') {
    $current_user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM messenger WHERE user_id = ? AND is_read_by_user = 0 AND sender_type = 'admin'");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $notif_data = $stmt->get_result()->fetch_assoc();
    $_SESSION['unread_messages'] = $notif_data['unread_count'] ?? 0;
    $stmt->close();
} else {
    $_SESSION['unread_messages'] = 0;
}
// --- નવો નોટિફિકેશન કોડ અહીં પૂરો થાય છે ---
?>
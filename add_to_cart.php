<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if product id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: stationery_store.php');
    exit();
}

$product_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];
$quantity = 1;
$order_status = 'In Cart';


// === આ રહ્યો કાયમી ઉકેલ (FIX) ===
// 1. ડેટાબેઝમાં INSERT કરતા પહેલા, ચકાસો કે આ user_id 'users' ટેબલમાં અસ્તિત્વમાં છે કે નહીં.
$check_user_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$check_user_stmt->bind_param("i", $user_id);
$check_user_stmt->execute();
$user_result = $check_user_stmt->get_result();

if ($user_result->num_rows == 0) {
    // 2. જો યુઝર ડેટાબેઝમાં ન મળે (એટલે કે સેશન જૂનું છે):
    // સેશનનો નાશ કરો (Log out)
    session_unset();
    session_destroy();
    
    // 3. લોગઇન પેજ પર પાછા મોકલો.
    // એરર બતાવવાને બદલે, અમે તેમને ફરીથી લોગઇન કરવા કહીશું.
    header('Location: login.php?message=session_expired');
    exit();
}
$check_user_stmt->close();
// === ઉકેલ (FIX) અહીં પૂરો થયો ===


// હવે આ કોડ 100% સુરક્ષિત છે, કારણ કે આપણને ખાતરી છે કે user_id માન્ય છે.
$stmt = $conn->prepare("INSERT INTO stationery_orders (user_id, product_id, quantity, order_status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $user_id, $product_id, $quantity, $order_status);

if ($stmt->execute()) {
    // Redirect to the cart page after successful insertion
    header('Location: view_cart.php');
} else {
    // Handle error
    echo "Error adding to cart. Please try again.";
}

$stmt->close();
$conn->close();
?>
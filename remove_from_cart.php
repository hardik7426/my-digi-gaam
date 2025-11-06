<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if order id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: view_cart.php');
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

// Delete the item from the cart, ensuring it belongs to the logged-in user
$stmt = $conn->prepare("DELETE FROM stationery_orders WHERE id = ? AND user_id = ? AND order_status = 'In Cart'");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect back to the cart
header('Location: view_cart.php');
exit();
?>
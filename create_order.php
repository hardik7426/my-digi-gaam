<?php
// create_order.php
require 'db.php';
header('Content-Type: application/json');

// Replace with your Razorpay credentials
define('RAZORPAY_KEY_ID', 'rzp_test_bOO7RkRAo6QmEo');
define('RAZORPAY_KEY_SECRET', 'e8OQPew9CiwbUc96IGR75d1c');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = (int)$_SESSION['user_id'];

// Calculate total from current 'In Cart' orders for user (server-side authoritative)
$stmt = $conn->prepare("SELECT SUM(p.price) as total, GROUP_CONCAT(o.id) as order_ids FROM stationery_orders o JOIN stationery_products p ON o.product_id = p.id WHERE o.user_id = ? AND o.order_status = 'In Cart'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total = (float) ($res['total'] ?? 0);
$order_ids_csv = $res['order_ids'] ?? '';

if ($total <= 0 || !$order_ids_csv) {
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

// Razorpay expects amount in paise (INR * 100)
$amount_paise = (int) round($total * 100);

$payload = [
    'amount' => $amount_paise,
    'currency' => 'INR',
    'receipt' => 'order_rcpt_' . time() . '_' . $user_id,
    'payment_capture' => 1 // auto-capture
];

// create order via Razorpay API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

$data = json_decode($response, true);
if ($http_status !== 200 && $http_status !== 201) {
    echo json_encode(['error' => 'Razorpay API error', 'details' => $data]);
    exit;
}

$razorpay_order_id = $data['id'] ?? null;
if (!$razorpay_order_id) {
    echo json_encode(['error' => 'Invalid order response']);
    exit;
}

// Save razorpay_order_id to all stationery_orders rows for this user that are In Cart
$order_ids = explode(',', $order_ids_csv);
$placeholders = implode(',', array_fill(0, count($order_ids), '?'));
$types = str_repeat('i', count($order_ids));
$params = $order_ids;
array_unshift($params, $user_id); // but we don't have user_id in WHERE clause for IN list; we'll use prepared statement differently

// Simpler update: update by user_id and In Cart (no need to match ids)
$update_stmt = $conn->prepare("UPDATE stationery_orders SET razorpay_order_id = ? WHERE user_id = ? AND order_status = 'In Cart'");
$update_stmt->bind_param("si", $razorpay_order_id, $user_id);
$update_stmt->execute();
$update_stmt->close();

echo json_encode([
    'key_id' => RAZORPAY_KEY_ID,
    'key_id' => RAZORPAY_KEY_ID,
    'key_id' => RAZORPAY_KEY_ID,
    'key_id' => RAZORPAY_KEY_ID,
    'key_id' => RAZORPAY_KEY_ID,
    'razorpay_order_id' => $razorpay_order_id,
    'amount' => $amount_paise,
    'currency' => $data['currency'] ?? 'INR'
]);

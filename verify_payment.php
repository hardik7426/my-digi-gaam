
<?php
// verify_payment.php
require 'db.php';
header('Content-Type: application/json');

define('RAZORPAY_KEY_ID', 'YOUR_RAZORPAY_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'YOUR_RAZORPAY_KEY_SECRET');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No input']);
    exit;
}

$razorpay_order_id = $input['razorpay_order_id'] ?? null;
$razorpay_payment_id = $input['razorpay_payment_id'] ?? null;
$razorpay_signature = $input['razorpay_signature'] ?? null;

if (!$razorpay_order_id || !$razorpay_payment_id || !$razorpay_signature) {
    echo json_encode(['success' => false, 'error' => 'Missing payment data']);
    exit;
}

// Create signature to verify
$generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, RAZORPAY_KEY_SECRET);

if (hash_equals($generated_signature, $razorpay_signature)) {
    // signature valid -> mark related orders as Paid
    // update rows where razorpay_order_id matches
    $stmt = $conn->prepare("UPDATE stationery_orders SET payment_id = ?, payment_status = 'Paid', order_status = 'Completed' WHERE razorpay_order_id = ? AND order_status = 'In Cart'");
    $stmt->bind_param("ss", $razorpay_payment_id, $razorpay_order_id);
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    } else {
        $err = $conn->error;
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'DB update failed: ' . $err]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid signature']);
    exit;
}

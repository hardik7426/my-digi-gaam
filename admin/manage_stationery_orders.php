<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';

// Update Status Logic
if (isset($_GET['complete'])) {
    $order_id = (int)$_GET['complete'];
    $stmt = $conn->prepare("UPDATE stationery_orders SET order_status = 'Completed' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) $message = "ઓર્ડર 'Completed' તરીકે માર્ક થયો.";
    $stmt->close();
}

// Delete Logic
if (isset($_GET['delete'])) {
    $order_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM stationery_orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) $message = "ઓર્ડર દૂર કરાયો.";
    $stmt->close();
}

// Fetch all orders, joining with user and product tables
// *** અહીં ફેરફાર કરેલ છે: u.name ને બદલે u.username ***
$orders = $conn->query("SELECT o.id, o.order_status, o.created_at, u.username as user_name, p.name as product_name, p.price
                       FROM stationery_orders o
                       JOIN users u ON o.user_id = u.id
                       JOIN stationery_products p ON o.product_id = p.id
                       ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <title>સ્ટેશનરી ઓર્ડર મેનેજ કરો</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: rgba(255, 255, 255, 0.8); --card-bg: rgba(255, 255, 255, 0.7); --primary-text: #1a202c; --secondary-text: #4a5568; --accent-color-1: #3182ce; --danger-color: #e53e3e; --success-color: #38a169; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.6)), url('../assets/images/index.jpeg'); background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); backdrop-filter: blur(10px); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--primary-text); }
        .main-header i { margin-right: 12px; color: var(--accent-color-1); }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .admin-container { max-width: 1300px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .table-section { background: var(--card-bg); backdrop-filter: blur(10px); border-radius: 12px; padding: 30px 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(255, 255, 255, 0.5); margin-bottom: 30px; overflow-x: auto; }
        .table-section h3 { margin-bottom: 20px; font-size: 1.4rem; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); text-align: left; }
        td a { color: var(--accent-color-1); font-weight: 500; text-decoration: none; }
        .action-delete { color: var(--danger-color); }
        .action-complete { color: var(--success-color); }
        .status-incart { color: #dd6b20; font-weight: 600; }
        .status-completed { color: var(--success-color); font-weight: 600; }
        .message.success { color: #2f855a; background-color: #c6f6d5; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-shopping-cart"></i> સ્ટેશનરી ઓર્ડર મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ</a>
    </header>
    <main>
        <div class="admin-container">
            <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            <div class="table-section">
                <h3>બધા ઓર્ડર</h3>
                <table>
                    <thead>
                        <tr>
                            <th>યુઝર</th>
                            <th>પ્રોડક્ટ</th>
                            <th>કિંમત</th>
                            <th>સ્ટેટસ</th>
                            <th>તારીખ</th>
                            <th>ક્રિયાઓ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td>₹<?php echo htmlspecialchars($row['price']); ?></td>
                            <td>
                                <?php if($row['order_status'] == 'In Cart'): ?>
                                    <span class="status-incart">કાર્ટમાં છે</span>
                                <?php else: ?>
                                    <span class="status-completed">પૂર્ણ</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if($row['order_status'] == 'In Cart'): ?>
                                    <a href="manage_stationery_orders.php?complete=<?php echo $row['id']; ?>" class="action-complete" onclick="return confirm('શું તમે આ ઓર્ડરને પૂર્ણ તરીકે માર્ક કરવા માંગો છો?');">પૂર્ણ કરો</a> |
                                <?php endif; ?>
                                <a href="manage_stationery_orders.php?delete=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>
</body>
</html>
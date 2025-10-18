<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}
$appointments = $conn->query("SELECT a.*, d.name as doctor_name, u.username as user_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN doctors d ON a.doctor_id = d.id 
    ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>એપોઇન્ટમેન્ટ મેનેજ કરો - એડમિન પેનલ</title>
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
        .admin-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .table-section { background: var(--card-bg); backdrop-filter: blur(10px); border-radius: 12px; padding: 30px 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(255, 255, 255, 0.5); margin-bottom: 30px; }
        .table-section h3 { margin-bottom: 20px; font-size: 1.4rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); text-align: left; }
        thead th { font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary-text); }
        td a { color: var(--accent-color-1); font-weight: 600; text-decoration: none; }
        .status-Pending { color: #dd6b20; font-weight: 600; }
        .status-Approved { color: #38a169; font-weight: 600; }
        .status-Rejected { color: #e53e3e; font-weight: 600; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-calendar-check"></i> એપોઇન્ટમેન્ટ રિક્વેસ્ટ્સ</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ</a>
    </header>
    <main>
        <div class="admin-container">
            <div class="table-section">
                <h3>બધી એપોઇન્ટમેન્ટ્સ</h3>
                <table>
                    <thead>
                        <tr><th>દર્દીનું નામ</th><th>ડોક્ટર</th><th>બુકિંગ તારીખ</th><th>સ્થિતિ</th><th>જુઓ / જવાબ આપો</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['booking_date'])); ?></td>
                            <td><span class="status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td><a href="view_appointment.php?id=<?php echo $row['id']; ?>">વિગતો જુઓ</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>
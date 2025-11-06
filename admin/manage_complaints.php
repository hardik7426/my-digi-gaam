<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';
// Update status logic
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE complaints SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) $message = "સ્ટેટસ અપડેટ થયું.";
    $stmt->close();
}
// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM complaints WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "ફરીયાદ દૂર કરાઈ.";
    $stmt->close();
}
// Fetch all data
$complaints = $conn->query("SELECT * FROM complaints ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ફરીયાદો મેનેજ કરો - એડમિન પેનલ</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        :root {
            --header-bg: rgba(255, 255, 255, 0.8);
            --card-bg: rgba(255, 255, 255, 0.7);
            --primary-text: #1a202c;
            --secondary-text: #4a5568;
            --accent-color-1: #3182ce;
            --danger-color: #e53e3e;
            --status-open-bg: #fffaf0;
            --status-open-text: #dd6b20;
            --status-closed-bg: #f0fff4;
            --status-closed-text: #38a169;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Noto Sans Gujarati', sans-serif;
            color: var(--primary-text);
            background-image: linear-gradient(rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.6)), url('../assets/images/index.jpeg');
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main { flex-grow: 1; }

        .main-header {
            background-color: var(--header-bg);
            backdrop-filter: blur(10px);
            padding: 1rem 2.5rem;
            box-shadow: 0 4px 6px -1px var(--shadow-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky; top: 0; z-index: 1000;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--primary-text); }
        .main-header i { margin-right: 12px; color: var(--accent-color-1); }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .main-header a.back-link:hover { color: var(--accent-color-1); }

        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }

        /* Glass Table Section */
        .table-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 30px 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 30px;
        }
        .table-section h3 { margin-bottom: 20px; font-weight: 600; font-size: 1.4rem; color: var(--primary-text); }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); text-align: left; vertical-align: middle; }
        thead th { font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary-text); }
        
        .status-select { padding: 8px 12px; border-radius: 20px; border: 1px solid transparent; font-weight: 600; font-family: 'Noto Sans Gujarati', sans-serif; appearance: none; -webkit-appearance: none; cursor: pointer; }
        .status-open { background-color: var(--status-open-bg); color: var(--status-open-text); border-color: var(--status-open-text); }
        .status-closed { background-color: var(--status-closed-bg); color: var(--status-closed-text); border-color: var(--status-closed-text); }
        
        .delete-action { color: var(--danger-color); font-weight: 500; text-decoration: none; transition: color 0.3s; }
        .delete-action:hover { color: #9b2c2c; }
        .delete-action i { margin-right: 5px; }

        .message { text-align: center; padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; }
        .message.success { color: #2f855a; background-color: #c6f6d5; }
        
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; font-weight: 500; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-bullhorn"></i> ફરીયાદો મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="admin-container">
            <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            <div class="table-section">
                <h3>બધી ફરીયાદો</h3>
                <table>
                    <thead>
                        <tr><th>નામ</th><th>શેરીની વિગત</th><th>મોબાઈલ</th><th>તારીખ</th><th>સ્ટેટસ</th><th>ક્રિયાઓ</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $complaints->fetch_assoc()): ?>
                            <?php
                                $status_class = ($row['status'] === 'બંધ') ? 'status-closed' : 'status-open';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['complainant_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['street_details']); ?></td>
                                <td><?php echo htmlspecialchars($row['mobile_number']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <form action="manage_complaints.php" method="post" class="status-form">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <select name="status" class="status-select <?php echo $status_class; ?>" onchange="this.form.submit()">
                                            <option value="ચાલુ" <?php if($row['status'] == 'ચાલુ') echo 'selected'; ?>>ચાલુ</option>
                                            <option value="બંધ" <?php if($row['status'] == 'બંધ') echo 'selected'; ?>>બંધ</option>
                                        </select>
                                        <noscript><button type="submit" name="update_status">અપડેટ</button></noscript>
                                    </form>
                                </td>
                                <td>
                                    <a href="manage_complaints.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
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
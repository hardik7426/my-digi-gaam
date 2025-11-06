<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';
// Add/Update Logic (no changes)
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $complaint_id = $_POST['complaint_id'];
    $assigned_to = $_POST['assigned_to'];
    $status = $_POST['status'];

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO electricity_issues (complaint_id, assigned_to, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $complaint_id, $assigned_to, $status);
        if ($stmt->execute()) $message = "નવો રેકોર્ડ ઉમેરાયો.";
    } else {
        $stmt = $conn->prepare("UPDATE electricity_issues SET complaint_id=?, assigned_to=?, status=? WHERE id=?");
        $stmt->bind_param("sssi", $complaint_id, $assigned_to, $status, $id);
        if ($stmt->execute()) $message = "રેકોર્ડ અપડેટ થયો.";
    }
    $stmt->close();
}
// Delete Logic (no changes)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM electricity_issues WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "રેકોર્ડ દૂર કરાયો.";
    $stmt->close();
}
// Fetch for editing (no changes)
$edit_issue = ['id' => '', 'complaint_id' => '', 'assigned_to' => '', 'status' => 'ચાલુ'];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM electricity_issues WHERE id = $id");
    $edit_issue = $result->fetch_assoc();
}

// --- START: Search Logic ---
$search_term = '';
if (isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
}

$sql = "SELECT * FROM electricity_issues";
if (!empty($search_term)) {
    $search_param = "%" . $search_term . "%";
    $sql .= " WHERE complaint_id LIKE ? OR assigned_to LIKE ?";
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($search_term)) {
    $stmt->bind_param("ss", $search_param, $search_param);
}

$stmt->execute();
$issues = $stmt->get_result();
// --- END: Search Logic ---
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>લાઈટ મેનેજ કરો - એડમિન પેનલ</title>
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
        .admin-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .form-section, .table-section { background: var(--card-bg); backdrop-filter: blur(10px); border-radius: 12px; padding: 30px 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(255, 255, 255, 0.5); margin-bottom: 30px; }
        .form-section h3, .table-section h3 { margin-bottom: 20px; font-size: 1.4rem; }
        input[type="text"], select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; }
        button[type="submit"] { padding: 12px 30px; background-color: var(--accent-color-1); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); text-align: left; }
        td a { color: var(--accent-color-1); font-weight: 500; text-decoration: none; }
        .delete-action { color: var(--danger-color); }
        .message.success { color: #2f855a; background-color: #c6f6d5; padding: 15px; margin-top: 20px; border-radius: 8px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-lightbulb"></i> લાઈટ મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>
    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_issue['id']) ? 'નવો ઈશ્યુ ઉમેરો' : 'ઈશ્યુ એડિટ કરો'; ?></h3>
                <form action="manage_electricity.php" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_issue['id']); ?>">
                    <input type="text" name="complaint_id" placeholder="ID (દા.ત. C1)" value="<?php echo htmlspecialchars($edit_issue['complaint_id']); ?>" required>
                    <input type="text" name="assigned_to" placeholder="સોંપેલ વ્યક્તિનું નામ" value="<?php echo htmlspecialchars($edit_issue['assigned_to']); ?>" required>
                    <select name="status" required>
                        <option value="ચાલુ" <?php if($edit_issue['status'] == 'ચાલુ') echo 'selected'; ?>>ચાલુ</option>
                        <option value="બંધ" <?php if($edit_issue['status'] == 'બંધ') echo 'selected'; ?>>બંધ</option>
                    </select>
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>
            <div class="table-section">
                <h3>બધા ઈશ્યુ</h3>
                <form action="manage_electricity.php" method="GET" style="margin-bottom: 20px;">
                    <input type="text" name="search" placeholder="ID અથવા સોંપેલ વ્યક્તિ દ્વારા શોધો..." value="<?php echo htmlspecialchars($search_term); ?>">
                </form>
                <table>
                    <thead>
                        <tr><th>ID</th><th>સોંપેલ</th><th>સ્થિતિ</th><th>ક્રિયાઓ</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $issues->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['complaint_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['assigned_to']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <a href="manage_electricity.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_electricity.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
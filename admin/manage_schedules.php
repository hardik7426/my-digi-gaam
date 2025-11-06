<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
function format_time_gujarati($time24) {
    if (empty($time24)) return '';
    list($hour, $minutes) = explode(':', $time24); $hour = (int)$hour;
    $period = '';
    if ($hour >= 21 || $hour < 4) { $period = 'રાત્રે'; }
    elseif ($hour >= 17) { $period = 'સાંજે'; }
    elseif ($hour >= 12) { $period = 'બપોરે'; }
    else { $period = 'સવારે'; }
    $hour12 = $hour % 12;
    if ($hour12 == 0) { $hour12 = 12; }
    return sprintf('%s %02d:%s', $period, $hour12, $minutes);
}

$message = '';
// Add/Update Logic
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $name = $_POST['name'];
    $route_from = $_POST['route_from']; // New field
    $route_to = $_POST['route_to'];     // New field
    $arrival_time = $_POST['arrival_time'];
    $departure_time = $_POST['departure_time'];

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO schedules (type, name, route_from, route_to, arrival_time, departure_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $type, $name, $route_from, $route_to, $arrival_time, $departure_time);
        if ($stmt->execute()) $message = "નવો રેકોર્ડ ઉમેરાયો.";
    } else {
        $stmt = $conn->prepare("UPDATE schedules SET type=?, name=?, route_from=?, route_to=?, arrival_time=?, departure_time=? WHERE id=?");
        $stmt->bind_param("ssssssi", $type, $name, $route_from, $route_to, $arrival_time, $departure_time, $id);
        if ($stmt->execute()) $message = "રેકોર્ડ અપડેટ થયો.";
    }
    $stmt->close();
}
// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "રેકોર્ડ દૂર કરાયો.";
    $stmt->close();
}
// Fetch data for editing
$edit_schedule = ['id' => '', 'type' => '', 'name' => '', 'route_from' => '', 'route_to' => '', 'arrival_time' => '', 'departure_time' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM schedules WHERE id = $id");
    $edit_schedule = $result->fetch_assoc();
}
$schedules = $conn->query("SELECT * FROM schedules ORDER BY type, arrival_time");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>સમયપત્રક મેનેજ કરો - એડમિન પેનલ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: rgba(255, 255, 255, 0.8); --card-bg: rgba(255, 255, 255, 0.7); --primary-text: #1a202c; --secondary-text: #4a5568; --accent-color-1: #3182ce; --danger-color: #e53e3e; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.6)), url('../assets/images/index.jpeg'); background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); backdrop-filter: blur(10px); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--primary-text); }
        .main-header i { margin-right: 12px; color: var(--accent-color-1); }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .main-header a.back-link:hover { color: var(--accent-color-1); }
        .admin-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; width: 100%; }
        .form-section, .table-section { background: var(--card-bg); backdrop-filter: blur(10px); border-radius: 12px; padding: 30px 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(255, 255, 255, 0.5); margin-bottom: 30px; }
        .form-section h3, .table-section h3 { margin-bottom: 20px; font-weight: 600; font-size: 1.4rem; color: var(--primary-text); }
        input[type="text"], input[type="time"], select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #cbd5e0; border-radius: 8px; box-sizing: border-box; transition: border-color 0.3s, box-shadow 0.3s; font-family: 'Noto Sans Gujarati', sans-serif; font-size: 1rem; }
        input:focus, select:focus { border-color: var(--accent-color-1); box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); outline: none; }
        button[type="submit"] { display: inline-block; padding: 12px 30px; background-color: var(--accent-color-1); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 600; transition: background-color 0.3s; }
        button[type="submit"]:hover { background-color: #2b6cb0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); text-align: left; }
        thead th { font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary-text); }
        td a { color: var(--accent-color-1); font-weight: 500; text-decoration: none; }
        td a:hover { text-decoration: underline; }
        .delete-action { color: var(--danger-color); }
        .message { text-align: center; padding: 15px; margin-top: 20px; border-radius: 8px; font-weight: 500; }
        .message.success { color: #2f855a; background-color: #c6f6d5; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; font-weight: 500; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-clock"></i> સમયપત્રક મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_schedule['id']) ? 'નવું સમયપત્રક ઉમેરો' : 'સમયપત્રક એડિટ કરો'; ?></h3>
                <form action="manage_schedules.php" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_schedule['id']); ?>">
                    <select name="type" required>
                        <option value="બસ" <?php if($edit_schedule['type'] == 'બસ') echo 'selected'; ?>>બસ</option>
                        <option value="ટ્રેન" <?php if($edit_schedule['type'] == 'ટ્રેન') echo 'selected'; ?>>ટ્રેન</option>
                    </select>
                    <input type="text" name="name" placeholder="નામ (દા.ત. એક્સપ્રેસ)" value="<?php echo htmlspecialchars($edit_schedule['name']); ?>" required>
                    <input type="text" name="route_from" placeholder="ક્યાંથી (From)" value="<?php echo htmlspecialchars($edit_schedule['route_from']); ?>" required>
                    <input type="text" name="route_to" placeholder="ક્યાં સુધી (To)" value="<?php echo htmlspecialchars($edit_schedule['route_to']); ?>" required>
                    
                    <label for="arrival_time">આવવાનો સમય (24-કલાક ફોર્મેટ)</label>
                    <input type="time" id="arrival_time" name="arrival_time" value="<?php echo htmlspecialchars($edit_schedule['arrival_time']); ?>" required>
                    
                    <label for="departure_time">ઉપડવાનો સમય (24-કલાક ફોર્મેટ)</label>
                    <input type="time" id="departure_time" name="departure_time" value="<?php echo htmlspecialchars($edit_schedule['departure_time']); ?>" required>
                    
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>

            <div class="table-section">
                <h3>હાલનું સમયપત્રક</h3>
                <table>
                    <thead>
                        <tr><th>પ્રકાર</th><th>રૂટ</th><th>આવવાનો સમય</th><th>ઉપડવાનો સમય</th><th>ક્રિયાઓ</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['route_from']); ?></strong> થી <strong><?php echo htmlspecialchars($row['route_to']); ?></strong></td>
                            <td><?php echo format_time_gujarati($row['arrival_time']); ?></td>
                            <td><?php echo format_time_gujarati($row['departure_time']); ?></td>
                            <td>
                                <a href="manage_schedules.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_schedules.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
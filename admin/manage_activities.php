<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';
// Add/Update Logic
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $organizer = $_POST['organizer'];
    $contact = $_POST['contact'];
    $schedule_day = $_POST['schedule_day'];
    $schedule_time = $_POST['schedule_time'];
    $location = $_POST['location'];

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO activities (title, organizer, contact, schedule_day, schedule_time, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $organizer, $contact, $schedule_day, $schedule_time, $location);
        if ($stmt->execute()) $message = "નવી પ્રવૃત્તિ ઉમેરાઈ.";
    } else {
        $stmt = $conn->prepare("UPDATE activities SET title=?, organizer=?, contact=?, schedule_day=?, schedule_time=?, location=? WHERE id=?");
        $stmt->bind_param("ssssssi", $title, $organizer, $contact, $schedule_day, $schedule_time, $location, $id);
        if ($stmt->execute()) $message = "પ્રવૃત્તિ અપડેટ થઈ.";
    }
    $stmt->close();
}
// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "પ્રવૃત્તિ દૂર કરાઈ.";
    $stmt->close();
}
// Fetch data for editing
$edit_activity = ['id' => '', 'title' => '', 'organizer' => '', 'contact' => '', 'schedule_day' => '', 'schedule_time' => '', 'location' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM activities WHERE id = $id");
    $edit_activity = $result->fetch_assoc();
}
// Fetch all data
$activities = $conn->query("SELECT * FROM activities ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>પ્રવૃત્તિઓ મેનેજ કરો - એડમિન પેનલ</title>
    
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
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }

        /* Glass Form & Table Sections */
        .form-section, .table-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 30px 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 30px;
        }
        .form-section h3, .table-section h3 { margin-bottom: 20px; font-weight: 600; font-size: 1.4rem; color: var(--primary-text); }
        .form-section label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--secondary-text); }
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-family: 'Noto Sans Gujarati', sans-serif;
            font-size: 1rem;
        }
        input:focus { border-color: var(--accent-color-1); box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); outline: none; }
        button[type="submit"] {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--accent-color-1);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }
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
        <h1><i class="fa-solid fa-hands-praying"></i> પ્રવૃત્તિઓ મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_activity['id']) ? 'નવી પ્રવૃત્તિ ઉમેરો' : 'પ્રવૃત્તિ એડિટ કરો'; ?></h3>
                <form action="manage_activities.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $edit_activity['id']; ?>">
                    
                    <label for="title">શીર્ષક</label>
                    <input type="text" id="title" name="title" placeholder="પ્રવૃત્તિનું શીર્ષક" value="<?php echo htmlspecialchars($edit_activity['title']); ?>" required>
                    
                    <label for="organizer">આયોજક</label>
                    <input type="text" id="organizer" name="organizer" placeholder="આયોજકનું નામ" value="<?php echo htmlspecialchars($edit_activity['organizer']); ?>" required>
                    
                    <label for="contact">સંપર્ક નંબર</label>
                    <input type="text" id="contact" name="contact" placeholder="સંપર્ક માટે મોબાઇલ નંબર" value="<?php echo htmlspecialchars($edit_activity['contact']); ?>" required>
                    
                    <label for="schedule_day">દિવસ</label>
                    <input type="text" id="schedule_day" name="schedule_day" placeholder="દા.ત. સોમ-શનિ, દરરોજ" value="<?php echo htmlspecialchars($edit_activity['schedule_day']); ?>" required>
                    
                    <label for="schedule_time">સમય</label>
                    <input type="text" id="schedule_time" name="schedule_time" placeholder="દા.ત. રાતે 9:00 - 10:00" value="<?php echo htmlspecialchars($edit_activity['schedule_time']); ?>" required>
                    
                    <label for="location">સ્થળ</label>
                    <input type="text" id="location" name="location" placeholder="પ્રવૃત્તિનું સ્થળ" value="<?php echo htmlspecialchars($edit_activity['location']); ?>" required>
                    
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>

            <div class="table-section">
                <h3>બધી પ્રવૃત્તિઓ</h3>
                <table>
                    <thead>
                        <tr><th>શીર્ષક</th><th>આયોજક</th><th>સંપર્ક</th><th>સમયપત્રક</th><th>ક્રિયાઓ</th></tr>
                    </thead>
                    <tbody>
                    <?php while($row = $activities->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['organizer']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td><?php echo htmlspecialchars($row['schedule_day'] . ' (' . $row['schedule_time'] . ')'); ?></td>
                            <td>
                                <a href="manage_activities.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_activities.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
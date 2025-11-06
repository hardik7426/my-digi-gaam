<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';
// Add/Update Logic
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $phone = $_POST['phone'];

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO contacts (name, designation, phone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $designation, $phone);
        if ($stmt->execute()) $message = "नવો સંપર્ક ઉમેરાયો.";
    } else {
        $stmt = $conn->prepare("UPDATE contacts SET name=?, designation=?, phone=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $designation, $phone, $id);
        if ($stmt->execute()) $message = "સંપર્ક અપડેટ થયો.";
    }
    $stmt->close();
}
// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "સંપર્ક દૂર કરાયો.";
    $stmt->close();
}
// Fetch data for editing
$edit_contact = ['id' => '', 'name' => '', 'designation' => '', 'phone' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM contacts WHERE id = $id");
    $edit_contact = $result->fetch_assoc();
}
// Fetch all data
$contacts = $conn->query("SELECT * FROM contacts ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>સંપર્ક મેનેજ કરો - એડમિન પેનલ</title>
    
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

        /* NEW Glass Form & Table Sections */
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
        <h1><i class="fa-solid fa-address-book"></i> સંપર્ક મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_contact['id']) ? 'નવો સંપર્ક ઉમેરો' : 'સંપર્ક એડિટ કરો'; ?></h3>
                <form action="manage_contacts.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $edit_contact['id']; ?>">
                    <input type="text" name="designation" placeholder="હોદ્દો (દા.ત. સરપંચ)" value="<?php echo htmlspecialchars($edit_contact['designation']); ?>" required>
                    <input type="text" name="name" placeholder="નામ" value="<?php echo htmlspecialchars($edit_contact['name']); ?>" required>
                    <input type="text" name="phone" placeholder="ફોન નંબર" value="<?php echo htmlspecialchars($edit_contact['phone']); ?>" required>
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>

            <div class="table-section">
                <h3>બધા સંપર્કો</h3>
                <table>
                    <thead>
                        <tr><th>હોદ્દો</th><th>નામ</th><th>ફોન</th><th>ક્રિયાઓ</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $contacts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['designation']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td>
                                <a href="manage_contacts.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_contacts.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
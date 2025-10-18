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
    $location = $_POST['location'];
    $owner_name = $_POST['owner_name'];
    $contact = $_POST['contact'];
    $open_time = $_POST['open_time'];
    $close_time = $_POST['close_time'];
    $photo = $_POST['existing_photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/medicals/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $photo = time() . '_' . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $photo;
        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $message = "Sorry, there was an error uploading your file.";
            $photo = $_POST['existing_photo'];
        }
    }

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO medical_stores (name, location, owner_name, contact, open_time, close_time, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $location, $owner_name, $contact, $open_time, $close_time, $photo);
        if ($stmt->execute()) $message = "નવો મેડિકલ સ્ટોર ઉમેરાયો.";
    } else {
        $stmt = $conn->prepare("UPDATE medical_stores SET name=?, location=?, owner_name=?, contact=?, open_time=?, close_time=?, photo=? WHERE id=?");
        $stmt->bind_param("sssssssi", $name, $location, $owner_name, $contact, $open_time, $close_time, $photo, $id);
        if ($stmt->execute()) $message = "મેડિકલ સ્ટોર અપડેટ થયો.";
    }
    $stmt->close();
}

// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $conn->query("SELECT photo FROM medical_stores WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['photo']) && file_exists('../uploads/medicals/' . $row['photo'])) {
            unlink('../uploads/medicals/' . $row['photo']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM medical_stores WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "મેડિકલ સ્ટોર દૂર કરાયો.";
    $stmt->close();
}


$edit_store = ['id'=>'', 'name'=>'', 'location'=>'', 'owner_name'=>'', 'contact'=>'', 'open_time'=>'', 'close_time'=>'', 'photo'=>''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM medical_stores WHERE id = $id");
    $edit_store = $result->fetch_assoc();
}
$stores = $conn->query("SELECT * FROM medical_stores ORDER BY name");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>મેડિકલ સ્ટોર મેનેજ કરો</title>
    
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
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--primary-text); }
        .main-header i { margin-right: 12px; color: var(--accent-color-1); }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .admin-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }
        .form-section, .table-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 30px 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 30px;
        }
        .form-section h3, .table-section h3 { margin-bottom: 20px; font-size: 1.4rem; }
        .form-section label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--secondary-text); }
        input[type="text"], input[type="time"], input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 1rem;
            background-color: rgba(255,255,255,0.5);
            font-family: 'Noto Sans Gujarati', sans-serif;
        }
        input:focus {
            outline: none;
            border-color: var(--accent-color-1);
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
        }
        button[type="submit"] {
            padding: 12px 30px;
            background-color: var(--accent-color-1);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); text-align: left; }
        thead th { font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary-text); }
        td a { color: var(--accent-color-1); font-weight: 500; text-decoration: none; }
        .delete-action { color: var(--danger-color); }
        .message.success { color: #2f855a; background-color: #c6f6d5; padding: 15px; margin-top: 20px; border-radius: 8px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-pills"></i> મેડિકલ સ્ટોર મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ</a>
    </header>
    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_store['id']) ? 'નવો મેડિકલ સ્ટોર ઉમેરો' : 'મેડિકલ સ્ટોર એડિટ કરો'; ?></h3>
                <form action="manage_medical_stores.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_store['id']); ?>">
                    <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($edit_store['photo']); ?>">
                    
                    <label for="name">મેડિકલ સ્ટોરનું નામ</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_store['name']); ?>" required>
                    
                    <label for="location">સ્થળ/સરનામું</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($edit_store['location']); ?>" required>
                    
                    <label for="owner_name">માલિકનું નામ</label>
                    <input type="text" id="owner_name" name="owner_name" value="<?php echo htmlspecialchars($edit_store['owner_name']); ?>" required>
                    
                    <label for="contact">સંપર્ક નંબર</label>
                    <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($edit_store['contact']); ?>" required>
                    
                    <label for="open_time">ખુલવાનો સમય</label>
                    <input type="time" id="open_time" name="open_time" value="<?php echo htmlspecialchars($edit_store['open_time']); ?>" required>
                    
                    <label for="close_time">બંધ થવાનો સમય</label>
                    <input type="time" id="close_time" name="close_time" value="<?php echo htmlspecialchars($edit_store['close_time']); ?>" required>
                    
                    <label for="photo">ફોટો</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>
            <div class="table-section">
                <h3>બધા મેડિકલ સ્ટોર્સ</h3>
                <table>
                    <thead><tr><th>નામ</th><th>માલિક</th><th>સંપર્ક</th><th>ક્રિયાઓ</th></tr></thead>
                    <tbody>
                        <?php while($row = $stores->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td>
                                <a href="manage_medical_stores.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_medical_stores.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>
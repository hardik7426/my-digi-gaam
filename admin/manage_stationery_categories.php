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
    $image_name = $_POST['existing_image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/stationery/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $message = "Sorry, there was an error uploading your file.";
            $image_name = $_POST['existing_image'];
        }
    }

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO stationery_categories (name, image) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $image_name);
        if ($stmt->execute()) $message = "નવી કેટેગરી ઉમેરાઈ.";
    } else {
        $stmt = $conn->prepare("UPDATE stationery_categories SET name=?, image=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $image_name, $id);
        if ($stmt->execute()) $message = "કેટેગરી અપડેટ થઈ.";
    }
    $stmt->close();
}
// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $conn->query("SELECT image FROM stationery_categories WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['image']) && file_exists('../uploads/stationery/' . $row['image'])) {
            unlink('../uploads/stationery/' . $row['image']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM stationery_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "કેટેગરી દૂર કરાઈ.";
    $stmt->close();
}

$edit_cat = ['id' => '', 'name' => '', 'image' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM stationery_categories WHERE id = $id");
    $edit_cat = $result->fetch_assoc();
}
$categories = $conn->query("SELECT * FROM stationery_categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>સ્ટેશનરી કેટેગરી મેનેજ કરો</title>
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
        .form-section label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--secondary-text); }
        input[type="text"], input[type="file"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; background-color: rgba(255,255,255,0.5); }
        button { padding: 12px 30px; background-color: var(--accent-color-1); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 600; }
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
        <h1><i class="fa-solid fa-tags"></i> સ્ટેશનરી કેટેગરી મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ</a>
    </header>
    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_cat['id']) ? 'નવી કેટેગરી ઉમેરો' : 'કેટેગરી એડિટ કરો'; ?></h3>
                <form action="manage_stationery_categories.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_cat['id']); ?>">
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_cat['image']); ?>">
                    <label for="name">કેટેગરીનું નામ</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_cat['name']); ?>" required>
                    <label for="image">કેટેગરીનો ફોટો</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>
            <div class="table-section">
                <h3>બધી કેટેગરી</h3>
                <table>
                    <thead><tr><th>નામ</th><th>ક્રિયાઓ</th></tr></thead>
                    <tbody>
                        <?php while($row = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>
                                <a href="manage_stationery_categories.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_stationery_categories.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('આ કેટેગરી ડિલીટ કરવાથી તેની બધી પ્રોડક્ટ્સ પણ ડિલીટ થઈ જશે. શું તમે ચોક્કસ છો?');">ડિલીટ</a>
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
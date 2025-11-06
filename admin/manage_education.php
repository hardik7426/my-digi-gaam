<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';
// Add/Update Logic
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $standard = $_POST['standard'];
    $medium = $_POST['medium'];
    $subject = $_POST['subject'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $pdf_filename = $_POST['existing_pdf'];

    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $target_dir = "../uploads/";
        $pdf_filename = time() . '_' . basename($_FILES["pdf_file"]["name"]);
        $target_file = $target_dir . $pdf_filename;
        if (!move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
            $message = "Sorry, there was an error uploading your file.";
            $pdf_filename = $_POST['existing_pdf'];
        }
    }

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO education_content (standard, medium, subject, category, title, description, pdf_filename) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $standard, $medium, $subject, $category, $title, $description, $pdf_filename);
        if ($stmt->execute()) $message = "નવી સામગ્રી ઉમેરાઈ.";
    } else {
        $stmt = $conn->prepare("UPDATE education_content SET standard=?, medium=?, subject=?, category=?, title=?, description=?, pdf_filename=? WHERE id=?");
        $stmt->bind_param("sssssssi", $standard, $medium, $subject, $category, $title, $description, $pdf_filename, $id);
        if ($stmt->execute()) $message = "સામગ્રી અપડેટ થઈ.";
    }
    $stmt->close();
}
// Delete Logic
// ... (Your existing delete PHP logic remains the same)
// Fetch data for editing
$edit_content = ['id' => '', 'standard' => '', 'medium' => '', 'subject' => '', 'category' => '', 'title' => '', 'description' => '', 'pdf_filename' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM education_content WHERE id = $id");
    $edit_content = $result->fetch_assoc();
}
$contents = $conn->query("SELECT * FROM education_content ORDER BY standard, medium, subject, id DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>શિક્ષણ મેનેજ કરો - એડમિન પેનલ</title>
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
        .form-section h3, .table-section h3 { margin-bottom: 20px; font-weight: 600; font-size: 1.4rem; }
        .form-section label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--secondary-text); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        input, select, textarea { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #cbd5e0; border-radius: 8px; font-family: 'Noto Sans Gujarati', sans-serif; font-size: 1rem; }
        button { padding: 12px 30px; background-color: var(--accent-color-1); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); text-align: left; }
        td a { color: var(--accent-color-1); font-weight: 500; text-decoration: none; }
        .delete-action { color: var(--danger-color); }
        .message.success { color: #2f855a; background-color: #c6f6d5; padding: 15px; margin-top: 20px; border-radius: 8px; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; font-weight: 500; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-graduation-cap"></i> શિક્ષણ મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_content['id']) ? 'નવી સામગ્રી ઉમેરો' : 'સામગ્રી એડિટ કરો'; ?></h3>
                <form action="manage_education.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_content['id']); ?>">
                    <input type="hidden" name="existing_pdf" value="<?php echo htmlspecialchars($edit_content['pdf_filename']); ?>">
                    
                    <div class="form-grid">
                        <div>
                            <label for="standard">ધોરણ</label>
                            <select id="standard" name="standard" required>
                                <?php for($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if($edit_content['standard'] == $i) echo 'selected'; ?>>ધોરણ <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="medium">માધ્યમ</label>
                            <select id="medium" name="medium" required>
                                <option value="Gujarati" <?php if($edit_content['medium'] == 'Gujarati') echo 'selected'; ?>>ગુજરાતી</option>
                                <option value="English" <?php if($edit_content['medium'] == 'English') echo 'selected'; ?>>English</option>
                            </select>
                        </div>
                    </div>

                    <label for="subject">વિષય</label>
                    <input type="text" id="subject" name="subject" placeholder="દા.ત. ગણિત, વિજ્ઞાન" value="<?php echo htmlspecialchars($edit_content['subject']); ?>" required>

                    <label for="category">પ્રકાર</label>
                    <select id="category" name="category" required>
                        <option value="Book" <?php if($edit_content['category'] == 'Book') echo 'selected'; ?>>પુસ્તક (Book)</option>
                        <option value="Navneet" <?php if($edit_content['category'] == 'Navneet') echo 'selected'; ?>>નવનીત (Digest)</option>
                        <option value="Old Paper" <?php if($edit_content['category'] == 'Old Paper') echo 'selected'; ?>>જૂના પ્રશ્નપત્રો</option>
                    </select>
                    
                    <label for="title">શીર્ષક</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($edit_content['title']); ?>" required>
                    
                    <label for="description">વર્ણન (વૈકલ્પિક)</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($edit_content['description']); ?></textarea>
                    
                    <label for="pdf_file">PDF ફાઈલ</label>
                    <input type="file" name="pdf_file" id="pdf_file" accept=".pdf">
                    <?php if (!empty($edit_content['pdf_filename'])): ?>
                        <p>હાલની ફાઈલ: <?php echo htmlspecialchars($edit_content['pdf_filename']); ?></p>
                    <?php endif; ?>

                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>

            <div class="table-section">
                <h3>બધી શૈક્ષણિક સામગ્રી</h3>
                <table>
                    <thead>
                        <tr><th>ધોરણ</th><th>માધ્યમ</th><th>વિષય</th><th>શીર્ષક</th><th>ક્રિયાઓ</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $contents->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['standard']); ?></td>
                            <td><?php echo htmlspecialchars($row['medium']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <a href="manage_education.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_education.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
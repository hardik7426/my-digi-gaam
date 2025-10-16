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
    $publish_date = $_POST['publish_date'];
    $description = $_POST['description'];
    $source_name = $_POST['source_name'];
    $source_link = $_POST['source_link'];
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
        $stmt = $conn->prepare("INSERT INTO news (title, publish_date, description, source_name, source_link, pdf_filename) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $publish_date, $description, $source_name, $source_link, $pdf_filename);
        if ($stmt->execute()) $message = "નવા સમાચાર ઉમેરાયા.";
    } else {
        $stmt = $conn->prepare("UPDATE news SET title=?, publish_date=?, description=?, source_name=?, source_link=?, pdf_filename=? WHERE id=?");
        $stmt->bind_param("ssssssi", $title, $publish_date, $description, $source_name, $source_link, $pdf_filename, $id);
        if ($stmt->execute()) $message = "સમાચાર અપડેટ થયા.";
    }
    $stmt->close();
}
// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $conn->query("SELECT pdf_filename FROM news WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['pdf_filename']) && file_exists('../uploads/' . $row['pdf_filename'])) {
            unlink('../uploads/' . $row['pdf_filename']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "સમાચાર દૂર કરાયા.";
    $stmt->close();
}
// Fetch data for editing
$edit_news = ['id' => '', 'title' => '', 'publish_date' => date('Y-m-d'), 'description' => '', 'source_name' => '', 'source_link' => '', 'pdf_filename' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM news WHERE id = $id");
    $edit_news = $result->fetch_assoc();
}
$news_items = $conn->query("SELECT * FROM news ORDER BY publish_date DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>સમાચાર મેનેજ કરો - એડમિન પેનલ</title>
    
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
        
        input[type="text"], input[type="date"], input[type="url"], textarea {
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
        input:focus, textarea:focus { border-color: var(--accent-color-1); box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); outline: none; }
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
        input[type="file"] { border: 1px solid #cbd5e0; border-radius: 8px; padding: 10px; width: 100%; margin-top: 5px; margin-bottom: 15px; }
        .existing-file-note { font-size: 0.9rem; color: var(--secondary-text); margin-top: -10px; margin-bottom: 20px; }

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
        <h1><i class="fa-solid fa-newspaper"></i> સમાચાર મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ પર પાછા જાઓ</a>
    </header>

    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_news['id']) ? 'નવા સમાચાર ઉમેરો' : 'સમાચાર એડિટ કરો'; ?></h3>
                <form action="manage_news.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $edit_news['id']; ?>">
                    <input type="hidden" name="existing_pdf" value="<?php echo $edit_news['pdf_filename']; ?>">
                    
                    <label for="title">શીર્ષક</label>
                    <input type="text" id="title" name="title" placeholder="સમાચારનું શીર્ષક" value="<?php echo htmlspecialchars($edit_news['title']); ?>" required>
                    
                    <label for="publish_date">પ્રકાશિત તારીખ</label>
                    <input type="date" id="publish_date" name="publish_date" value="<?php echo htmlspecialchars($edit_news['publish_date']); ?>" required>
                    
                    <label for="source_name">સમાચાર સ્ત્રોત (દા.ત. દિવ્ય ભાસ્કર)</label>
                    <input type="text" id="source_name" name="source_name" placeholder="ન્યૂઝપેપરનું નામ" value="<?php echo htmlspecialchars($edit_news['source_name']); ?>">

                    <label for="source_link">મૂળ લેખની લિંક (વૈકલ્પિક)</label>
                    <input type="url" id="source_link" name="source_link" placeholder="https://example.com/news-article" value="<?php echo htmlspecialchars($edit_news['source_link']); ?>">

                    <label for="description">વિગતો</label>
                    <textarea id="description" name="description" placeholder="સમાચારની વિગતો..." rows="8"><?php echo htmlspecialchars($edit_news['description']); ?></textarea>

                    <label for="pdf_file">સમાચાર કટિંગ PDF (વૈકલ્પિક)</label>
                    <input type="file" name="pdf_file" id="pdf_file" accept=".pdf">
                    <?php if (!empty($edit_news['pdf_filename'])): ?>
                        <p class="existing-file-note">હાલની ફાઈલ: <?php echo htmlspecialchars($edit_news['pdf_filename']); ?></p>
                    <?php endif; ?>
                    
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>

            <div class="table-section">
                <h3>બધા સમાચાર</h3>
                <table>
                    <thead>
                        <tr><th>તારીખ</th><th>શીર્ષક</th><th>ક્રિયાઓ</th></tr>
                    </thead>
                    <tbody>
                    <?php while($row = $news_items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d-m-Y', strtotime($row['publish_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <a href="manage_news.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_news.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
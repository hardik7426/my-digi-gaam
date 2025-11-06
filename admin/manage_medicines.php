<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';
$diseases = $conn->query("SELECT * FROM diseases ORDER BY name");

// Add/Update Logic
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $disease_id = $_POST['disease_id'];
    $name = $_POST['name'];
    $company = $_POST['company'];
    $price = $_POST['price'];
    $usage_time = $_POST['usage_time'];
    $age_restriction = $_POST['age_restriction'];
    $dosage = $_POST['dosage'];
    $take_with = $_POST['take_with'];
    $form_type = $_POST['form_type'];
    $category_type = $_POST['category_type'];
    $photo = $_POST['existing_photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/medicines/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $photo = time() . '_' . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $photo;
        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $message = "ફોટો અપલોડ કરવામાં ભૂલ આવી."; $photo = $_POST['existing_photo'];
        }
    }

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO medicines (disease_id, name, company, price, usage_time, age_restriction, dosage, take_with, form_type, category_type, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdsssssss", $disease_id, $name, $company, $price, $usage_time, $age_restriction, $dosage, $take_with, $form_type, $category_type, $photo);
        if ($stmt->execute()) $message = "નવી દવા ઉમેરાઈ.";
    } else {
        $stmt = $conn->prepare("UPDATE medicines SET disease_id=?, name=?, company=?, price=?, usage_time=?, age_restriction=?, dosage=?, take_with=?, form_type=?, category_type=?, photo=? WHERE id=?");
        $stmt->bind_param("issdsssssssi", $disease_id, $name, $company, $price, $usage_time, $age_restriction, $dosage, $take_with, $form_type, $category_type, $photo, $id);
        if ($stmt->execute()) $message = "દવાની વિગતો અપડેટ થઈ.";
    }
    $stmt->close();
}

// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete photo file
    $result = $conn->query("SELECT photo FROM medicines WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['photo']) && file_exists('../uploads/medicines/' . $row['photo'])) {
            unlink('../uploads/medicines/' . $row['photo']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "દવાની વિગતો દૂર કરાઈ.";
    $stmt->close();
}

$edit_med = ['id'=>'', 'disease_id'=>'', 'name'=>'', 'company'=>'', 'price'=>'', 'usage_time'=>'', 'age_restriction'=>'', 'dosage'=>'', 'take_with'=>'', 'form_type'=>'', 'category_type'=>'', 'photo'=>''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM medicines WHERE id = $id");
    $edit_med = $result->fetch_assoc();
}
$medicines = $conn->query("SELECT m.*, d.name as disease_name FROM medicines m JOIN diseases d ON m.disease_id = d.id ORDER BY d.name, m.name");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>દવાઓ મેનેજ કરો - એડમિન પેનલ</title>
    
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
        input[type="text"], input[type="number"], input[type="file"], select, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 1rem;
            background-color: rgba(255,255,255,0.5);
            font-family: 'Noto Sans Gujarati', sans-serif;
        }
        input:focus, select:focus, textarea:focus {
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
        <h1><i class="fa-solid fa-capsules"></i> દવાઓ મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ</a>
    </header>
    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_med['id']) ? 'નવી દવા ઉમેરો' : 'દવા એડિટ કરો'; ?></h3>
                <form action="manage_medicines.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_med['id']); ?>">
                    <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($edit_med['photo']); ?>">
                    
                    <label for="disease_id">રોગ (જેના માટે આ દવા છે)</label>
                    <select id="disease_id" name="disease_id" required>
                        <option value="">-- રોગ પસંદ કરો --</option>
                        <?php mysqli_data_seek($diseases, 0); ?>
                        <?php while($d = $diseases->fetch_assoc()): ?>
                        <option value="<?php echo $d['id']; ?>" <?php if($edit_med['disease_id'] == $d['id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label for="name">દવાનું નામ</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_med['name']); ?>" required>
                    
                    <label for="company">કંપની</label>
                    <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($edit_med['company']); ?>">
                    
                    <label for="price">કિંમત (₹)</label>
                    <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($edit_med['price']); ?>" required>
                    
                    <label for="usage_time">ક્યારે લેવી (દા.ત. જમ્યા પહેલા, જમ્યા પછી)</label>
                    <input type="text" id="usage_time" name="usage_time" value="<?php echo htmlspecialchars($edit_med['usage_time']); ?>">
                    
                    <label for="age_restriction">ઉંમર પ્રતિબંધ (દા.ત. 18+ વર્ષ, બાળકો માટે)</label>
                    <input type="text" id="age_restriction" name="age_restriction" value="<?php echo htmlspecialchars($edit_med['age_restriction']); ?>">
                    
                    <label for="dosage">ડોઝ (દા.ત. 1 ગોળી, 2 ચમચી)</label>
                    <input type="text" id="dosage" name="dosage" value="<?php echo htmlspecialchars($edit_med['dosage']); ?>">
                    
                    <label for="take_with">શેની સાથે લેવી (પાણી, દૂધ)</label>
                    <input type="text" id="take_with" name="take_with" value="<?php echo htmlspecialchars($edit_med['take_with']); ?>">
                    
                    <label for="form_type">દવાનો પ્રકાર (ટેબ્લેટ, લિક્વિડ, વગેરે)</label>
                    <input type="text" id="form_type" name="form_type" value="<?php echo htmlspecialchars($edit_med['form_type']); ?>">
                    
                    <label for="category_type">કેટેગરી</label>
                    <select id="category_type" name="category_type">
                        <option value="Allopathic" <?php if($edit_med['category_type'] == 'Allopathic') echo 'selected'; ?>>એલોપેથિક</option>
                        <option value="Ayurvedic" <?php if($edit_med['category_type'] == 'Ayurvedic') echo 'selected'; ?>>આયુર્વેદિક</option>
                        <option value="Homeopathic" <?php if($edit_med['category_type'] == 'Homeopathic') echo 'selected'; ?>>હોમિયોપેથિક</option>
                    </select>
                    
                    <label for="photo">ફોટો</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>

            <div class="table-section">
                <h3>બધી દવાઓ</h3>
                <table>
                    <thead><tr><th>રોગ</th><th>દવાનું નામ</th><th>કિંમત</th><th>ક્રિયાઓ</th></tr></thead>
                    <tbody>
                        <?php while($row = $medicines->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['disease_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>₹<?php echo htmlspecialchars($row['price']); ?></td>
                            <td>
                                <a href="manage_medicines.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_medicines.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
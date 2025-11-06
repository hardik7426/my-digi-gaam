<?php
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit();
}

$message = '';
// Updated query to get categories with store names
$categories = $conn->query("SELECT c.id, c.name, s.name as store_name FROM stationery_categories c JOIN stationery_stores s ON c.stationery_store_id = s.id ORDER BY s.name, c.name");

// Add/Update Logic
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $company = $_POST['company'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image_name = $_POST['existing_image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/stationery/";
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $message = "Sorry, there was an error uploading your file.";
            $image_name = $_POST['existing_image'];
        }
    }

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO stationery_products (category_id, name, company, price, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $category_id, $name, $company, $price, $description, $image_name);
        if ($stmt->execute()) $message = "નવી પ્રોડક્ટ ઉમેરાઈ.";
    } else {
        $stmt = $conn->prepare("UPDATE stationery_products SET category_id=?, name=?, company=?, price=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("issssi", $category_id, $name, $company, $price, $description, $image_name, $id);
        if ($stmt->execute()) $message = "પ્રોડક્ટ અપડેટ થઈ.";
    }
    $stmt->close();
}

// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $conn->query("SELECT image FROM stationery_products WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['image']) && file_exists('../uploads/stationery/' . $row['image'])) {
            unlink('../uploads/stationery/' . $row['image']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM stationery_products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $message = "પ્રોડક્ટ દૂર કરાઈ.";
    $stmt->close();
}

$edit_product = ['id' => '', 'category_id' => '', 'name' => '', 'company' => '', 'price' => '', 'description' => '', 'image' => ''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM stationery_products WHERE id = $id");
    $edit_product = $result->fetch_assoc();
}
$products = $conn->query("SELECT p.*, c.name as category_name FROM stationery_products p JOIN stationery_categories c ON p.category_id = c.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <title>સ્ટેશનરી પ્રોડક્ટ્સ મેનેજ કરો</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        input, select, textarea { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; font-family: 'Noto Sans Gujarati', sans-serif; background-color: rgba(255,255,255,0.5); }
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
        <h1><i class="fa-solid fa-pencil-ruler"></i> સ્ટેશનરી પ્રોડક્ટ્સ મેનેજ કરો</h1>
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> એડમિન ડેશબોર્ડ</a>
    </header>
    <main>
        <div class="admin-container">
            <div class="form-section">
                <h3><?php echo empty($edit_product['id']) ? 'નવી પ્રોડક્ટ ઉમેરો' : 'પ્રોડક્ટ એડિટ કરો'; ?></h3>
                <form action="manage_stationery_products.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_product['id']); ?>">
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_product['image']); ?>">
                    
                    <label for="category_id">કેટેગરી (સ્ટોર)</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- કેટેગરી પસંદ કરો --</option>
                        <?php mysqli_data_seek($categories, 0); ?>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php if($edit_product['category_id'] == $cat['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['store_name'] . ' - ' . $cat['name']); // Show Store Name ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="name">પ્રોડક્ટનું નામ</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>

                    <label for="company">કંપની</label>
                    <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($edit_product['company']); ?>">
                    
                    <label for="price">કિંમત (₹)</label>
                    <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($edit_product['price']); ?>" required>

                    <label for="description">વર્ણન</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($edit_product['description']); ?></textarea>
                    
                    <label for="image">પ્રોડક્ટનો ફોટો</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    
                    <button type="submit" name="save">સેવ કરો</button>
                </form>
                <?php if ($message) echo "<p class='message success'>$message</p>"; ?>
            </div>

            <div class="table-section">
                <h3>બધી પ્રોડક્ટ્સ</h3>
                <table>
                    <thead><tr><th>કેટેગરી</th><th>નામ</th><th>કિંમત</th><th>ક્રિયાઓ</th></tr></thead>
                    <tbody>
                        <?php while($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>₹<?php echo htmlspecialchars($row['price']); ?></td>
                            <td>
                                <a href="manage_stationery_products.php?edit=<?php echo $row['id']; ?>">એડિટ</a> |
                                <a href="manage_stationery_products.php?delete=<?php echo $row['id']; ?>" class="delete-action" onclick="return confirm('શું તમે ચોક્કસ આને કાઢી નાખવા માંગો છો?');">ડિલીટ</a>
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
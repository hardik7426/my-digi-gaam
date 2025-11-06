<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all diseases for the dropdown
$diseases = $conn->query("SELECT * FROM diseases ORDER BY name");

// Fetch medicines based on selected disease
$selected_disease_id = isset($_GET['disease_id']) ? (int)$_GET['disease_id'] : 0;
$medicines = [];
if ($selected_disease_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM medicines WHERE disease_id = ? ORDER BY name");
    $stmt->bind_param("i", $selected_disease_id);
    $stmt->execute();
    $medicines_result = $stmt->get_result();
    while ($row = $medicines_result->fetch_assoc()) {
        $medicines[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>દવાઓની માહિતી - માય ડિજી ગામ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --header-bg: #ffffff; --card-bg: rgba(255, 255, 255, 0.92); --primary-text: #1a202c; --secondary-text: #718096; --accent-color-1: #3182ce; --accent-color-2: #38b2ac; --shadow-color: rgba(0, 0, 0, 0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Gujarati', sans-serif; color: var(--primary-text); background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/index.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; }
        main { flex-grow: 1; }
        .main-header { background-color: var(--header-bg); padding: 1rem 2.5rem; box-shadow: 0 4px 6px -1px var(--shadow-color); display: flex; justify-content: space-between; align-items: center; }
        .main-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--accent-color-1); }
        .main-header i { margin-right: 12px; }
        .main-header a.back-link { color: var(--secondary-text); text-decoration: none; font-weight: 500; }
        .content-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; width: 100%; }
        
        .filter-container { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); padding: 25px; border-radius: 12px; margin-bottom: 40px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); text-align: center; }
        .filter-container h3 { margin-bottom: 15px; font-size: 1.2rem; }
        #diseaseSelect { width: 100%; max-width: 400px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: 'Noto Sans Gujarati', sans-serif; font-size: 1rem; }
        
        .medicine-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .medicine-card { background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border: 1px solid rgba(255, 255, 255, 0.3); overflow: hidden; }
        .medicine-card img { width: 100%; height: 220px; object-fit: cover; }
        .card-content { padding: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .card-header h2 { font-size: 1.4rem; font-weight: 700; }
        .price { font-size: 1.5rem; font-weight: 700; color: var(--accent-color-2); }
        .company { font-size: 1rem; color: var(--secondary-text); font-weight: 500; margin-bottom: 20px; }
        .details-list { list-style: none; padding: 0; }
        .details-list li { display: flex; align-items: center; margin-bottom: 10px; font-size: 0.95rem; }
        .details-list i { margin-right: 10px; color: var(--accent-color-1); width: 20px; text-align: center; }
        
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-capsules"></i> દવાઓની માહિતી</h1>
        <a href="hospital.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <div class="filter-container">
                <h3>તમારી બીમારી / રોગ પસંદ કરો</h3>
                <form action="medicines.php" method="GET">
                    <select id="diseaseSelect" name="disease_id" onchange="this.form.submit()">
                        <option value="">-- રોગ પસંદ કરો --</option>
                        <?php while($row = $diseases->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php if($selected_disease_id == $row['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>

            <div class="medicine-grid">
                <?php foreach($medicines as $med): ?>
                <div class="medicine-card">
                    <img src="uploads/medicines/<?php echo htmlspecialchars($med['photo'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($med['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x300?text=No+Photo';">
                    <div class="card-content">
                        <div class="card-header">
                            <h2><?php echo htmlspecialchars($med['name']); ?></h2>
                            <span class="price">₹<?php echo htmlspecialchars($med['price']); ?></span>
                        </div>
                        <h4 class="company"><?php echo htmlspecialchars($med['company']); ?></h4>
                        <ul class="details-list">
                            <li><i class="fa-solid fa-pills"></i><strong>પ્રકાર:</strong> <?php echo htmlspecialchars($med['form_type']); ?> (<?php echo htmlspecialchars($med['category_type']); ?>)</li>
                            <li><i class="fa-solid fa-syringe"></i><strong>ડોઝ:</strong> <?php echo htmlspecialchars($med['dosage']); ?></li>
                            <li><i class="fa-solid fa-clock"></i><strong>ક્યારે:</strong> <?php echo htmlspecialchars($med['usage_time']); ?></li>
                            <li><i class="fa-solid fa-glass-water"></i><strong>સાથે:</strong> <?php echo htmlspecialchars($med['take_with']); ?></li>
                            <li><i class="fa-solid fa-user-check"></i><strong>ઉંમર:</strong> <?php echo htmlspecialchars($med['age_restriction']); ?></li>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>Hardik , Dhiraj , Nihar</strong>
    </footer>
</body>
</html>
<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: doctors.php'); exit();
}

$doctor_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'error';

// Fetch doctor details to show
$stmt = $conn->prepare("SELECT name, timings FROM doctors WHERE id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
if (!$doctor) { die("Doctor not found."); }

// Handle form submission
if (isset($_POST['book'])) {
    $patient_name = $_POST['patient_name'];
    $dob = $_POST['dob'];
    $location = $_POST['location'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $contact_number = $_POST['contact_number'];
    $problem_description = $_POST['problem_description'];
    $booking_date = $_POST['booking_date'];

    $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor_id, patient_name, dob, location, gender, age, contact_number, problem_description, booking_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iissssisss", $user_id, $doctor_id, $patient_name, $dob, $location, $gender, $age, $contact_number, $problem_description, $booking_date);
    
    if ($stmt->execute()) {
        $message = "તમારી એપોઇન્ટમેન્ટ રિક્વેસ્ટ મોકલી દેવામાં આવી છે. એડમિન ટૂંક સમયમાં 'મેસેન્જર' ટેબમાં જવાબ આપશે.";
        $message_type = 'success';
    } else {
        $message = "રિક્વેસ્ટ મોકલવામાં ભૂલ આવી.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>એપોઇન્ટમેન્ટ બુક કરો</title>
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
        .content-container { max-width: 800px; margin: 40px auto; padding: 40px; width: 90%; background: var(--card-bg); backdrop-filter: blur(8px); border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group.full-width { grid-column: span 2; }
        label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--secondary-text); }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; }
        button[type="submit"] { width: 100%; padding: 15px; margin-top: 20px; background: linear-gradient(135deg, var(--accent-color-1), var(--accent-color-2)); color: white; border: none; border-radius: 8px; font-size: 1.2rem; font-weight: 600; cursor: pointer; }
        .message { text-align: center; padding: 15px; margin-top: 20px; border-radius: 8px; font-weight: 500; }
        .message.success { color: #2f855a; background-color: #c6f6d5; }
        .message.error { color: #c53030; background-color: #fed7d7; }
        .footer { background-color: #2d3748; color: #a0aec0; text-align: center; padding: 20px 0; margin-top: auto; font-size: 0.9rem; }
        .footer strong { color: #ffffff; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } .form-group.full-width { grid-column: span 1; } }
    </style>
</head>
<body>
    <header class="main-header">
        <h1><i class="fa-solid fa-calendar-check"></i> એપોઇન્ટમેન્ટ બુક કરો</h1>
        <a href="doctor_detail.php?id=<?php echo $doctor_id; ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> પાછા જાઓ</a>
    </header>
    <main>
        <div class="content-container">
            <h3>ડો. <?php echo htmlspecialchars($doctor['name']); ?> સાથે એપોઇન્ટમેન્ટ</h3>
            <p style="color: var(--secondary-text); margin-bottom: 20px;">કૃપા કરીને નીચેનું ફોર્મ ભરો. એડમિન ટૂંક સમયમાં તમારો સંપર્ક કરશે.</p>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php else: ?>
            <form action="book_appointment.php?id=<?php echo $doctor_id; ?>" method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="patient_name">દર્દીનું નામ</label>
                        <input type="text" id="patient_name" name="patient_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_number">સંપર્ક નંબર</label>
                        <input type="text" id="contact_number" name="contact_number" required>
                    </div>
                    <div class="form-group">
                        <label for="dob">જન્મ તારીખ</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="age">ઉંમર</label>
                        <input type="number" id="age" name="age" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="location">સ્થળ/સરનામું</label>
                        <input type="text" id="location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">જાતિ</label>
                        <select id="gender" name="gender" required>
                            <option value="Male">પુરુષ</option>
                            <option value="Female">સ્ત્રી</option>
                            <option value="Other">અન્ય</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="booking_date">બુકિંગ તારીખ</label>
                        <input type="date" id="booking_date" name="booking_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="problem_description">સમસ્યાનું ટૂંકું વર્ણન</label>
                        <textarea id="problem_description" name="problem_description" rows="4" required></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>ડોક્ટરનો ઉપલબ્ધ સમય</label>
                        <input type="text" value="<?php echo htmlspecialchars($doctor['timings']); ?>" readonly style="background-color: #f7fafc;">
                    </div>
                </div>
                <button type="submit" name="book">રિક્વેસ્ટ મોકલો</button>
            </form>
            <?php endif; ?>
        </div>
    </main>
    <footer class="footer">
        © ૨૦૨૫ માય ડિજી ગામ | All Rights Reserved.<br>
        Developed by <strong>[Your Name Here]</strong>
    </footer>
</body>
</html>
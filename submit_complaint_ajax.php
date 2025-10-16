<?php
require 'db.php';

// રિસ્પોન્સ માટે હેડર સેટ કરો
header('Content-Type: application/json');

// તપાસો કે જરૂરી ડેટા મળ્યો છે કે નહીં
if (isset($_POST['name']) && isset($_POST['street'])) {
    $name = $_POST['name'];
    $street = $_POST['street'];
    $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';

    if (empty($name) || empty($street)) {
        echo json_encode(['status' => 'error', 'message' => 'કૃપા કરીને નામ અને શેરીની વિગતો ભરો.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO complaints (complainant_name, street_details, mobile_number) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $street, $mobile);

    if ($stmt->execute()) {
        // સફળતા પર JSON રિસ્પોન્સ મોકલો
        echo json_encode(['status' => 'success', 'message' => 'આભાર! તમારી ફરીયાદ સફળતાપૂર્વક નોંધાઈ ગઈ છે.']);
    } else {
        // એરર પર JSON રિસ્પોન્સ મોકલો
        echo json_encode(['status' => 'error', 'message' => 'ફરીયાદ સબમિટ કરવામાં ભૂલ આવી.']);
    }
    $stmt->close();
} else {
    // જો ડેટા ન મળ્યો હોય તો એરર મોકલો
    echo json_encode(['status' => 'error', 'message' => 'અમાન્ય વિનંતી.']);
}
?>
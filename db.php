<?php
// પહેલા તપાસો કે સેશન ચાલુ છે કે નહીં. જો ન હોય, તો જ તેને શરૂ કરો.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_digi_gaam";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
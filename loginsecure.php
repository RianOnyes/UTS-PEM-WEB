<?php
$conn = new mysqli("localhost", "root", "", "e_wallet");

$phone = $_POST['phone'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = ? AND password = ?");
$stmt->bind_param("ss", $phone, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    session_start();
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    echo "Login berhasil! Selamat datang, " . $user['phone_number'];
} else {
    echo "Nomor telepon atau password salah!";
}
?>

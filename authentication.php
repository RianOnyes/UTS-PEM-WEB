<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "password", "e_wallet");

// Mengambil input dari form
$phone = $_POST['phone'];
$password = $_POST['password'];

// Gunakan prepared statement untuk mencegah SQL Injection
$stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = ? AND password = ?");
$stmt->bind_param("ss", $phone, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Login berhasil
    session_start();
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    header("Location: dashboard.php");
} else {
    // Login gagal
    echo "Nomor telepon atau password salah!";
}
?>

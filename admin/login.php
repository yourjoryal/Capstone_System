<?php
session_start();
$conn = new mysqli('localhost','root','','capstone_system');

$msg = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $msg = "Invalid login.";
    }
}
?>

<form method="POST">
    <h2>Admin Login</h2>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button name="login">Login</button>
    <p style="color:red;"><?php echo $msg; ?></p>
</form>

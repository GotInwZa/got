<?php
include 'config/connectdb.php'; // ไฟล์เชื่อมต่อฐานข้อมูล
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim(string: $_POST["username"]);
    $password = trim(string: $_POST["password"]);

    /* ====== เตรียมคำสั่ง SQL แบบปลอดภัย ====== */
    $sql = "SELECT user_id, username, password, role , add_by
            FROM users 
            WHERE username = ?";

    $stmt = $conn->prepare(query: $sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    /* ====== ตรวจสอบว่ามีผู้ใช้หรือไม่ ====== */
    if ($result->num_rows === 0) {
        $error = "ไม่มีผู้ใช้ในระบบ";
    } else if($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        /* ====== ตรวจสอบรหัสผ่าน ====== */
        if ($password === $row["password"] || password_verify($password,$row["password"])) {
            // เก็บ session
            $_SESSION["login"] = true;
            $_SESSION["user_id"] = $row["user_id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["role"] = $row["role"];
            $_SESSION["add_by"]=$row["add_by"];

            // เช็ค role
            if ($row["role"] === 1 || $row["role"] === 3) {
                header("Location: admin/admin_user.php");
                exit;
            } elseif ($row["role"] === 2) {
                header("Location: user/books.php");
                exit;
            }
        } else {
            $error = "ไม่มีผู้ใช้ในระบบ";
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <h4 class="text-center mb-3">เข้าสู่ระบบ</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Login
            </button>
        </form>
        <div class="text-end mt-3">
            <a href="guide.php"
            class="text-decoration-none text-secondary small">
                อ่านคู่มือผู้ใช้
            </a>
        </div>
    </div>
</div>

</body>
</html>

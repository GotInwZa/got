<?php
require_once '../config/connectdb.php';
require_once '../nav.php';
session_start();

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header(header: "Location: ../login.php");
    exit;
}

$error = "";
$success = "";

if (isset($_POST['save'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hostname = trim($_POST['hostname']);
    $role     = $_POST['role'];

    /* ====== ตรวจค่าว่าง ====== */
    if ($username === "" || $password === "" || $hostname === "" || $role === "") {
        $error = "กรุณากรอกข้อมูลให้ครบ";
    } else {

        /* ====== ตรวจ username ซ้ำ ====== */
        $sql_check = "SELECT user_id FROM users WHERE username = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว";
        } else {

            /* ====== เข้ารหัส password ====== */
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            /* ====== เพิ่มผู้ใช้ ====== */
            $sql_insert = "INSERT INTO users (username, password, hostname, role)
                           VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param(
                "ssss",
                $username,
                $hash_password,
                $hostname,
                $role
            );

            if ($stmt_insert->execute()) {
                $success = "เพิ่มผู้ใช้เรียบร้อยแล้ว";
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }

            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Create User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark fw-bold">
            เพิ่มผู้ใช้ใหม่
        </div>
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
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

                <div class="mb-3">
                    <label class="form-label">Hostname</label>
                    <input type="text" name="hostname" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- เลือกสิทธิ์ --</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="admin_user.php" class="btn btn-secondary">
                        กลับ
                    </a>
                    <button type="submit" name="save" class="btn btn-success">
                        Save
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>

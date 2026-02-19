<?php
require_once '../config/connectdb.php';
require_once '../nav.php';
session_start();

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['user_id'])) {
    header("Location: admin_user.php");
    exit;
}

$user_id = (int)$_GET['user_id'];
$error = "";
$success = "";

/* ====== ดึงข้อมูลผู้ใช้ ====== */
$sql = "SELECT user_id, username, hostname, role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_user.php");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

/* ====== อัปเดตข้อมูล ====== */
if (isset($_POST['save'])) {

    $username = trim($_POST['username']);
    $hostname = trim($_POST['hostname']);
    $role     = $_POST['role'];

    if ($username === "" || $hostname === "" || $role === "") {
        $error = "กรุณากรอกข้อมูลให้ครบ";
    } else {

        $sql_update = "
            UPDATE users
            SET username = ?, hostname = ?, role = ?
            WHERE user_id = ?
        ";
        $stmt_up = $conn->prepare($sql_update);
        $stmt_up->bind_param("sssi", $username, $hostname, $role, $user_id);

        if ($stmt_up->execute()) {
            $success = "แก้ไขข้อมูลเรียบร้อยแล้ว";
            // อัปเดตค่าที่แสดงทันที
            $user['username'] = $username;
            $user['hostname'] = $hostname;
            $user['role'] = $role;
        } else {
            $error = "ไม่สามารถแก้ไขข้อมูลได้";
        }

        $stmt_up->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark fw-bold">
            แก้ไขผู้ใช้
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
                    <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                    <input type="text" name="username" class="form-control"
                           value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">ชื่อที่แสดง (Hostname)</label>
                    <input type="text" name="hostname" class="form-control"
                           value="<?= htmlspecialchars($user['hostname']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                        <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
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




<?php
require_once '../config/connectdb.php';
require_once '../nav.php';
session_start();

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true || $_SESSION["role"] == 2) {
    header(header: "Location: ../login.php");
    exit;
}

/* ====== ลบผู้ใช้ ====== */
if (isset($_POST["delete_user_id"])) {
    $delete_id = $_POST["delete_user_id"];

    $sql_delete = "DELETE FROM users WHERE user_id = ?";
    $stmt_del = $conn->prepare($sql_delete);
    $stmt_del->bind_param("i", $delete_id);
    $stmt_del->execute();
    $stmt_del->close();
}

/* ====== ดึงข้อมูลผู้ใช้ ====== */
$sql = "SELECT user_id, username, hostname, role FROM users WHERE add_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$_SESSION["add_by"]);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin - Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">รายชื่อผู้ใช้ในระบบ</h3>

        <a href="admin_create_user.php"
        class="btn btn-warning text-dark fw-bold">
            + Create User
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Hostname</th>
                    <th>Role</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["user_id"]) ?></td>
                    <td><?= htmlspecialchars($row["username"]) ?></td>
                    <td><?= htmlspecialchars($row["hostname"]) ?></td>
                    <td><?= htmlspecialchars($row["role"]) ?></td>
                    <td>
                        <!-- Edit -->
                        <a href="admin_user_edit.php?user_id=<?= $row["user_id"] ?>"
                           class="btn btn-warning btn-sm">
                           Edit
                        </a>

                        <!-- Delete -->
                        <button class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal<?= $row["user_id"] ?>">
                            Delete
                        </button>
                    </td>
                </tr>

                <!-- Modal Delete -->
                <div class="modal fade" id="deleteModal<?= $row["user_id"] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">ยืนยันการลบ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                จะลบผู้ใช้นี้หรือไม่ ?
                            </div>
                            <div class="modal-footer">
                                <form method="post">
                                    <input type="hidden" name="delete_user_id"
                                           value="<?= $row["user_id"] ?>">
                                    <button type="submit" class="btn btn-danger">ใช่</button>
                                    <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">
                                        ไม่
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>

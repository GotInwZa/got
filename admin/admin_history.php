<?php
require_once '../config/connectdb.php';
require_once '../nav.php';
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header(header: "Location: ../login.php");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = $_GET['search'] ?? '';

$sql = "
SELECT 
    bl.log_id,
    u.hostname,
    b.title,
    bl.status,
    bl.logs_time
FROM books_log bl
JOIN users u ON u.user_id = bl.user_id
JOIN books b ON b.book_id = bl.book_id
WHERE
";

$params = [];
$types = "";

// user เห็นเฉพาะของตัวเอง
if ($role === 'user') {
    $sql .= " bl.user_id = ?";
    $types .= "i";
    $params[] = $user_id;
}

// search
if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR u.hostname LIKE ?)";
    $types .= "ss";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY bl.logs_time DESC LIMIT ?";
$types .= "i";
$params[] = $limit;

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container mt-4">

    <h3 class="mb-3">ประวัติการยืม–คืนหนังสือ</h3>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   placeholder="ค้นหาชื่อหนังสือ / hostname"
                   value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="col-md-2">
            <select name="limit" class="form-select" onchange="this.form.submit()">
                <?php foreach ([5,10,25,50] as $l): ?>
                    <option value="<?= $l ?>" <?= $limit==$l?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100">ค้นหา</button>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>ผู้ใช้ (Hostname)</th>
            <th>ชื่อหนังสือ</th>
            <th>สถานะ</th>
            <th>เวลา</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['log_id'] ?></td>
                    <td><?= htmlspecialchars($row['hostname']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <?php if ($row['status'] == 0): ?>
                            <span class="badge bg-success">ยืม</span>
                        <?php else: ?>
                            <span class="badge bg-danger">คืน</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['logs_time'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">ไม่พบข้อมูล</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
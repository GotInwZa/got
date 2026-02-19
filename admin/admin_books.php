<?php
require_once '../config/connectdb.php';
require_once '../nav.php';
session_start();

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header(header: "Location: ../login.php");
    exit;
}

/* ====== รับค่า search และ limit ====== */
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$limit  = isset($_GET["limit"]) ? (int)$_GET["limit"] : 10;
if ($limit <= 0) $limit = 10;

/* ====== SQL ====== */
$sql = "
SELECT 
    books.book_id,
    books.title,
    books.description,
    theme.theme_name,
    books.author,
    books.status
FROM books
JOIN theme ON theme.theme_id = books.theme
WHERE books.title LIKE ?
   OR books.author LIKE ?
   OR theme.theme_name LIKE ?
ORDER BY theme.theme_name ASC
LIMIT ?
";

$stmt = $conn->prepare($sql);

$like = "%{$search}%";
$stmt->bind_param("sssi", $like, $like, $like, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการหนังสือ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h3 class="mb-3">รายการหนังสือทั้งหมด</h3>

    <!-- Search + Limit -->
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="ค้นหาหนังสือ / ผู้เขียน / ธีม"
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="limit" class="form-select">
                <?php foreach ([5,10,20,50] as $l): ?>
                    <option value="<?= $l ?>" <?= $limit == $l ? "selected" : "" ?>>
                        แสดง <?= $l ?> แถว
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-left">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>ชื่อหนังสือ</th>
                    <th>รายละเอียด</th>
                    <th>Theme</th>
                    <th>ผู้เขียน</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="7">ไม่พบข้อมูล</td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["book_id"] ?></td>
                    <td ><?= htmlspecialchars($row["title"]) ?></td>
                    <td ><?= htmlspecialchars($row["description"]) ?></td>
                    <td ><?= htmlspecialchars($row["theme_name"]) ?></td>
                    <td ><?= htmlspecialchars($row["author"]) ?></td>
                    <td>
                        <?php if ($row["status"] === 0): ?>
                            <span class="badge bg-success">ว่าง</span>
                        <?php else: ?>
                            <span class="badge bg-danger">ถูกยืม</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row["status"] === 0): ?>
                        <a href="borrow.php?book_id=<?= $row["book_id"] ?>"
                           class="btn btn-sm btn-primary">
                           ยืม
                        </a>
                        <?php else: ?>
                        <a href="return.php?book_id=<?= $row["book_id"] ?>"
                           class="btn btn-sm btn-secondary">
                           คืน
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
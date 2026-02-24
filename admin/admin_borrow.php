<?php
require_once '../config/connectdb.php';
require_once '../nav.php';
session_start();

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header(header: "Location: ../login.php");
    exit;
}

if (!isset($_GET["book_id"])) {
    die("ไม่พบหนังสือ");
}

$book_id = (int)$_GET["book_id"];

/* ====== ดึงข้อมูลหนังสือ ====== */
$sql_book = "SELECT title, description, status FROM books WHERE book_id = ?";
$stmt_book = $conn->prepare($sql_book);
$stmt_book->bind_param("i", $book_id);
$stmt_book->execute();
$stmt_book->bind_result($title, $description, $book_status);
$stmt_book->fetch();
$stmt_book->close();

/* ====== ถ้าหนังสือถูกยืมแล้ว ====== */
if ($book_status == 1) {
    die("หนังสือเล่มนี้ถูกยืมไปแล้ว");
}

/* ====== บันทึกการยืม ====== */
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $borrow_date = $_POST["borrow_date"];
    $return_date = $_POST["return_date"];

    $user_id = $_SESSION["user_id"];
    $status = 1;

    /* insert transactions */
    $sql_insert = "
        INSERT INTO transactions
        (user_id, book_id, borrow_date, return_date, status)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param(
        "iissi",
        $user_id,
        $book_id,
        $borrow_date,
        $return_date,
        $status
    );
    $stmt->execute();
    $stmt->close();

    /* update book status */
    $sql_update = "UPDATE books SET status = 1 WHERE book_id = ?";
    $stmt2 = $conn->prepare($sql_update);
    $stmt2->bind_param("i", $book_id);
    $stmt2->execute();
    $stmt2->close();

    $success = true;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืมหนังสือ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h4 class="mb-4">บันทึกการยืมหนังสือ</h4>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    บันทึกการยืมเรียบร้อยแล้ว
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">ชื่อหนังสือ</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($title) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียด</label>
                    <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">วันที่ยืม</label>
                    <input type="date"
                           name="borrow_date"
                           class="form-control"
                           value="<?= date("Y-m-d") ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">วันที่คืน</label>
                    <input type="date"
                           name="return_date"
                           class="form-control"
                           min="<?= date("Y-m-d") ?>"
                           max="<?= date("Y-m-d", strtotime("+90 days")) ?>"
                           required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save
                </button>
                <a href="admin_books.php" class="btn btn-secondary">
                    กลับ
                </a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>

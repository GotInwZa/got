<?php
require_once '../config/connectdb.php';
session_start();

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header(header: "Location: ../login.php");
    exit;
}

/* ====== รับ book_id ====== */
if (!isset($_GET["book_id"])) {
    die("ไม่พบหนังสือ");
}

$book_id = (int)$_GET["book_id"];
$user_id = $_SESSION["user_id"];

/* ====== ดึงข้อมูลหนังสือ ====== */
$sql_book = "
    SELECT title, description, status 
    FROM books 
    WHERE book_id = ?
";
$stmt_book = $conn->prepare($sql_book);
$stmt_book->bind_param("i", $book_id);
$stmt_book->execute();
$stmt_book->bind_result($title, $description, $book_status);
$stmt_book->fetch();
$stmt_book->close();

/* ====== ถ้าหนังสือยังไม่ถูกยืม ====== */
if ($book_status == 0) {
    die("หนังสือเล่มนี้ยังไม่ได้ถูกยืม");
}

/* ====== หา transaction ที่ยังไม่คืนของ user นี้ ====== */
$sql_tran = "
    SELECT transaction_id, borrow_date 
    FROM transactions
    WHERE user_id = ? 
      AND book_id = ?
      AND status = 1
    ORDER BY borrow_date DESC
    LIMIT 1
";
$stmt_tran = $conn->prepare($sql_tran);
$stmt_tran->bind_param("ii", $user_id, $book_id);
$stmt_tran->execute();
$stmt_tran->bind_result($transaction_id, $borrow_date);

if (!$stmt_tran->fetch()) {
    die("ไม่พบรายการยืมของคุณ");
}
$stmt_tran->close();

/* ====== บันทึกการคืน ====== */
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $return_date = date("Y-m-d");

    /* update transactions */
    $sql_update_tran = "
        UPDATE transactions
        SET return_date = ?, status = 0
        WHERE transaction_id = ?
    ";
    $stmt1 = $conn->prepare($sql_update_tran);
    $stmt1->bind_param("si", $return_date, $transaction_id);
    $stmt1->execute();
    $stmt1->close();

    /* update book status */
    $sql_update_book = "
        UPDATE books 
        SET status = 0 
        WHERE book_id = ?
    ";
    $stmt2 = $conn->prepare($sql_update_book);
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
    <title>คืนหนังสือ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h4 class="mb-4">คืนหนังสือ</h4>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    คืนหนังสือเรียบร้อยแล้ว
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">ชื่อหนังสือ</label>
                    <input type="text"
                           class="form-control"
                           value="<?= htmlspecialchars($title) ?>"
                           readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียด</label>
                    <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($description) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save
                </button>
                <a href="books.php" class="btn btn-secondary">
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

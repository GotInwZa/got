<?php
require_once '../config/connectdb.php';
require_once '../nav.php';
session_start();

/* ====== เช็คสิทธิ์ ====== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true || $_SESSION["role"] == 2) {
    header(header: "Location: ../login.php");
    exit;
}

$themes = [];
$sql_theme = "SELECT theme_id, theme_name FROM theme ORDER BY theme_id ASC";
$stmt_theme = $conn->prepare($sql_theme);
$stmt_theme->execute();
$result_theme = $stmt_theme->get_result();

while ($row = $result_theme->fetch_assoc()) {
    $themes[] = $row;
}
$stmt_theme->close();

/* ====== บันทึกข้อมูลหนังสือ ====== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $theme_id = $_POST["theme_id"];
    $author = trim($_POST["author"]);

    if ($description === "") {
        $description = $title;
    }

    $sql_insert = "INSERT INTO books (title, description, theme, author,add_by)
                   VALUES (?, ?, ?, ?,?)";

    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param(
        "ssisi",
        $title,
        $description,
        $theme_id,
        $author,
        $_SESSION["role"]
    );
    $stmt->execute();
    $stmt->close();

}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin - Add Book</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h4 class="mb-4">เพิ่มหนังสือ</h4>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    เพิ่มหนังสือเรียบร้อยแล้ว
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">ชื่อหนังสือ</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียด</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Theme</label>
                    <select name="theme_id" class="form-select" required>
                        <option value="">-- เลือกธีมหนังสือ --</option>
                        <?php foreach ($themes as $theme): ?>
                            <option value="<?= $theme["theme_id"] ?>">
                                <?= htmlspecialchars($theme["theme_name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">ผู้เขียน</label>
                    <input type="text" name="author" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>

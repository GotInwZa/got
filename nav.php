<?php
session_start();

/* ====== เช็คสิทธิ์ ====== */
$role = $_SESSION['role'] ?? null;
?>

<nav class="navbar navbar-expand-md navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      การยืมหนังสือบนอุปกรณ์เคลื่อนที่
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

        <?php if ($role === 1): ?>
          <li class="nav-item">
            <a class="nav-link" href="admin_user.php">User</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_books.php">Book</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_history.php">History</a>
          </li>

        <?php elseif ($role === 'user'): ?>
          <li class="nav-item">
            <a class="nav-link" href="books.php">Book</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="history.php">History</a>
          </li>
        <?php endif; ?>

        <?php if ($role): ?>
          <li class="nav-item">
            <a class="nav-link text-warning" href="logout.php">Logout</a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

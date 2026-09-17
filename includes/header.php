<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MAPOLY Bookshop IMS</title>
<!-- Bootstrap via CDN — no install needed, browser caches it after first load -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/mapoly_bookshop/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <span class="navbar-brand mb-0 h1">MAPOLY Bookshop IMS</span>
    <?php if (!empty($_SESSION['username'])): ?>
      <div class="d-flex align-items-center flex-wrap gap-2">
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="/mapoly_bookshop/dashboard/admin_dashboard.php" class="btn btn-sm btn-outline-light">Dashboard</a>
          <a href="/mapoly_bookshop/books/add_book.php" class="btn btn-sm btn-outline-light">Add Book</a>
          <a href="/mapoly_bookshop/categories/index.php" class="btn btn-sm btn-outline-light">Categories</a>
          <a href="/mapoly_bookshop/suppliers/index.php" class="btn btn-sm btn-outline-light">Suppliers</a>
          <a href="/mapoly_bookshop/reports/index.php" class="btn btn-sm btn-outline-light">Reports</a>
          <a href="/mapoly_bookshop/transactions/record_purchase.php" class="btn btn-sm btn-outline-light">Restock</a>
        <?php else: ?>
          <a href="/mapoly_bookshop/dashboard/officer_dashboard.php" class="btn btn-sm btn-outline-light">Dashboard</a>
          <a href="/mapoly_bookshop/transactions/record_sale.php" class="btn btn-sm btn-outline-light">Record Sale</a>
        <?php endif; ?>
        <span class="text-white ms-2">
          <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)
        </span>
        <a href="/mapoly_bookshop/auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    <?php endif; ?>
  </div>
</nav>
<div class="container">
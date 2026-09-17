<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

// Algorithm 3, step 1: books at or below their reorder level
$lowStockStmt = $pdo->query('
    SELECT book_id, title, quantity_in_stock, reorder_level
    FROM tbl_book
    WHERE quantity_in_stock <= reorder_level
    ORDER BY (quantity_in_stock - reorder_level) ASC
');
$lowStockBooks = $lowStockStmt->fetchAll();

// Full stock summary for the table
$booksStmt = $pdo->query('
    SELECT b.book_id, b.title, c.category_name, b.quantity_in_stock, b.reorder_level
    FROM tbl_book b
    LEFT JOIN tbl_category c ON b.category_id = c.category_id
    ORDER BY b.title
');
$books = $booksStmt->fetchAll();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM tbl_book WHERE book_id = ?')->execute([$id]);
    header('Location: /mapoly_bookshop/dashboard/admin_dashboard.php?deleted=1');
    exit;
}

require __DIR__ . '/../includes/header.php';
?>

<h3>Admin Dashboard</h3>

<?php if (!empty($_GET['deleted'])): ?>
  <div class="alert alert-success">Book deleted.</div>
<?php endif; ?>

<?php if ($lowStockBooks): ?>
<div class="card card-alert mb-4">
  <div class="card-body">
    <h5 class="card-title">Low-Stock Alerts (<?= count($lowStockBooks) ?> item<?= count($lowStockBooks) === 1 ? '' : 's' ?>)</h5>
    <ul class="mb-0">
      <?php foreach ($lowStockBooks as $b): ?>
        <li>
          <?= htmlspecialchars($b['title']) ?> —
          <?= (int)$b['quantity_in_stock'] ?> in stock (reorder level <?= (int)$b['reorder_level'] ?>)
          <a href="/mapoly_bookshop/transactions/record_purchase.php?book_id=<?= (int)$b['book_id'] ?>" class="ms-2">Restock</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h5>Stock Summary</h5>
  <a href="/mapoly_bookshop/books/add_book.php" class="btn btn-success btn-sm">+ Add New Book</a>
</div>

<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr>
      <th>Book</th>
      <th>Category</th>
      <th>Qty in Stock</th>
      <th>Reorder Level</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($books as $b): ?>
      <?php $low = $b['quantity_in_stock'] <= $b['reorder_level']; ?>
      <tr class="<?= $low ? 'low-stock' : '' ?>">
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td><?= htmlspecialchars($b['category_name'] ?? '—') ?></td>
        <td><?= (int)$b['quantity_in_stock'] ?></td>
        <td><?= (int)$b['reorder_level'] ?></td>
        <td><?= $low ? 'LOW' : 'OK' ?></td>
        <td>
          <a href="/mapoly_bookshop/books/edit.php?id=<?= $b['book_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
          <a href="/mapoly_bookshop/dashboard/admin_dashboard.php?delete=<?= $b['book_id'] ?>"
             class="btn btn-sm btn-outline-danger"
             onclick="return confirm('Delete this book? This will also delete its sale/purchase history. This cannot be undone.');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
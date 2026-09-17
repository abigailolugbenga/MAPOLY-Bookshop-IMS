<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('officer');

$booksStmt = $pdo->query('SELECT book_id, title, quantity_in_stock FROM tbl_book ORDER BY title');
$books = $booksStmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h3>Store Officer Dashboard</h3>
<a href="/mapoly_bookshop/transactions/record_sale.php" class="btn btn-primary mb-3">Record a Sale</a>

<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr><th>Book</th><th>Qty in Stock</th></tr>
  </thead>
  <tbody>
    <?php foreach ($books as $b): ?>
      <tr>
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td><?= (int)$b['quantity_in_stock'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>

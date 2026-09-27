<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('officer');

$booksStmt = $pdo->query('
    SELECT b.book_id, b.title, b.quantity_in_stock, b.reorder_level, b.unit_price,
           b.last_restocked_at, c.category_name
    FROM tbl_book b
    LEFT JOIN tbl_category c ON b.category_id = c.category_id
    ORDER BY b.title
');
$books = $booksStmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h3>Store Officer Dashboard</h3>
<a href="/mapoly_bookshop/transactions/record_sale.php" class="btn btn-primary mb-3">Record a Sale</a>

<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr><th>Book</th><th>Category</th><th>Availability</th><th>Last Restocked</th><th>Price (₦)</th></tr>
  </thead>
  <tbody>
    <?php foreach ($books as $b): ?>
      <tr>
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td><?= htmlspecialchars($b['category_name'] ?? '—') ?></td>
        <td><?= (int)$b['quantity_in_stock'] ?></td>
        <td><?= $b['last_restocked_at'] ? date('d M Y, h:i A', strtotime($b['last_restocked_at'])) : '—' ?></td>
        <td><?= number_format($b['unit_price'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
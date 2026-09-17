<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('officer');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId   = (int)$_POST['book_id'];
    $quantity = (int)$_POST['quantity'];
    $userId   = $_SESSION['user_id'];

    if ($quantity <= 0) {
        $error = 'Quantity must be greater than zero.';
    } else {
        try {
            // Step 2: begin a real database transaction
            $pdo->beginTransaction();

            // Step 3: lock the row so two simultaneous sales can't both succeed (NFR5 concurrency)
            $stmt = $pdo->prepare('SELECT quantity_in_stock, reorder_level, unit_price, title FROM tbl_book WHERE book_id = ? FOR UPDATE');
            $stmt->execute([$bookId]);
            $book = $stmt->fetch();

            if (!$book) {
                throw new Exception('Book not found.');
            }

            // Step 4: check sufficient stock
            if ($quantity > $book['quantity_in_stock']) {
                throw new Exception('Insufficient stock. Only ' . $book['quantity_in_stock'] . ' left.');
            }

            // Step 5: deduct stock and log the transaction
            $newQty = $book['quantity_in_stock'] - $quantity;
            $pdo->prepare('UPDATE tbl_book SET quantity_in_stock = ? WHERE book_id = ?')
                ->execute([$newQty, $bookId]);

            $pdo->prepare('
                INSERT INTO tbl_transaction (book_id, user_id, transaction_type, quantity, unit_price)
                VALUES (?, ?, "Sale", ?, ?)
            ')->execute([$bookId, $userId, $quantity, $book['unit_price']]);

            // Step 6: commit — sale and stock update succeed together, or not at all
            $pdo->commit();

            // Step 7: low-stock check happens automatically next time the dashboard loads
            $success = 'Sale recorded successfully.'
                . ($newQty <= $book['reorder_level'] ? ' Note: this item is now at or below its reorder level.' : '');

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$books = $pdo->query('SELECT book_id, title, quantity_in_stock FROM tbl_book ORDER BY title')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h3>Record a Sale</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="post" class="bg-white p-4 rounded shadow-sm" style="max-width:500px;">
  <div class="mb-3">
    <label class="form-label">Book</label>
    <select name="book_id" class="form-select" required>
      <?php foreach ($books as $b): ?>
        <option value="<?= $b['book_id'] ?>">
          <?= htmlspecialchars($b['title']) ?> (<?= $b['quantity_in_stock'] ?> in stock)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">Quantity Sold</label>
    <input type="number" name="quantity" class="form-control" min="1" required>
  </div>
  <button type="submit" class="btn btn-primary">Record Sale</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>

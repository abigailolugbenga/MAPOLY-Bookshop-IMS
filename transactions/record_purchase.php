<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

$error = '';
$success = '';
$preselectBookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId   = (int)$_POST['book_id'];
    $quantity = (int)$_POST['quantity'];
    $userId   = $_SESSION['user_id'];

    if ($quantity <= 0) {
        $error = 'Quantity must be greater than zero.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('SELECT quantity_in_stock, unit_price FROM tbl_book WHERE book_id = ? FOR UPDATE');
            $stmt->execute([$bookId]);
            $book = $stmt->fetch();

            if (!$book) {
                throw new Exception('Book not found.');
            }

            // Step 3: add quantity received to current stock
            $newQty = $book['quantity_in_stock'] + $quantity;
            $pdo->prepare('UPDATE tbl_book SET quantity_in_stock = ? WHERE book_id = ?')
                ->execute([$newQty, $bookId]);

            // Step 4: log the purchase transaction
            $pdo->prepare('
                INSERT INTO tbl_transaction (book_id, user_id, transaction_type, quantity, unit_price)
                VALUES (?, ?, "Purchase", ?, ?)
            ')->execute([$bookId, $userId, $quantity, $book['unit_price']]);

            $pdo->commit();
            $success = 'Restock recorded successfully. New quantity: ' . $newQty;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$books = $pdo->query('SELECT book_id, title, quantity_in_stock FROM tbl_book ORDER BY title')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h3>Record a Purchase / Restock</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="post" class="bg-white p-4 rounded shadow-sm" style="max-width:500px;">
  <div class="mb-3">
    <label class="form-label">Book</label>
    <select name="book_id" class="form-select" required>
      <?php foreach ($books as $b): ?>
        <option value="<?= $b['book_id'] ?>" <?= $b['book_id'] == $preselectBookId ? 'selected' : '' ?>>
          <?= htmlspecialchars($b['title']) ?> (currently <?= $b['quantity_in_stock'] ?> in stock)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">Quantity Received</label>
    <input type="number" name="quantity" class="form-control" min="1" required>
  </div>
  <button type="submit" class="btn btn-success">Record Restock</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>

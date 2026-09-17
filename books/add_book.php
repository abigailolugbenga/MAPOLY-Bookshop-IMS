<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $isbn        = trim($_POST['isbn'] ?? '');
    $categoryId  = $_POST['category_id'] ?: null;
    $supplierId  = $_POST['supplier_id'] ?: null;
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $reorder     = (int)($_POST['reorder_level'] ?? 0);
    $price       = (float)($_POST['unit_price'] ?? 0);

    if ($title === '' || $quantity < 0 || $reorder < 0 || $price < 0) {
        $error = 'Please fill in all required fields with valid values.';
    } else {
        // Prepared statement — never concatenate user input into SQL directly
        $stmt = $pdo->prepare('
            INSERT INTO tbl_book (title, isbn, category_id, supplier_id, quantity_in_stock, reorder_level, unit_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$title, $isbn, $categoryId, $supplierId, $quantity, $reorder, $price]);
        $success = 'Book added successfully.';
    }
}

$categories = $pdo->query('SELECT category_id, category_name FROM tbl_category ORDER BY category_name')->fetchAll();
$suppliers  = $pdo->query('SELECT supplier_id, supplier_name FROM tbl_supplier ORDER BY supplier_name')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h3>Add New Book / Stock Item</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="post" class="bg-white p-4 rounded shadow-sm" style="max-width:600px;">
  <div class="row mb-3">
    <div class="col">
      <label class="form-label">Book Title</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="col">
      <label class="form-label">ISBN</label>
      <input type="text" name="isbn" class="form-control">
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label class="form-label">Category</label>
      <select name="category_id" class="form-select">
        <option value="">-- Select --</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col">
      <label class="form-label">Supplier</label>
      <select name="supplier_id" class="form-select">
        <option value="">-- Select --</option>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label class="form-label">Quantity</label>
      <input type="number" name="quantity" class="form-control" min="0" required>
    </div>
    <div class="col">
      <label class="form-label">Reorder Level</label>
      <input type="number" name="reorder_level" class="form-control" min="0" required>
    </div>
    <div class="col">
      <label class="form-label">Unit Price (₦)</label>
      <input type="number" step="0.01" name="unit_price" class="form-control" min="0" required>
    </div>
  </div>
  <button type="submit" class="btn btn-success">Save Book</button>
  <a href="/mapoly_bookshop/dashboard/admin_dashboard.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $name    = trim($_POST['supplier_name'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $phone   = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '' || strlen($name) < 2) {
        $error = 'Supplier name must be at least 2 characters.';
    } elseif (!preg_match('/^[\p{L}\s\-\&\.\']+$/u', $name)) {
        $error = 'Supplier name can only contain letters, spaces, and - & . \' (no numbers or symbols like @).';
    } elseif ($contact !== '' && !preg_match('/^[\p{L}\s\-\.\']+$/u', $contact)) {
        $error = 'Contact person can only contain letters, spaces, and - . \' (no numbers).';
    } elseif ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $error = 'Phone number can only contain digits, spaces, and + - ( ), 7-20 characters long.';
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO tbl_supplier (supplier_name, contact_person, phone_number, address)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$name, $contact, $phone, $address]);
        $success = 'Supplier added.';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM tbl_supplier WHERE supplier_id = ?')->execute([$id]);
    $success = 'Supplier deleted.';
}

$suppliers = $pdo->query('
    SELECT s.supplier_id, s.supplier_name, s.contact_person, s.phone_number, s.address, COUNT(b.book_id) AS book_count
    FROM tbl_supplier s
    LEFT JOIN tbl_book b ON b.supplier_id = s.supplier_id
    GROUP BY s.supplier_id, s.supplier_name, s.contact_person, s.phone_number, s.address
    ORDER BY s.supplier_name
')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h3>Suppliers</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="post" class="bg-white p-4 rounded shadow-sm mb-4" style="max-width:600px;">
  <div class="row mb-3">
    <div class="col">
      <label class="form-label">Supplier Name</label>
      <input type="text" name="supplier_name" class="form-control" required>
    </div>
    <div class="col">
      <label class="form-label">Contact Person</label>
      <input type="text" name="contact_person" class="form-control">
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label class="form-label">Phone Number</label>
      <input type="text" name="phone_number" class="form-control">
    </div>
    <div class="col">
      <label class="form-label">Address</label>
      <input type="text" name="address" class="form-control">
    </div>
  </div>
  <button type="submit" name="add_supplier" class="btn btn-success">Add Supplier</button>
</form>

<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Address</th><th>Books Supplied</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($suppliers as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['supplier_name']) ?></td>
        <td><?= htmlspecialchars($s['contact_person'] ?? '—') ?></td>
        <td><?= htmlspecialchars($s['phone_number'] ?? '—') ?></td>
        <td><?= htmlspecialchars($s['address'] ?: '—') ?></td>
        <td><?= (int)$s['book_count'] ?></td>
        <td>
          <a href="/mapoly_bookshop/suppliers/edit.php?id=<?= $s['supplier_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
          <a href="/mapoly_bookshop/suppliers/index.php?delete=<?= $s['supplier_id'] ?>"
             class="btn btn-sm btn-outline-danger"
             onclick="return confirm('Delete this supplier? Books from them will keep their other details but lose this supplier link.');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
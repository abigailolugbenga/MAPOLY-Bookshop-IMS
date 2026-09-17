<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

$stmt = $pdo->prepare('SELECT * FROM tbl_supplier WHERE supplier_id = ?');
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    die('Supplier not found. <a href="/mapoly_bookshop/suppliers/index.php">Back to suppliers</a>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $pdo->prepare('
            UPDATE tbl_supplier
            SET supplier_name = ?, contact_person = ?, phone_number = ?, address = ?
            WHERE supplier_id = ?
        ')->execute([$name, $contact, $phone, $address, $id]);
        $success = 'Supplier updated.';
        $supplier = ['supplier_name' => $name, 'contact_person' => $contact, 'phone_number' => $phone, 'address' => $address];
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h3>Edit Supplier</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="post" class="bg-white p-4 rounded shadow-sm" style="max-width:600px;">
  <div class="row mb-3">
    <div class="col">
      <label class="form-label">Supplier Name</label>
      <input type="text" name="supplier_name" class="form-control"
             value="<?= htmlspecialchars($supplier['supplier_name']) ?>" required>
    </div>
    <div class="col">
      <label class="form-label">Contact Person</label>
      <input type="text" name="contact_person" class="form-control"
             value="<?= htmlspecialchars($supplier['contact_person'] ?? '') ?>">
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label class="form-label">Phone Number</label>
      <input type="text" name="phone_number" class="form-control"
             value="<?= htmlspecialchars($supplier['phone_number'] ?? '') ?>">
    </div>
    <div class="col">
      <label class="form-label">Address</label>
      <input type="text" name="address" class="form-control"
             value="<?= htmlspecialchars($supplier['address'] ?? '') ?>">
    </div>
  </div>
  <button type="submit" class="btn btn-success">Save Changes</button>
  <a href="/mapoly_bookshop/suppliers/index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
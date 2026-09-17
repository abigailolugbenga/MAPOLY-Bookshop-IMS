<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

$stmt = $pdo->prepare('SELECT * FROM tbl_category WHERE category_id = ?');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    die('Category not found. <a href="/mapoly_bookshop/categories/index.php">Back to categories</a>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '' || strlen($name) < 2) {
        $error = 'Category name must be at least 2 characters.';
    } elseif (!preg_match('/^[\p{L}\s\-\&\.]+$/u', $name)) {
        $error = 'Category name can only contain letters, spaces, and - & . (no numbers or symbols like @).';
    } else {
        $pdo->prepare('UPDATE tbl_category SET category_name = ? WHERE category_id = ?')
            ->execute([$name, $id]);
        $success = 'Category updated.';
        $category['category_name'] = $name;
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h3>Edit Category</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="post" class="bg-white p-4 rounded shadow-sm" style="max-width:500px;">
  <div class="mb-3">
    <label class="form-label">Category Name</label>
    <input type="text" name="category_name" class="form-control"
           value="<?= htmlspecialchars($category['category_name']) ?>" required>
  </div>
  <button type="submit" class="btn btn-success">Save Changes</button>
  <a href="/mapoly_bookshop/categories/index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
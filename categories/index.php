<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

$error = '';
$success = '';

// Add new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '' || strlen($name) < 2) {
        $error = 'Category name must be at least 2 characters.';
    } elseif (!preg_match('/^[\p{L}\s\-\&\.]+$/u', $name)) {
        $error = 'Category name can only contain letters, spaces, and - & . (no numbers or symbols like @).';
    } else {
        $stmt = $pdo->prepare('INSERT INTO tbl_category (category_name) VALUES (?)');
        $stmt->execute([$name]);
        $success = 'Category added.';
    }
}

// Delete a category (books in it just lose their category link — see schema ON DELETE SET NULL)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM tbl_category WHERE category_id = ?')->execute([$id]);
    $success = 'Category deleted.';
}

$categories = $pdo->query('
    SELECT c.category_id, c.category_name, COUNT(b.book_id) AS book_count
    FROM tbl_category c
    LEFT JOIN tbl_book b ON b.category_id = c.category_id
    GROUP BY c.category_id, c.category_name
    ORDER BY c.category_name
')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<h3>Categories</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="post" class="bg-white p-3 rounded shadow-sm mb-4 d-flex gap-2" style="max-width:500px;">
  <input type="text" name="category_name" class="form-control" placeholder="New category name" required>
  <button type="submit" name="add_category" class="btn btn-success">Add</button>
</form>

<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr><th>Category</th><th>Books</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($categories as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['category_name']) ?></td>
        <td><?= (int)$c['book_count'] ?></td>
        <td>
          <a href="/mapoly_bookshop/categories/edit.php?id=<?= $c['category_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
          <a href="/mapoly_bookshop/categories/index.php?delete=<?= $c['category_id'] ?>"
             class="btn btn-sm btn-outline-danger"
             onclick="return confirm('Delete this category? Books in it will keep their other details but lose this category.');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
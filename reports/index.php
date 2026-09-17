<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require_role('admin');

// Default to "this month so far" if no dates given
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');
$type      = $_GET['type'] ?? 'all'; // all | Sale | Purchase

$error = '';
if (strtotime($startDate) === false || strtotime($endDate) === false) {
    $error = 'Please enter valid dates.';
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-d');
} elseif ($startDate > $endDate) {
    $error = 'Start date cannot be after end date.';
}

// Build the query — end date is inclusive through 23:59:59
$sql = '
    SELECT t.transaction_id, t.transaction_date, t.transaction_type, t.quantity, t.unit_price,
           (t.quantity * t.unit_price) AS line_total,
           b.title AS book_title, u.username
    FROM tbl_transaction t
    JOIN tbl_book b ON t.book_id = b.book_id
    JOIN tbl_user u ON t.user_id = u.user_id
    WHERE t.transaction_date BETWEEN ? AND ?
';
$params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];

if ($type === 'Sale' || $type === 'Purchase') {
    $sql .= ' AND t.transaction_type = ?';
    $params[] = $type;
}
$sql .= ' ORDER BY t.transaction_date ASC';

$transactions = [];
$totalQty = 0;
$totalAmount = 0;

if (!$error) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    foreach ($transactions as $t) {
        $totalQty += $t['quantity'];
        $totalAmount += $t['line_total'];
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h3>Sales &amp; Purchase Report</h3>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="get" class="bg-white p-3 rounded shadow-sm mb-4 row g-2 align-items-end">
  <div class="col-auto">
    <label class="form-label mb-0">From</label>
    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
  </div>
  <div class="col-auto">
    <label class="form-label mb-0">To</label>
    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
  </div>
  <div class="col-auto">
    <label class="form-label mb-0">Type</label>
    <select name="type" class="form-select">
      <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All</option>
      <option value="Sale" <?= $type === 'Sale' ? 'selected' : '' ?>>Sales only</option>
      <option value="Purchase" <?= $type === 'Purchase' ? 'selected' : '' ?>>Purchases only</option>
    </select>
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
</form>

<div class="row mb-4">
  <div class="col-md-4">
    <div class="card bg-white shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Transactions</div>
        <div class="fs-4 fw-bold"><?= count($transactions) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-white shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Total Quantity</div>
        <div class="fs-4 fw-bold"><?= (int)$totalQty ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-white shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Total Amount (₦)</div>
        <div class="fs-4 fw-bold"><?= number_format($totalAmount, 2) ?></div>
      </div>
    </div>
  </div>
</div>

<table class="table table-bordered bg-white">
  <thead class="table-light">
    <tr>
      <th>Date</th>
      <th>Book</th>
      <th>Type</th>
      <th>Quantity</th>
      <th>Unit Price (₦)</th>
      <th>Amount (₦)</th>
      <th>Staff</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($transactions)): ?>
      <tr><td colspan="7" class="text-center text-muted">No transactions found for this period.</td></tr>
    <?php endif; ?>
    <?php foreach ($transactions as $t): ?>
      <tr>
        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($t['transaction_date']))) ?></td>
        <td><?= htmlspecialchars($t['book_title']) ?></td>
        <td>
          <span class="badge <?= $t['transaction_type'] === 'Sale' ? 'bg-success' : 'bg-primary' ?>">
            <?= htmlspecialchars($t['transaction_type']) ?>
          </span>
        </td>
        <td><?= (int)$t['quantity'] ?></td>
        <td><?= number_format($t['unit_price'], 2) ?></td>
        <td><?= number_format($t['line_total'], 2) ?></td>
        <td><?= htmlspecialchars($t['username']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
  <?php if (!empty($transactions)): ?>
  <tfoot>
    <tr class="table-light fw-bold">
      <td colspan="3">TOTAL</td>
      <td><?= (int)$totalQty ?></td>
      <td></td>
      <td><?= number_format($totalAmount, 2) ?></td>
      <td></td>
    </tr>
  </tfoot>
  <?php endif; ?>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
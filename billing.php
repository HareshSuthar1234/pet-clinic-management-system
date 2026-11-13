<?php
require 'config.php';
$title = 'Billing'; $active = 'billing';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {   // DELETE
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM billing WHERE bill_id = ?");
    $stmt->bind_param('i', $id); $stmt->execute();
    flash('Bill deleted.');
    header('Location: billing.php'); exit;
}

// READ + SEARCH (pet or owner name) + payment status filter
$q = trim($_GET['q'] ?? ''); $st = $_GET['status'] ?? '';
$like = "%$q%";
$sql = "SELECT b.*, a.appointment_date, p.pet_name, o.name AS owner_name FROM billing b
        JOIN appointments a ON b.appointment_id = a.appointment_id
        JOIN pets p ON a.pet_id = p.pet_id JOIN owners o ON p.owner_id = o.owner_id
        WHERE (p.pet_name LIKE ? OR o.name LIKE ? OR b.description LIKE ?)";
$types = 'sss'; $params = [$like, $like, $like];
if ($st !== '') { $sql .= " AND b.status = ?"; $types .= 's'; $params[] = $st; }
$sql .= " ORDER BY b.bill_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result();

include 'includes/header.php';
?>
<div class="bar">
  <div><h1>Billing</h1><p class="sub" style="margin:0">Bills raised for clinic visits</p></div>
  <a class="btn sun" href="billing_form.php">+ Create bill</a>
</div>
<div class="card">
  <form class="search" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search pet, owner or description">
    <select name="status">
      <option value="">All payments</option>
      <?php foreach (['Paid','Pending'] as $s): ?><option <?= selected($st, $s) ?>><?= $s ?></option><?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Search</button>
    <?php if ($q !== '' || $st !== ''): ?><a class="btn grey" href="billing.php">Clear</a><?php endif; ?>
  </form>
</div>
<div class="card wrap">
  <table>
    <tr><th>Bill</th><th>Visit date</th><th>Pet</th><th>Owner</th><th>Description</th><th>Amount</th><th>Paid on</th><th>Status</th><th>Actions</th></tr>
    <?php if ($rows->num_rows === 0): ?>
      <tr><td colspan="9" class="empty">No bills found.</td></tr>
    <?php endif; ?>
    <?php while ($r = $rows->fetch_assoc()): ?>
    <tr>
      <td>#<?= $r['bill_id'] ?></td>
      <td><?= e(date('d M Y', strtotime($r['appointment_date']))) ?></td>
      <td><?= e($r['pet_name']) ?></td><td><?= e($r['owner_name']) ?></td><td><?= e($r['description']) ?></td>
      <td>&#8377;<?= number_format($r['amount'], 2) ?></td>
      <td><?= $r['payment_date'] ? e(date('d M Y', strtotime($r['payment_date']))) : '-' ?></td>
      <td><span class="badge b-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
      <td>
        <a class="btn sm" href="billing_form.php?id=<?= $r['bill_id'] ?>">Edit</a>
        <form class="inline" method="post" data-confirm="Delete bill #<?= $r['bill_id'] ?>?">
          <input type="hidden" name="delete_id" value="<?= $r['bill_id'] ?>">
          <button class="btn sm red" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include 'includes/footer.php'; ?>

<?php
require 'config.php';
$title = 'Appointments'; $active = 'appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {   // DELETE
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param('i', $id); $stmt->execute();
    flash('Appointment deleted.');
    header('Location: appointments.php'); exit;
}

// READ + SEARCH (pet, owner, vet) + status filter + date filter
$q = trim($_GET['q'] ?? ''); $st = $_GET['status'] ?? ''; $date = $_GET['date'] ?? '';
$like = "%$q%";
$sql = "SELECT a.*, p.pet_name, o.name AS owner_name FROM appointments a
        JOIN pets p ON a.pet_id = p.pet_id JOIN owners o ON p.owner_id = o.owner_id
        WHERE (p.pet_name LIKE ? OR o.name LIKE ? OR a.vet_name LIKE ?)";
$types = 'sss'; $params = [$like, $like, $like];
if ($st !== '')   { $sql .= " AND a.status = ?";           $types .= 's'; $params[] = $st; }
if ($date !== '') { $sql .= " AND a.appointment_date = ?"; $types .= 's'; $params[] = $date; }
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result();

include 'includes/header.php';
?>
<div class="bar">
  <div><h1>Appointments</h1><p class="sub" style="margin:0">Book, update and track visits</p></div>
  <a class="btn sun" href="appointment_form.php">+ Book appointment</a>
</div>
<div class="card">
  <form class="search" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search pet, owner or vet">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach (['Scheduled','Completed','Cancelled'] as $s): ?><option <?= selected($st, $s) ?>><?= $s ?></option><?php endforeach; ?>
    </select>
    <input type="date" name="date" value="<?= e($date) ?>" aria-label="Filter by date">
    <button class="btn" type="submit">Search</button>
    <?php if ($q !== '' || $st !== '' || $date !== ''): ?><a class="btn grey" href="appointments.php">Clear</a><?php endif; ?>
  </form>
</div>
<div class="card wrap">
  <table>
    <tr><th>ID</th><th>Date</th><th>Time</th><th>Pet</th><th>Owner</th><th>Vet</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
    <?php if ($rows->num_rows === 0): ?>
      <tr><td colspan="9" class="empty">No appointments found.</td></tr>
    <?php endif; ?>
    <?php while ($r = $rows->fetch_assoc()): ?>
    <tr>
      <td><?= $r['appointment_id'] ?></td>
      <td><?= e(date('d M Y', strtotime($r['appointment_date']))) ?></td>
      <td><?= e(date('h:i A', strtotime($r['appointment_time']))) ?></td>
      <td><?= e($r['pet_name']) ?></td><td><?= e($r['owner_name']) ?></td><td><?= e($r['vet_name']) ?></td>
      <td><?= e($r['reason']) ?></td>
      <td><span class="badge b-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
      <td>
        <a class="btn sm" href="appointment_form.php?id=<?= $r['appointment_id'] ?>">Edit</a>
        <form class="inline" method="post" data-confirm="Delete this appointment for <?= e($r['pet_name']) ?>? Its bill will also be deleted.">
          <input type="hidden" name="delete_id" value="<?= $r['appointment_id'] ?>">
          <button class="btn sm red" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include 'includes/footer.php'; ?>

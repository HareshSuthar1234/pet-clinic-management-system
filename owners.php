<?php
require 'config.php';
$title = 'Owners'; $active = 'owners';

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM owners WHERE owner_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Owner deleted along with their pets, appointments and bills.');
    header('Location: owners.php'); exit;
}

// READ + SEARCH (name, phone or email)
$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$stmt = $conn->prepare("SELECT o.*, (SELECT COUNT(*) FROM pets p WHERE p.owner_id = o.owner_id) AS pet_count
    FROM owners o WHERE o.name LIKE ? OR o.phone LIKE ? OR o.email LIKE ? ORDER BY o.owner_id DESC");
$stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$rows = $stmt->get_result();

include 'includes/header.php';
?>
<div class="bar">
  <div><h1>Owners</h1><p class="sub" style="margin:0">People who bring their pets to the clinic</p></div>
  <a class="btn sun" href="owner_form.php">+ Add owner</a>
</div>
<div class="card">
  <form class="search" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name, phone or email">
    <button class="btn" type="submit">Search</button>
    <?php if ($q !== ''): ?><a class="btn grey" href="owners.php">Clear</a><?php endif; ?>
  </form>
</div>
<div class="card wrap">
  <table>
    <tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Pets</th><th>Actions</th></tr>
    <?php if ($rows->num_rows === 0): ?>
      <tr><td colspan="7" class="empty"><?= $q !== '' ? 'No owners match "' . e($q) . '".' : 'No owners yet. Add the first owner.' ?></td></tr>
    <?php endif; ?>
    <?php while ($r = $rows->fetch_assoc()): ?>
    <tr>
      <td><?= $r['owner_id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td>
      <td><?= e($r['email']) ?></td><td><?= e($r['address']) ?></td><td><?= $r['pet_count'] ?></td>
      <td>
        <a class="btn sm" href="owner_form.php?id=<?= $r['owner_id'] ?>">Edit</a>
        <form class="inline" method="post" data-confirm="Delete <?= e($r['name']) ?>? Their pets, appointments and bills will also be deleted.">
          <input type="hidden" name="delete_id" value="<?= $r['owner_id'] ?>">
          <button class="btn sm red" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include 'includes/footer.php'; ?>

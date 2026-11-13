<?php
require 'config.php';
$title = 'Pets'; $active = 'pets';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {   // DELETE
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM pets WHERE pet_id = ?");
    $stmt->bind_param('i', $id); $stmt->execute();
    flash('Pet deleted along with its appointments and bills.');
    header('Location: pets.php'); exit;
}

// READ + SEARCH (pet name, species, breed or owner) + species filter
$q = trim($_GET['q'] ?? ''); $sp = $_GET['species'] ?? '';
$like = "%$q%";
$sql = "SELECT p.*, o.name AS owner_name FROM pets p JOIN owners o ON p.owner_id = o.owner_id
        WHERE (p.pet_name LIKE ? OR p.breed LIKE ? OR o.name LIKE ?)";
if ($sp !== '') { $sql .= " AND p.species = ?"; }
$sql .= " ORDER BY p.pet_id DESC";
$stmt = $conn->prepare($sql);
if ($sp !== '') $stmt->bind_param('ssss', $like, $like, $like, $sp);
else $stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$rows = $stmt->get_result();
$species = ['Dog','Cat','Bird','Rabbit','Other'];

include 'includes/header.php';
?>
<div class="bar">
  <div><h1>Pets</h1><p class="sub" style="margin:0">Every patient registered with the clinic</p></div>
  <a class="btn sun" href="pet_form.php">+ Add pet</a>
</div>
<div class="card">
  <form class="search" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search pet, breed or owner">
    <select name="species">
      <option value="">All species</option>
      <?php foreach ($species as $s): ?><option <?= selected($sp, $s) ?>><?= $s ?></option><?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Search</button>
    <?php if ($q !== '' || $sp !== ''): ?><a class="btn grey" href="pets.php">Clear</a><?php endif; ?>
  </form>
</div>
<div class="card wrap">
  <table>
    <tr><th>ID</th><th>Pet</th><th>Species</th><th>Breed</th><th>Age</th><th>Gender</th><th>Owner</th><th>Actions</th></tr>
    <?php if ($rows->num_rows === 0): ?>
      <tr><td colspan="8" class="empty">No pets found. Try a different search or add a pet.</td></tr>
    <?php endif; ?>
    <?php while ($r = $rows->fetch_assoc()): ?>
    <tr>
      <td><?= $r['pet_id'] ?></td><td><?= e($r['pet_name']) ?></td><td><?= e($r['species']) ?></td>
      <td><?= e($r['breed']) ?></td><td><?= $r['age'] ?> yr</td><td><?= e($r['gender']) ?></td><td><?= e($r['owner_name']) ?></td>
      <td>
        <a class="btn sm" href="pet_form.php?id=<?= $r['pet_id'] ?>">Edit</a>
        <form class="inline" method="post" data-confirm="Delete <?= e($r['pet_name']) ?>? Its appointments and bills will also be deleted.">
          <input type="hidden" name="delete_id" value="<?= $r['pet_id'] ?>">
          <button class="btn sm red" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include 'includes/footer.php'; ?>

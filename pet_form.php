<?php
require 'config.php';
$active = 'pets';
$id = (int)($_GET['id'] ?? 0);
$row = [];
if ($id) {
    $s = $conn->prepare("SELECT * FROM pets WHERE pet_id = ?");
    $s->bind_param('i', $id); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) { flash('Pet not found.', 'error'); header('Location: pets.php'); exit; }
}
$title = $id ? 'Edit pet' : 'Add pet';
$errors = [];
$species = ['Dog','Cat','Bird','Rabbit','Other'];
$owners = $conn->query("SELECT owner_id, name, phone FROM owners ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = (int)($_POST['owner_id'] ?? 0); $pet_name = trim($_POST['pet_name'] ?? '');
    $sp = $_POST['species'] ?? ''; $breed = trim($_POST['breed'] ?? '');
    $age = $_POST['age'] ?? ''; $gender = $_POST['gender'] ?? '';

    $chk = $conn->prepare("SELECT 1 FROM owners WHERE owner_id=?"); $chk->bind_param('i', $owner_id); $chk->execute();
    if (!$chk->get_result()->num_rows) $errors['owner_id'] = 'Choose an owner.';
    if (strlen($pet_name) < 2 || strlen($pet_name) > 60) $errors['pet_name'] = 'Pet name must be 2 to 60 characters.';
    if (!in_array($sp, $species, true)) $errors['species'] = 'Choose a species.';
    if ($breed === '') $errors['breed'] = 'Breed is required.';
    if (!ctype_digit((string)$age) || $age < 0 || $age > 40) $errors['age'] = 'Age must be a whole number from 0 to 40.';
    if (!in_array($gender, ['Male','Female'], true)) $errors['gender'] = 'Choose a gender.';

    if (!$errors) {
        $age = (int)$age;
        if ($id) {
            $s = $conn->prepare("UPDATE pets SET owner_id=?, pet_name=?, species=?, breed=?, age=?, gender=? WHERE pet_id=?");
            $s->bind_param('isssisi', $owner_id, $pet_name, $sp, $breed, $age, $gender, $id);
            $msg = 'Pet details updated.';
        } else {
            $s = $conn->prepare("INSERT INTO pets (owner_id, pet_name, species, breed, age, gender) VALUES (?,?,?,?,?,?)");
            $s->bind_param('isssis', $owner_id, $pet_name, $sp, $breed, $age, $gender);
            $msg = 'Pet added.';
        }
        $s->execute();
        flash($msg); header('Location: pets.php'); exit;
    }
}
function err($errors, $k) { return isset($errors[$k]) ? '<span class="err">' . $errors[$k] . '</span>' : ''; }
include 'includes/header.php';
?>
<h1><?= $title ?></h1>
<p class="sub">Register the pet under its owner. <a href="owner_form.php">Owner not listed? Add one first.</a></p>
<div class="card">
<form class="validate form" method="post">
  <div><label for="owner_id">Owner</label>
    <select id="owner_id" name="owner_id" required>
      <option value="">Select owner</option>
      <?php while ($o = $owners->fetch_assoc()): ?>
        <option value="<?= $o['owner_id'] ?>" <?= selected($_POST['owner_id'] ?? $row['owner_id'] ?? '', $o['owner_id']) ?>><?= e($o['name']) ?> (<?= e($o['phone']) ?>)</option>
      <?php endwhile; ?>
    </select><?= err($errors, 'owner_id') ?></div>
  <div><label for="pet_name">Pet name</label>
    <input id="pet_name" name="pet_name" required value="<?= old('pet_name', $row) ?>"><?= err($errors, 'pet_name') ?></div>
  <div><label for="species">Species</label>
    <select id="species" name="species" required>
      <option value="">Select species</option>
      <?php foreach ($species as $s): ?><option <?= selected($_POST['species'] ?? $row['species'] ?? '', $s) ?>><?= $s ?></option><?php endforeach; ?>
    </select><?= err($errors, 'species') ?></div>
  <div><label for="breed">Breed</label>
    <input id="breed" name="breed" required value="<?= old('breed', $row) ?>"><?= err($errors, 'breed') ?></div>
  <div><label for="age">Age (years)</label>
    <input id="age" type="number" name="age" min="0" max="40" required value="<?= old('age', $row) ?>"><?= err($errors, 'age') ?></div>
  <div><label for="gender">Gender</label>
    <select id="gender" name="gender" required>
      <option value="">Select gender</option>
      <?php foreach (['Male','Female'] as $g): ?><option <?= selected($_POST['gender'] ?? $row['gender'] ?? '', $g) ?>><?= $g ?></option><?php endforeach; ?>
    </select><?= err($errors, 'gender') ?></div>
  <div class="full actions">
    <button class="btn sun" type="submit"><?= $id ? 'Save changes' : 'Add pet' ?></button>
    <a class="btn grey" href="pets.php">Cancel</a>
  </div>
</form>
</div>
<?php include 'includes/footer.php'; ?>

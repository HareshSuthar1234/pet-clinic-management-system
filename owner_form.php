<?php
require 'config.php';
$active = 'owners';
$id = (int)($_GET['id'] ?? 0);
$row = [];
if ($id) {                                   // EDIT: load existing record to pre-fill the form
    $s = $conn->prepare("SELECT * FROM owners WHERE owner_id = ?");
    $s->bind_param('i', $id); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) { flash('Owner not found.', 'error'); header('Location: owners.php'); exit; }
}
$title = $id ? 'Edit owner' : 'Add owner';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? ''); $address = trim($_POST['address'] ?? '');

    // Server-side validation
    if (strlen($name) < 2 || strlen($name) > 80) $errors['name'] = 'Name must be 2 to 80 characters.';
    if (!preg_match('/^[0-9]{10}$/', $phone)) $errors['phone'] = 'Phone must be exactly 10 digits.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if ($address === '') $errors['address'] = 'Address is required.';

    if (!$errors) {
        if ($id) {   // UPDATE
            $s = $conn->prepare("UPDATE owners SET name=?, phone=?, email=?, address=? WHERE owner_id=?");
            $s->bind_param('ssssi', $name, $phone, $email, $address, $id);
            $msg = 'Owner details updated.';
        } else {     // CREATE
            $s = $conn->prepare("INSERT INTO owners (name, phone, email, address) VALUES (?,?,?,?)");
            $s->bind_param('ssss', $name, $phone, $email, $address);
            $msg = 'Owner added.';
        }
        $s->execute();
        flash($msg); header('Location: owners.php'); exit;
    }
}
include 'includes/header.php';
?>
<h1><?= $title ?></h1>
<p class="sub">All fields are required.</p>
<div class="card">
<form class="validate form" method="post">
  <div><label for="name">Full name</label>
    <input id="name" name="name" required value="<?= old('name', $row) ?>"><?php if (isset($errors['name'])): ?><span class="err"><?= $errors['name'] ?></span><?php endif; ?></div>
  <div><label for="phone">Phone (10 digits)</label>
    <input id="phone" name="phone" required pattern="[0-9]{10}" data-msg="Phone must be exactly 10 digits." value="<?= old('phone', $row) ?>"><?php if (isset($errors['phone'])): ?><span class="err"><?= $errors['phone'] ?></span><?php endif; ?></div>
  <div><label for="email">Email</label>
    <input id="email" type="email" name="email" required value="<?= old('email', $row) ?>"><?php if (isset($errors['email'])): ?><span class="err"><?= $errors['email'] ?></span><?php endif; ?></div>
  <div><label for="address">Address</label>
    <input id="address" name="address" required value="<?= old('address', $row) ?>"><?php if (isset($errors['address'])): ?><span class="err"><?= $errors['address'] ?></span><?php endif; ?></div>
  <div class="full actions">
    <button class="btn sun" type="submit"><?= $id ? 'Save changes' : 'Add owner' ?></button>
    <a class="btn grey" href="owners.php">Cancel</a>
  </div>
</form>
</div>
<?php include 'includes/footer.php'; ?>

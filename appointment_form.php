<?php
require 'config.php';
$active = 'appointments';
$id = (int)($_GET['id'] ?? 0);
$row = [];
if ($id) {
    $s = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
    $s->bind_param('i', $id); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) { flash('Appointment not found.', 'error'); header('Location: appointments.php'); exit; }
    $row['appointment_time'] = substr($row['appointment_time'], 0, 5);
}
$title = $id ? 'Edit appointment' : 'Book appointment';
$errors = [];
$pets = $conn->query("SELECT p.pet_id, p.pet_name, p.species, o.name AS owner_name FROM pets p JOIN owners o ON p.owner_id=o.owner_id ORDER BY p.pet_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = (int)($_POST['pet_id'] ?? 0); $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? ''; $vet = trim($_POST['vet_name'] ?? '');
    $reason = trim($_POST['reason'] ?? ''); $status = $_POST['status'] ?? 'Scheduled';

    $chk = $conn->prepare("SELECT 1 FROM pets WHERE pet_id=?"); $chk->bind_param('i', $pet_id); $chk->execute();
    if (!$chk->get_result()->num_rows) $errors['pet_id'] = 'Choose a pet.';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) $errors['appointment_date'] = 'Enter a valid date.';
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) $errors['appointment_time'] = 'Enter a valid time.';
    if (strlen($vet) < 3) $errors['vet_name'] = 'Vet name must be at least 3 characters.';
    if (strlen($reason) < 3) $errors['reason'] = 'Describe the reason for the visit.';
    if (!in_array($status, ['Scheduled','Completed','Cancelled'], true)) $errors['status'] = 'Choose a status.';

    if (!$errors && $status === 'Scheduled') {   // block double-booking of the same vet and slot
        $c = $conn->prepare("SELECT 1 FROM appointments WHERE vet_name=? AND appointment_date=? AND appointment_time=? AND status='Scheduled' AND appointment_id<>?");
        $c->bind_param('sssi', $vet, $date, $time, $id); $c->execute();
        if ($c->get_result()->num_rows) $errors['appointment_time'] = $vet . ' already has an appointment at this time.';
    }
    if (!$errors) {
        if ($id) {
            $s = $conn->prepare("UPDATE appointments SET pet_id=?, appointment_date=?, appointment_time=?, vet_name=?, reason=?, status=? WHERE appointment_id=?");
            $s->bind_param('isssssi', $pet_id, $date, $time, $vet, $reason, $status, $id);
            $msg = 'Appointment updated.';
        } else {
            $s = $conn->prepare("INSERT INTO appointments (pet_id, appointment_date, appointment_time, vet_name, reason, status) VALUES (?,?,?,?,?,?)");
            $s->bind_param('isssss', $pet_id, $date, $time, $vet, $reason, $status);
            $msg = 'Appointment booked.';
        }
        $s->execute();
        flash($msg); header('Location: appointments.php'); exit;
    }
}
function err($errors, $k) { return isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : ''; }
include 'includes/header.php';
?>
<h1><?= $title ?></h1>
<p class="sub">Choose the pet, the vet and a time slot.</p>
<div class="card">
<form class="validate form" method="post">
  <div><label for="pet_id">Pet</label>
    <select id="pet_id" name="pet_id" required>
      <option value="">Select pet</option>
      <?php while ($p = $pets->fetch_assoc()): ?>
        <option value="<?= $p['pet_id'] ?>" <?= selected($_POST['pet_id'] ?? $row['pet_id'] ?? '', $p['pet_id']) ?>><?= e($p['pet_name']) ?> (<?= e($p['species']) ?>) - <?= e($p['owner_name']) ?></option>
      <?php endwhile; ?>
    </select><?= err($errors, 'pet_id') ?></div>
  <div><label for="vet_name">Veterinarian</label>
    <input id="vet_name" name="vet_name" required minlength="3" value="<?= old('vet_name', $row) ?>"><?= err($errors, 'vet_name') ?></div>
  <div><label for="appointment_date">Date</label>
    <input id="appointment_date" type="date" name="appointment_date" required value="<?= old('appointment_date', $row) ?>"><?= err($errors, 'appointment_date') ?></div>
  <div><label for="appointment_time">Time</label>
    <input id="appointment_time" type="time" name="appointment_time" required value="<?= old('appointment_time', $row) ?>"><?= err($errors, 'appointment_time') ?></div>
  <div><label for="reason">Reason for visit</label>
    <input id="reason" name="reason" required value="<?= old('reason', $row) ?>"><?= err($errors, 'reason') ?></div>
  <div><label for="status">Status</label>
    <select id="status" name="status" required>
      <?php foreach (['Scheduled','Completed','Cancelled'] as $s): ?><option <?= selected($_POST['status'] ?? $row['status'] ?? 'Scheduled', $s) ?>><?= $s ?></option><?php endforeach; ?>
    </select><?= err($errors, 'status') ?></div>
  <div class="full actions">
    <button class="btn sun" type="submit"><?= $id ? 'Save changes' : 'Book appointment' ?></button>
    <a class="btn grey" href="appointments.php">Cancel</a>
  </div>
</form>
</div>
<?php include 'includes/footer.php'; ?>

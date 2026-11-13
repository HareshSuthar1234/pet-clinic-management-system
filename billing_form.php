<?php
require 'config.php';
$active = 'billing';
$id = (int)($_GET['id'] ?? 0);
$row = [];
if ($id) {
    $s = $conn->prepare("SELECT * FROM billing WHERE bill_id = ?");
    $s->bind_param('i', $id); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) { flash('Bill not found.', 'error'); header('Location: billing.php'); exit; }
}
$title = $id ? 'Edit bill' : 'Create bill';
$errors = [];
$appts = $conn->query("SELECT a.appointment_id, a.appointment_date, a.reason, p.pet_name, o.name AS owner_name
    FROM appointments a JOIN pets p ON a.pet_id=p.pet_id JOIN owners o ON p.owner_id=o.owner_id
    ORDER BY a.appointment_date DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aid = (int)($_POST['appointment_id'] ?? 0); $desc = trim($_POST['description'] ?? '');
    $amount = $_POST['amount'] ?? ''; $status = $_POST['status'] ?? 'Pending';
    $pdate = trim($_POST['payment_date'] ?? '');

    $chk = $conn->prepare("SELECT 1 FROM appointments WHERE appointment_id=?"); $chk->bind_param('i', $aid); $chk->execute();
    if (!$chk->get_result()->num_rows) $errors['appointment_id'] = 'Choose an appointment.';
    if (strlen($desc) < 3) $errors['description'] = 'Describe what the bill covers.';
    if (!is_numeric($amount) || $amount <= 0 || $amount > 9999999) $errors['amount'] = 'Enter an amount greater than 0.';
    if (!in_array($status, ['Paid','Pending'], true)) $errors['status'] = 'Choose a payment status.';
    if ($status === 'Paid' && $pdate === '') $pdate = date('Y-m-d');   // paid today if no date entered
    if ($status === 'Pending') $pdate = '';
    $pdateDb = $pdate === '' ? null : $pdate;
    if ($pdateDb !== null) {
        $d = DateTime::createFromFormat('Y-m-d', $pdateDb);
        if (!$d || $d->format('Y-m-d') !== $pdateDb) $errors['payment_date'] = 'Enter a valid date.';
    }

    if (!$errors) {
        $amount = (float)$amount;
        if ($id) {
            $s = $conn->prepare("UPDATE billing SET appointment_id=?, description=?, amount=?, payment_date=?, status=? WHERE bill_id=?");
            $s->bind_param('isdssi', $aid, $desc, $amount, $pdateDb, $status, $id);
            $msg = 'Bill updated.';
        } else {
            $s = $conn->prepare("INSERT INTO billing (appointment_id, description, amount, payment_date, status) VALUES (?,?,?,?,?)");
            $s->bind_param('isdss', $aid, $desc, $amount, $pdateDb, $status);
            $msg = 'Bill created.';
        }
        $s->execute();
        flash($msg); header('Location: billing.php'); exit;
    }
}
function err($errors, $k) { return isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : ''; }
include 'includes/header.php';
?>
<h1><?= $title ?></h1>
<p class="sub">Attach the bill to the visit it belongs to. Leave the paid date empty to use today.</p>
<div class="card">
<form class="validate form" method="post">
  <div class="full"><label for="appointment_id">Appointment</label>
    <select id="appointment_id" name="appointment_id" required>
      <option value="">Select appointment</option>
      <?php while ($a = $appts->fetch_assoc()): ?>
        <option value="<?= $a['appointment_id'] ?>" <?= selected($_POST['appointment_id'] ?? $row['appointment_id'] ?? '', $a['appointment_id']) ?>>#<?= $a['appointment_id'] ?> - <?= e($a['pet_name']) ?> (<?= e($a['owner_name']) ?>) - <?= e(date('d M Y', strtotime($a['appointment_date']))) ?> - <?= e($a['reason']) ?></option>
      <?php endwhile; ?>
    </select><?= err($errors, 'appointment_id') ?></div>
  <div><label for="description">Description</label>
    <input id="description" name="description" required value="<?= old('description', $row) ?>"><?= err($errors, 'description') ?></div>
  <div><label for="amount">Amount (&#8377;)</label>
    <input id="amount" type="number" step="0.01" min="1" max="9999999" name="amount" required value="<?= old('amount', $row) ?>"><?= err($errors, 'amount') ?></div>
  <div><label for="status">Payment status</label>
    <select id="status" name="status" required>
      <?php foreach (['Pending','Paid'] as $s): ?><option <?= selected($_POST['status'] ?? $row['status'] ?? 'Pending', $s) ?>><?= $s ?></option><?php endforeach; ?>
    </select><?= err($errors, 'status') ?></div>
  <div><label for="payment_date">Paid on (optional)</label>
    <input id="payment_date" type="date" name="payment_date" value="<?= old('payment_date', $row) ?>"><?= err($errors, 'payment_date') ?></div>
  <div class="full actions">
    <button class="btn sun" type="submit"><?= $id ? 'Save changes' : 'Create bill' ?></button>
    <a class="btn grey" href="billing.php">Cancel</a>
  </div>
</form>
</div>
<?php include 'includes/footer.php'; ?>

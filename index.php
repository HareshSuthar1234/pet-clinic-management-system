<?php
require 'config.php';
$title = 'Dashboard'; $active = 'index';

function one($conn, $sql) { return $conn->query($sql)->fetch_row()[0] ?? 0; }
$owners  = one($conn, "SELECT COUNT(*) FROM owners");
$pets    = one($conn, "SELECT COUNT(*) FROM pets");
$today   = one($conn, "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status='Scheduled'");
$pending = one($conn, "SELECT COALESCE(SUM(amount),0) FROM billing WHERE status='Pending'");

$todayList = $conn->query("SELECT a.*, p.pet_name, o.name AS owner_name FROM appointments a
    JOIN pets p ON a.pet_id=p.pet_id JOIN owners o ON p.owner_id=o.owner_id
    WHERE a.appointment_date >= CURDATE() AND a.status='Scheduled'
    ORDER BY a.appointment_date, a.appointment_time LIMIT 6");
$recent = $conn->query("SELECT p.pet_name, p.species, o.name AS owner_name FROM pets p
    JOIN owners o ON p.owner_id=o.owner_id ORDER BY p.pet_id DESC LIMIT 5");

include 'includes/header.php';
?>
<h1>Dashboard</h1>
<p class="sub">Today at the clinic, <?= date('l, d F Y') ?></p>

<div class="stats">
  <div class="stat"><b><?= $owners ?></b><span>Registered owners</span></div>
  <div class="stat"><b><?= $pets ?></b><span>Pets on record</span></div>
  <div class="stat sun"><b><?= $today ?></b><span>Appointments today</span></div>
  <div class="stat sun"><b>&#8377;<?= number_format($pending, 2) ?></b><span>Bills pending</span></div>
</div>

<div class="card no-print">
  <h2>Quick actions</h2>
  <a class="btn" href="owner_form.php">+ Add owner</a>
  <a class="btn" href="pet_form.php">+ Add pet</a>
  <a class="btn sun" href="appointment_form.php">+ Book appointment</a>
  <a class="btn grey" href="billing_form.php">+ Create bill</a>
</div>

<div class="grid2">
  <div class="card">
    <h2>Upcoming appointments</h2>
    <div class="wrap"><table>
      <tr><th>When</th><th>Pet</th><th>Owner</th><th>Vet</th></tr>
      <?php if ($todayList->num_rows === 0): ?><tr><td colspan="4" class="empty">No upcoming appointments. Book one to get started.</td></tr><?php endif; ?>
      <?php while ($r = $todayList->fetch_assoc()): ?>
        <tr><td><?= e(date('d M', strtotime($r['appointment_date']))) ?>, <?= e(date('h:i A', strtotime($r['appointment_time']))) ?></td>
            <td><?= e($r['pet_name']) ?></td><td><?= e($r['owner_name']) ?></td><td><?= e($r['vet_name']) ?></td></tr>
      <?php endwhile; ?>
    </table></div>
  </div>
  <div class="card">
    <h2>Recently registered pets</h2>
    <div class="wrap"><table>
      <tr><th>Pet</th><th>Species</th><th>Owner</th></tr>
      <?php if ($recent->num_rows === 0): ?><tr><td colspan="3" class="empty">No pets registered yet.</td></tr><?php endif; ?>
      <?php while ($r = $recent->fetch_assoc()): ?>
        <tr><td><?= e($r['pet_name']) ?></td><td><?= e($r['species']) ?></td><td><?= e($r['owner_name']) ?></td></tr>
      <?php endwhile; ?>
    </table></div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>

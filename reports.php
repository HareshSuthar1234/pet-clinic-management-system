<?php
require 'config.php';
$title = 'Reports'; $active = 'reports';

$rev = $conn->query("SELECT COALESCE(SUM(CASE WHEN status='Paid' THEN amount END),0) AS paid,
                            COALESCE(SUM(CASE WHEN status='Pending' THEN amount END),0) AS pending FROM billing")->fetch_assoc();
$bySpecies = $conn->query("SELECT species, COUNT(*) c FROM pets GROUP BY species ORDER BY c DESC");
$byStatus  = $conn->query("SELECT status, COUNT(*) c FROM appointments GROUP BY status");
$byVet     = $conn->query("SELECT vet_name, COUNT(*) c FROM appointments GROUP BY vet_name ORDER BY c DESC");
$owners    = $conn->query("SELECT o.name, o.phone, COUNT(DISTINCT p.pet_id) pets, COALESCE(SUM(b.amount),0) billed
    FROM owners o LEFT JOIN pets p ON p.owner_id=o.owner_id
    LEFT JOIN appointments a ON a.pet_id=p.pet_id LEFT JOIN billing b ON b.appointment_id=a.appointment_id
    GROUP BY o.owner_id ORDER BY billed DESC");
$unpaid = $conn->query("SELECT b.bill_id, b.amount, b.description, p.pet_name, o.name owner_name, o.phone
    FROM billing b JOIN appointments a ON b.appointment_id=a.appointment_id
    JOIN pets p ON a.pet_id=p.pet_id JOIN owners o ON p.owner_id=o.owner_id
    WHERE b.status='Pending' ORDER BY b.amount DESC");

// small helper that draws a bar list
function bars($res, $label) {
    $data = []; $max = 1;
    while ($r = $res->fetch_assoc()) { $data[] = $r; $max = max($max, $r['c']); }
    if (!$data) { echo '<p class="empty">No data yet.</p>'; return; }
    foreach ($data as $r) {
        echo '<div class="bar-row"><span>' . e($r[$label]) . '</span><i style="width:' . round($r['c'] / $max * 100) . '%"></i><em>' . $r['c'] . '</em></div>';
    }
}
include 'includes/header.php';
?>
<div class="bar">
  <div><h1>Reports &amp; summary</h1><p class="sub" style="margin:0">Clinic activity and revenue</p></div>
  <button class="btn grey no-print" onclick="window.print()">Print report</button>
</div>

<div class="stats">
  <div class="stat"><b>&#8377;<?= number_format($rev['paid'], 2) ?></b><span>Revenue collected</span></div>
  <div class="stat sun"><b>&#8377;<?= number_format($rev['pending'], 2) ?></b><span>Still to collect</span></div>
</div>

<div class="grid2">
  <div class="card"><h2>Pets by species</h2><?php bars($bySpecies, 'species'); ?></div>
  <div class="card"><h2>Appointments by status</h2><?php bars($byStatus, 'status'); ?></div>
  <div class="card"><h2>Appointments per vet</h2><?php bars($byVet, 'vet_name'); ?></div>
</div>

<div class="card wrap">
  <h2>Owner summary</h2>
  <table><tr><th>Owner</th><th>Phone</th><th>Pets</th><th>Total billed</th></tr>
  <?php while ($r = $owners->fetch_assoc()): ?>
    <tr><td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td><td><?= $r['pets'] ?></td><td>&#8377;<?= number_format($r['billed'], 2) ?></td></tr>
  <?php endwhile; ?></table>
</div>

<div class="card wrap">
  <h2>Pending payments</h2>
  <table><tr><th>Bill</th><th>Owner</th><th>Phone</th><th>Pet</th><th>For</th><th>Amount</th></tr>
  <?php if ($unpaid->num_rows === 0): ?><tr><td colspan="6" class="empty">All bills are paid.</td></tr><?php endif; ?>
  <?php while ($r = $unpaid->fetch_assoc()): ?>
    <tr><td>#<?= $r['bill_id'] ?></td><td><?= e($r['owner_name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['pet_name']) ?></td><td><?= e($r['description']) ?></td><td>&#8377;<?= number_format($r['amount'], 2) ?></td></tr>
  <?php endwhile; ?></table>
</div>
<?php include 'includes/footer.php'; ?>

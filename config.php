<?php
// Database connection + small helper functions used by every page
session_start();

$DB_HOST = 'localhost';
$DB_USER = 'root';   // XAMPP default
$DB_PASS = '';       // XAMPP default (empty)
$DB_NAME = 'pet_clinic';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed. Start MySQL in XAMPP and import database/pet_clinic.sql. (' . $conn->connect_error . ')');
}
$conn->set_charset('utf8mb4');

// Escape output (prevents XSS)
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Value for a form field: submitted value first, then database row, else blank
function old($key, $row = []) { return e($_POST[$key] ?? $row[$key] ?? ''); }

// One-time message shown after redirect
function flash($msg, $type = 'success') { $_SESSION['flash'] = [$msg, $type]; }

function selected($a, $b) { return (string)$a === (string)$b ? 'selected' : ''; }

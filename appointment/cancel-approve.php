<?php
session_start(); // Add this at the top
require '../db.php';

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !in_array($action, ['approve', 'reject'])) {
    hheader("Location: http://localhost/purrfect-paws/dashboard/home_dashboard/home.php&cancelled=1");
    exit;
}

if ($action === 'approve') {
    // ✅ Approve cancellation
    $stmt = $mysqli->prepare("UPDATE appointments 
        SET cancel_approved = 1, cancel_requested = 0, status = 'cancelled' 
        WHERE appointment_id = ?");
    $_SESSION['cancel_flash'] = '✅ Cancellation approved.';
} else {
    // ❌ Reject cancellation
    $stmt = $mysqli->prepare("UPDATE appointments 
        SET cancel_approved = 0, cancel_requested = 0 
        WHERE appointment_id = ?");
    $_SESSION['cancel_flash'] = '❌ Cancellation rejected.';
}

$stmt->bind_param("i", $id);
$stmt->execute();

// Redirect without query string
header("Location: http://localhost/purrfect-paws/dashboard/home_dashboard/home.php?show=appointments&cancelled=1");
exit;

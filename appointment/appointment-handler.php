<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login/loginform.php");
        exit;
    }

    // Collect and sanitize inputs
    $user_id = $_SESSION['user_id'];
    $pet_id = isset($_POST['pet_id']) ? intval($_POST['pet_id']) : null;
    $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : null;
    $recommended_package = trim($_POST['recommended_package'] ?? '');
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $groomer_name = trim($_POST['groomer_name'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Validate required fields
    if (!$pet_id || !$package_id || !$appointment_date) {
        $_SESSION['error'] = "⚠️ Please complete all required fields.";
        header("Location: book-appointment.php?pet_id=" . urlencode($pet_id));
        exit;
    }

    // Check if the pet belongs to the logged-in user
    $check_pet_stmt = $mysqli->prepare("SELECT 1 FROM pets WHERE pet_id = ? AND user_id = ?");
    $check_pet_stmt->bind_param("ii", $pet_id, $user_id);
    $check_pet_stmt->execute();
    $check_pet_stmt->store_result();

    if ($check_pet_stmt->num_rows === 0) {
        $_SESSION['error'] = "⚠️ Invalid pet or unauthorized access.";
        header("Location: book-appointment.php");
        exit;
    }

    // Insert appointment into database
    $stmt = $mysqli->prepare("INSERT INTO appointments 
        (user_id, pet_id, package_id, appointment_date, groomer_name, notes, recommended_package) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $user_id, $pet_id, $package_id, $appointment_date, $groomer_name, $notes, $recommended_package);

    if ($stmt->execute()) {
        $appointment_id = $stmt->insert_id;

        // Run Python script for recommendation (optional, for logging or analysis)
        $pythonPath = "C:\\Users\\Ivan\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
        $scriptPath = "E:\\xampp\\htdocs\\Purrfect-paws\\recommendation\\recommend.py";
        $command = "\"$pythonPath\" \"$scriptPath\" 2>&1";
        $output = shell_exec($command);
        file_put_contents(__DIR__ . '/recommendation_log.txt', $output); // optional logging

        // Redirect to appointment confirmation page
        $_SESSION['success'] = "✅ Appointment booked successfully!";
        header("Location: ../homepage/appointments.php?appointment_id=$appointment_id");
        exit;
    } else {
        $_SESSION['error'] = "❌ Database error: " . $stmt->error;
        header("Location: book-appointment.php?pet_id=" . urlencode($pet_id));
        exit;
    }

} else {
    echo "Invalid request.";
}

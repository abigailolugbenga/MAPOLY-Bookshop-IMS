<?php
/**
 * Run this ONCE in your browser to change admin1's password, then DELETE this file.
 * Never leave a script like this on a live/production server.
 */
   require __DIR__ . '/../includes/db.php';

$username = 'Administrator';
$newPassword = 'Abigail@Kunle';   // <-- change this line before running

$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('UPDATE tbl_user SET password = ? WHERE username = ?');
$stmt->execute([$hash, $username]);

if ($stmt->rowCount() > 0) {
    echo "Password updated for $username.<br>";
    echo "New password: " . htmlspecialchars($newPassword) . "<br>";
    echo "<strong>Delete this file (change_password.php) now.</strong>";
} else {
    echo "No user found with username '$username'. Nothing was changed.";
}
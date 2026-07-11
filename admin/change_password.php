<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$db_file = __DIR__ . '/streamhub.sqlite';
$pdo = new PDO('sqlite:' . $db_file);
$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // Fetch the current admin user (assuming username is 'Sohag')
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = 'Sohag'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($old_pass, $user['password_hash'])) {
        if ($new_pass === $confirm_pass) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE username = 'Sohag'");
            $update->execute([':hash' => $new_hash]);
            $message = "Password updated successfully!";
            $msgType = 'success';
        } else {
            $message = "New passwords do not match.";
            $msgType = 'error';
        }
    } else {
        $message = "Current password is incorrect.";
        $msgType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background-color: #050505; color: #e2e8f0; font-family: 'Inter', sans-serif; }</style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-neutral-900 border border-white/10 p-8 rounded-2xl shadow-2xl">
        <h2 class="text-2xl font-bold text-white mb-6">Security Settings</h2>
        
        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-lg text-sm <?php echo $msgType === 'success' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="password" name="old_password" placeholder="Current Password" required class="w-full bg-neutral-950 p-3 rounded-lg border border-white/10 text-white">
            <input type="password" name="new_password" placeholder="New Password" required class="w-full bg-neutral-950 p-3 rounded-lg border border-white/10 text-white">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required class="w-full bg-neutral-950 p-3 rounded-lg border border-white/10 text-white">
            <div class="flex gap-3">
                <a href="admin.php" class="flex-1 text-center bg-neutral-800 py-3 rounded-lg text-sm font-bold">Back</a>
                <button type="submit" class="flex-1 bg-indigo-600 py-3 rounded-lg text-sm font-bold">Update Password</button>
            </div>
        </form>
    </div>
</body>
</html>
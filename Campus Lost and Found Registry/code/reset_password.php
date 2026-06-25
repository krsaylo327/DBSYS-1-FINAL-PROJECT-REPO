<?php
// ============================================================
//  reset_password.php — TEMPORARY admin utility
//  Lets you set a known password for any user in `users`,
//  properly hashed with password_hash(), so you can actually
//  log in and test the seeded accounts from campus_lost_found.sql.
//
//  ⚠ DELETE THIS FILE once you're done testing. Anyone who can
//  reach this URL can change ANY account's password — it has
//  no auth check on purpose, so you can use it before login works.
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php'; // reuses your existing $conn

$message    = '';
$message_ok = false;

// Pull the user list to populate the dropdown
$users = [];
$res = $conn->query("SELECT user_id, full_name, email, role FROM users ORDER BY role, full_name");
if ($res) { $users = $res->fetch_all(MYSQLI_ASSOC); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id      = (int)($_POST['user_id'] ?? 0);
    $new_password = trim($_POST['new_password'] ?? '');

    if ($user_id <= 0 || $new_password === '') {
        $message = 'Pick a user and enter a new password.';
    } elseif (strlen($new_password) < 4) {
        $message = 'Password should be at least 4 characters.';
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param('si', $hash, $user_id);
        if ($stmt->execute()) {
            $message    = "Password updated. You can now log in with that account using the password you just set.";
            $message_ok = true;
        } else {
            $message = 'Database error: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Test Password (temporary)</title>
<style>
  body { font-family: system-ui, sans-serif; max-width: 560px; margin: 2rem auto; padding: 0 1rem; color: #1e293b; }
  h1 { font-size: 1.25rem; }
  .warn { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .85rem; }
  .ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .88rem; }
  .err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .88rem; }
  form { display: flex; flex-direction: column; gap: .85rem; }
  select, input { padding: .6rem .75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: .9rem; }
  button { padding: .65rem 1rem; background: #1a56e8; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: .9rem; }
  label { font-size: .82rem; font-weight: 600; color: #475569; }
</style>
</head>
<body>

<h1>Reset Test Account Password</h1>
<div class="warn">⚠ Temporary tool — anyone with this URL can change any account's password. Delete this file once your test accounts are working.</div>

<?php if ($message): ?>
  <div class="<?= $message_ok ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">
  <label>Account
    <select name="user_id" required>
      <option value="">— Select a user —</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= (int)$u['user_id'] ?>">
          <?= htmlspecialchars($u['full_name']) ?> — <?= htmlspecialchars($u['email']) ?> (<?= htmlspecialchars($u['role']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>New password
    <input type="text" name="new_password" placeholder="e.g. Student123!" required>
  </label>

  <button type="submit">Set Password</button>
</form>

</body>
</html>
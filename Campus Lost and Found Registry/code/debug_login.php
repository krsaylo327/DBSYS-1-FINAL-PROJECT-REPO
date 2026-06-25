<?php
// ============================================================
//  debug_login.php — TEMPORARY diagnostic tool
//  Upload this next to login.php, run it in your browser,
//  enter the failing student's email + password, and it will
//  tell you exactly why password_verify() is failing.
//
//  ⚠ DELETE THIS FILE when you're done. It is not safe to leave
//  on a live/shared server — it reveals whether an email exists
//  and shows password hash formats.
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php'; // reuses your existing $conn

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    $result = ['email_tested' => $email];

    $stmt = $conn->prepare("SELECT user_id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $result['step1_user_found'] = false;
        $result['diagnosis'] = "No row in `users` matches that exact email. Check for typos, "
            . "extra spaces, or a different casing/domain stored in the database "
            . "(e.g. trailing space, '@gmial.com', or the row simply doesn't exist).";
    } else {
        $result['step1_user_found'] = true;
        $result['stored_email']     = $row['email'];
        $result['stored_role']      = $row['role'];
        $result['user_id']          = $row['user_id'];

        $hash = $row['password'];
        $result['hash_length']  = strlen($hash);
        $result['hash_prefix']  = substr($hash, 0, 4);

        // A real password_hash() bcrypt hash always starts with $2y$ and is 60 chars
        $looks_like_bcrypt = (bool)preg_match('/^\$2[axy]\$/', $hash);
        $result['looks_like_valid_hash'] = $looks_like_bcrypt;

        if (!$looks_like_bcrypt) {
            $result['diagnosis'] = "The stored 'password' value is NOT a bcrypt hash "
                . "(it should start with \$2y\$ and be 60 characters). This means the student's "
                . "account was created with a plain-text password, a different hashing method, "
                . "or the password column was populated manually/by a SQL seed script instead of "
                . "PHP's password_hash(). THIS IS ALMOST CERTAINLY YOUR BUG. "
                . "Fix: re-hash the password properly (see the UPDATE query example below), "
                . "and make sure register.php (or wherever student accounts are created) calls "
                . "password_hash(\$password, PASSWORD_DEFAULT) before inserting.";
        } else {
            $verify_ok = password_verify($password, $hash);
            $result['step2_password_verify'] = $verify_ok;
            if ($verify_ok) {
                $result['diagnosis'] = "password_verify() succeeded — this login should actually work. "
                    . "If it still fails in login.php, double check there isn't a leftover session, "
                    . "a redirect loop, or a typo in the live email/password being submitted.";
            } else {
                $result['diagnosis'] = "The hash format looks valid, but password_verify() failed — "
                    . "the password you typed here doesn't match what's stored for this user. "
                    . "The student may be mistyping it, or the account was given a different "
                    . "password than they think (reset it to be sure).";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login Diagnostic (temporary)</title>
<style>
  body { font-family: system-ui, sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; color: #1e293b; }
  h1 { font-size: 1.25rem; }
  .warn { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .85rem; }
  form { display: flex; flex-direction: column; gap: .75rem; max-width: 360px; margin-bottom: 2rem; }
  input { padding: .6rem .75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: .9rem; }
  button { padding: .6rem 1rem; background: #1a56e8; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: .9rem; }
  pre { background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 8px; font-size: .82rem; overflow-x: auto; white-space: pre-wrap; }
  .diag { background: #eff6ff; border: 1px solid #bfdbfe; padding: 1rem; border-radius: 8px; font-size: .88rem; line-height: 1.5; }
</style>
</head>
<body>

<h1>Login Diagnostic</h1>
<div class="warn">⚠ Temporary tool. Delete this file once you've found the bug — it's not safe to leave on a live server.</div>

<form method="POST">
  <label>Email
    <input type="text" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="student@gmail.com" required>
  </label>
  <label>Password (exactly what the student types)
    <input type="text" name="password" value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" required>
  </label>
  <button type="submit">Run Diagnostic</button>
</form>

<?php if ($result): ?>
  <div class="diag">
    <strong>Diagnosis:</strong><br>
    <?= htmlspecialchars($result['diagnosis']) ?>
  </div>
  <pre><?= htmlspecialchars(print_r($result, true)) ?></pre>
<?php endif; ?>

</body>
</html>

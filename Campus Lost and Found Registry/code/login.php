<?php
// ============================================================
//  login.php — Authentication Page
//  Campus Lost and Found Registry
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// db_connect.php handles session_start(), defines e(), require_login()
require_once 'db_connect.php';

// Redirect if already logged in ($_SESSION['user'] matches require_login())
if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email and password.';
    } else {
        $stmt = $conn->prepare(
            "SELECT user_id, full_name, password, role FROM users WHERE email = ? LIMIT 1"
        );
            if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row && password_verify($password, $row['password'])) {
                // Store as $_SESSION['user'] — matches require_login() in db_connect.php
                $_SESSION['user'] = [
                    'user_id'   => $row['user_id'],
                    'full_name' => $row['full_name'],
                    'role'      => $row['role'],
                ];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password. Please try again.';
            }
        } else {
            $error = 'A database error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In — trU-Access Campus Registry</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap">

<style>
/* ================================================================
   DESIGN TOKENS — mirrors index.php
   ================================================================ */
:root {
  --clf-primary       : #1a56e8;
  --clf-primary-hover : #1241c4;
  --clf-radius-sm     : 8px;
  --clf-transition    : .2s ease;
}

*, *::before, *::after { box-sizing: border-box; }

/* ================================================================
   ANIMATED GRADIENT BACKGROUND — formal deep navy
   ================================================================ */
@keyframes gradientPan {
  0%   { background-position: 0% 50%; }
  50%  { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
@keyframes orbDriftA {
  0%, 100% { transform: translate(0, 0); }
  40%       { transform: translate(50px, -40px); }
  70%       { transform: translate(-30px, 50px); }
}
@keyframes orbDriftB {
  0%, 100% { transform: translate(0, 0); }
  40%       { transform: translate(-60px, 35px); }
  70%       { transform: translate(40px, -50px); }
}
@keyframes riseUp {
  from { opacity: 0; transform: translateY(32px) scale(.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(-10px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes alertIn {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}

body {
  margin: 0;
  min-height: 100vh;
  font-family: 'DM Sans', sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  /*
   * Formal animated gradient: deep navy tones only.
   * Shifts between midnight blue shades — no purples, no bright colors.
   * background-size 400% gives the engine enough room to pan visibly.
   */
  background: linear-gradient(
    125deg,
    #04101f 0%,
    #0a1b38 20%,
    #0d2045 40%,
    #071628 55%,
    #0c1d40 72%,
    #071328 88%,
    #04101f 100%
  );
  background-size: 400% 400%;
  animation: gradientPan 20s ease infinite;
  position: relative;
  overflow: hidden;
}

/* Fine dot-grid texture layer */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: radial-gradient(circle, rgba(255,255,255,.042) 1px, transparent 1px);
  background-size: 28px 28px;
  pointer-events: none;
  z-index: 0;
}

/* Edge vignette for depth */
body::after {
  content: '';
  position: fixed;
  inset: 0;
  background: radial-gradient(ellipse at 50% 50%, transparent 45%, rgba(2,7,18,.65) 100%);
  pointer-events: none;
  z-index: 0;
}

/* Ambient glow blobs */
.bg-orb { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; }
.bg-orb-1 {
  width: 700px; height: 700px;
  background: radial-gradient(circle, rgba(26,86,232,.15) 0%, transparent 65%);
  top: -220px; left: -200px;
  animation: orbDriftA 26s ease-in-out infinite;
}
.bg-orb-2 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(12,36,80,.75) 0%, transparent 68%);
  bottom: -130px; right: -130px;
  animation: orbDriftB 32s ease-in-out infinite;
}
.bg-orb-3 {
  width: 380px; height: 380px;
  background: radial-gradient(circle, rgba(26,86,232,.08) 0%, transparent 70%);
  top: 35%; left: 55%;
  animation: orbDriftA 22s 6s ease-in-out infinite;
}

/* ================================================================
   PAGE LAYOUT
   ================================================================ */
.login-wrap {
  position: relative;
  z-index: 1;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.5rem;
  min-height: 100vh;
}

/* The two-panel card */
.login-shell {
  display: flex;
  width: 100%;
  max-width: 880px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow:
    0 44px 110px rgba(0,0,0,.6),
    0 0 0 1px rgba(255,255,255,.07);
  animation: riseUp .65s cubic-bezier(.22,.68,0,1.15) both;
}

/* ── Left: glass form panel ── */
.login-panel-left {
  flex: 0 0 420px;
  background: rgba(255,255,255,.055);
  backdrop-filter: blur(32px) saturate(1.6);
  -webkit-backdrop-filter: blur(32px) saturate(1.6);
  border-right: 1px solid rgba(255,255,255,.08);
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 3rem 2.75rem;
}

/* ── Right: info panel ── */
.login-panel-right {
  flex: 1;
  background: rgba(6,18,46,.52);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}
.login-panel-right::before {
  content: '';
  position: absolute; inset: 0;
  background-image: radial-gradient(circle, rgba(255,255,255,.046) 1px, transparent 1px);
  background-size: 22px 22px;
}
.panel-orb { position: absolute; border-radius: 50%; filter: blur(60px); opacity: .26; pointer-events: none; }
.panel-orb-1 { width: 280px; height: 280px; background: var(--clf-primary); top: -60px; right: -50px; }
.panel-orb-2 { width: 200px; height: 200px; background: #1e3a8a; bottom: 15px; left: 0; }

/* ================================================================
   BRAND BLOCK
   ================================================================ */
.brand-block {
  display: flex; align-items: center; gap: .65rem;
  margin-bottom: 2.4rem;
  animation: fadeUp .5s ease both;
}
.brand-icon {
  width: 38px; height: 38px; border-radius: 9px;
  background: var(--clf-primary);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.1rem; flex-shrink: 0;
  box-shadow: 0 4px 16px rgba(26,86,232,.45);
}
.brand-name       { font-size: 1.05rem; font-weight: 700; color: #dce8f8; line-height: 1.2; }
.brand-name small { display: block; font-size: .7rem; font-weight: 400; color: #607a9e; }

/* ================================================================
   HEADINGS
   ================================================================ */
.login-heading {
  font-size: 1.5rem; font-weight: 700; color: #ecf2fd;
  margin: 0 0 .3rem;
  animation: fadeUp .5s .07s ease both;
}
.login-sub {
  font-size: .84rem; color: #6888b2;
  margin: 0 0 1.85rem;
  animation: fadeUp .5s .13s ease both;
}
.form-wrap { animation: fadeUp .5s .19s ease both; }

/* ================================================================
   ALERT
   ================================================================ */
.clf-alert {
  display: flex; align-items: flex-start; gap: .55rem;
  padding: .7rem .95rem;
  border-radius: var(--clf-radius-sm);
  font-size: .82rem;
  margin-bottom: 1.2rem;
  animation: alertIn .3s ease;
}
.clf-alert.error {
  background: rgba(220,38,38,.13);
  border: 1px solid rgba(220,38,38,.3);
  color: #fca5a5;
}

/* ================================================================
   FORM FIELDS
   ================================================================ */
.clf-field { margin-bottom: 1.1rem; }
.clf-field label {
  display: block; font-size: .77rem; font-weight: 600;
  color: #9ab4d4; margin-bottom: .42rem; letter-spacing: .01em;
}
.clf-input-wrap { position: relative; }
.clf-input-wrap .input-icon {
  position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
  color: #45607e; font-size: .88rem; pointer-events: none;
}
.clf-input {
  width: 100%;
  padding: .68rem .9rem .68rem 2.35rem;
  font-size: .88rem; font-family: inherit;
  border: 1.5px solid rgba(255,255,255,.1);
  border-radius: var(--clf-radius-sm);
  background: rgba(255,255,255,.07);
  color: #e2eefa; outline: none;
  transition: border-color var(--clf-transition), box-shadow var(--clf-transition), background var(--clf-transition);
}
.clf-input::placeholder { color: #364e68; }
.clf-input:focus {
  border-color: rgba(26,86,232,.65);
  background: rgba(255,255,255,.105);
  box-shadow: 0 0 0 3px rgba(26,86,232,.16);
}
.clf-input.is-invalid { border-color: rgba(220,38,38,.55); }

/* Show/hide password */
.toggle-pw {
  position: absolute; right: .85rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; padding: 0;
  color: #45607e; cursor: pointer; font-size: .88rem; line-height: 1;
  transition: color var(--clf-transition);
}
.toggle-pw:hover { color: #7ab0ff; }

/* Forgot */
.forgot-row { display: flex; justify-content: flex-end; margin-top: -.4rem; margin-bottom: 1.2rem; }
.forgot-link { font-size: .77rem; color: #5588d8; text-decoration: none; font-weight: 500; }
.forgot-link:hover { color: #7ab0ff; text-decoration: underline; }

/* ================================================================
   SIGN-IN BUTTON
   ================================================================ */
.btn-signin {
  width: 100%; padding: .75rem;
  background: var(--clf-primary); color: #fff; border: none;
  border-radius: var(--clf-radius-sm);
  font-family: inherit; font-size: .9rem; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: .5rem;
  box-shadow: 0 4px 18px rgba(26,86,232,.32);
  transition: background var(--clf-transition), box-shadow var(--clf-transition), transform var(--clf-transition);
}
.btn-signin:hover {
  background: var(--clf-primary-hover);
  box-shadow: 0 6px 24px rgba(26,86,232,.44);
  transform: translateY(-1px);
}
.btn-signin:active { transform: translateY(0); }

/* ================================================================
   FOOTER
   ================================================================ */
.login-footer {
  margin-top: 1.4rem; text-align: center;
  font-size: .77rem; color: #45607e;
  animation: fadeUp .5s .25s ease both;
}
.login-footer a { color: #5588d8; text-decoration: none; font-weight: 500; }
.login-footer a:hover { color: #7ab0ff; text-decoration: underline; }

/* ================================================================
   RIGHT PANEL CARD
   ================================================================ */
.right-card {
  position: relative; z-index: 1;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.09);
  border-radius: 16px; padding: 2.2rem 1.9rem; max-width: 295px;
  backdrop-filter: blur(10px);
  animation: riseUp .9s .28s ease both;
}
.right-card h2  { color: #d8e6f8; font-size: 1.1rem; font-weight: 700; margin: 0 0 .4rem; }
.right-card p   { color: #607a9e; font-size: .8rem; line-height: 1.65; margin: 0 0 1.35rem; }
.feature-item   { display: flex; align-items: center; gap: .6rem; margin-bottom: .65rem; }
.feature-item .fi-icon {
  width: 30px; height: 30px; border-radius: 7px;
  background: rgba(26,86,232,.2); border: 1px solid rgba(26,86,232,.22);
  display: flex; align-items: center; justify-content: center;
  color: #7ab0ff; font-size: .8rem; flex-shrink: 0;
}
.feature-item span { color: #9abce0; font-size: .8rem; font-weight: 500; }

/* ================================================================
   RESPONSIVE
   ================================================================ */
@media (max-width: 768px) {
  .login-panel-right { display: none; }
  .login-panel-left  { flex: 1; padding: 2.5rem 1.75rem; border-right: none; }
  .login-shell { border-radius: 16px; max-width: 440px; }
}
</style>
</head>
<body>

<!-- Ambient background orbs — sit behind everything -->
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>
<div class="bg-orb bg-orb-3"></div>

<div class="login-wrap">
  <div class="login-shell">

    <!-- ══ LEFT: Glass Form Panel ════════════════════════════ -->
    <div class="login-panel-left">

      <div class="brand-block">
        <div class="brand-icon"><i class="bi bi-search-heart-fill"></i></div>
        <div class="brand-name">
          trU-Access
          <small>Campus Registry</small>
        </div>
      </div>

      <h1 class="login-heading">Welcome back</h1>
      <p class="login-sub">Sign in to access the Lost &amp; Found Registry.</p>

      <?php if ($error): ?>
      <div class="clf-alert error">
        <i class="bi bi-exclamation-circle-fill" style="margin-top:1px;flex-shrink:0;"></i>
        <?= e($error) ?>
      </div>
      <?php endif; ?>

      <div class="form-wrap">
        <form method="POST" action="login.php" novalidate>

          <div class="clf-field">
            <label for="email">Email address</label>
            <div class="clf-input-wrap">
              <i class="bi bi-envelope input-icon"></i>
              <input
                type="email" id="email" name="email"
                class="clf-input <?= $error ? 'is-invalid' : '' ?>"
                placeholder="you@university.edu"
                value="<?= e($_POST['email'] ?? '') ?>"
                autocomplete="email" required
              >
            </div>
          </div>

          <div class="clf-field">
            <label for="password">Password</label>
            <div class="clf-input-wrap">
              <i class="bi bi-lock input-icon"></i>
              <input
                type="password" id="password" name="password"
                class="clf-input <?= $error ? 'is-invalid' : '' ?>"
                placeholder="Enter your password"
                autocomplete="current-password" required
              >
              <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Toggle password visibility">
                <i class="bi bi-eye" id="pw-eye-icon"></i>
              </button>
            </div>
          </div>

          <div class="forgot-row">
            <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn-signin">
            <i class="bi bi-box-arrow-in-right"></i>
            Sign In
          </button>

        </form>
      </div>

      <div class="login-footer">
        Don't have an account? <a href="register.php">Request access</a>
      </div>

    </div><!-- /left panel -->

    <!-- ══ RIGHT: Info Panel ══════════════════════════════════ -->
    <div class="login-panel-right">
      <div class="panel-orb panel-orb-1"></div>
      <div class="panel-orb panel-orb-2"></div>

      <div class="right-card">
        <h2>Campus Lost &amp; Found</h2>
        <p>A centralized registry to help the campus community recover lost belongings quickly and securely.</p>

        <div class="feature-item">
          <div class="fi-icon"><i class="bi bi-search"></i></div>
          <span>Browse &amp; search reported items</span>
        </div>
        <div class="feature-item">
          <div class="fi-icon"><i class="bi bi-plus-circle"></i></div>
          <span>Report lost or found items</span>
        </div>
        <div class="feature-item">
          <div class="fi-icon"><i class="bi bi-shield-check"></i></div>
          <span>Verified claim workflow</span>
        </div>
        <div class="feature-item">
          <div class="fi-icon"><i class="bi bi-bell"></i></div>
          <span>Real-time status updates</span>
        </div>
      </div>
    </div><!-- /right panel -->

  </div><!-- /login-shell -->
</div><!-- /login-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
  const input = document.getElementById('password');
  const icon  = document.getElementById('pw-eye-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}
</script>
</body>
</html>
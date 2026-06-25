<?php
// ============================================================
//  claim_request.php — File a Claim (self-contained)
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

if (!function_exists('e')) {
    function e($val): string { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }
}

$user = require_login();
$claimant_id = 1; // TODO: replace with $_SESSION['user_id']

$errors  = [];
$success = false;

// ── Validate item_id from GET ─────────────────────────────
$item_id = isset($_GET['item_id']) && ctype_digit($_GET['item_id'])
           ? (int)$_GET['item_id'] : 0;

if ($item_id <= 0) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#dc2626;">
         Invalid or missing item ID. <a href="items_list.php">Go back</a></div>');
}

// ── Fetch the item (must be active + found) ───────────────
$stmt_item = $conn->prepare("
    SELECT
        i.item_id,
        i.item_name,
        i.description,
        i.photo_path,
        i.date_reported,
        i.status,
        i.item_type,
        c.category_name,
        l.building_name,
        l.room_or_area,
        u.full_name AS reporter
    FROM items i
    INNER JOIN categories c ON i.category_id = c.category_id
    INNER JOIN locations  l ON i.location_id  = l.location_id
    INNER JOIN users      u ON i.reported_by  = u.user_id
    WHERE i.item_id = ? AND i.status = 'active' AND i.item_type = 'found'
    LIMIT 1
");
$stmt_item->bind_param('i', $item_id);
$stmt_item->execute();
$item = $stmt_item->get_result()->fetch_assoc();
$stmt_item->close();

if (!$item) {
    die('<!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8"><title>Not Available</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    </head><body class="bg-light">
    <div class="container mt-5 text-center">
      <i class="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>
      <h4 class="mt-3">Item Not Available</h4>
      <p class="text-muted">This item is no longer active, or is not a found item.</p>
      <a href="items_list.php" class="btn btn-primary mt-2">Back to Items</a>
    </div>
    </body></html>');
}

// ── Check if user already filed a claim ───────────────────
$stmt_check = $conn->prepare("
    SELECT claim_id FROM claims
    WHERE item_id = ? AND claimant_id = ? AND status != 'rejected'
    LIMIT 1
");
$stmt_check->bind_param('ii', $item_id, $claimant_id);
$stmt_check->execute();
$already_claimed = $stmt_check->get_result()->num_rows > 0;
$stmt_check->close();

// ── Handle POST ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_claimed) {
    $proof        = trim($_POST['proof_description'] ?? '');
    $post_item_id = isset($_POST['item_id']) && ctype_digit($_POST['item_id'])
                    ? (int)$_POST['item_id'] : 0;

    if ($proof === '')
        $errors['proof'] = 'Please provide a description of your ownership proof.';
    elseif (mb_strlen($proof) < 20)
        $errors['proof'] = 'Proof description must be at least 20 characters.';
    elseif (mb_strlen($proof) > 1000)
        $errors['proof'] = 'Proof description must not exceed 1000 characters.';

    if ($post_item_id !== $item_id)
        $errors['general'] = 'Form mismatch. Please try again.';

    // ── Optional proof photo upload ───────────────────────
    $proof_photo_path = null;
    if (isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['proof_photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['proof_photo'] = 'File upload error.';
        } else {
            $finfo         = new finfo(FILEINFO_MIME_TYPE);
            $mime          = $finfo->file($file['tmp_name']);
            $allowed_types = ['image/jpeg','image/png','image/webp','image/gif'];
            if (!in_array($mime, $allowed_types)) {
                $errors['proof_photo'] = 'Only JPG, PNG, WEBP, or GIF images allowed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors['proof_photo'] = 'File must not exceed 5 MB.';
            } else {
                $upload_dir = 'uploads/proofs/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $safe_name = uniqid('proof_', true) . '.' . $ext;
                $dest      = $upload_dir . $safe_name;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $proof_photo_path = $dest;
                } else {
                    $errors['proof_photo'] = 'Could not save the uploaded file.';
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt_ins = $conn->prepare("
            INSERT INTO claims (item_id, claimant_id, proof_description, proof_photo_path, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt_ins->bind_param('iiss', $item_id, $claimant_id, $proof, $proof_photo_path);
        if ($stmt_ins->execute()) {
            $success        = true;
            $already_claimed = true;
        } else {
            $errors['db'] = 'Database error. Please try again.';
        }
        $stmt_ins->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>File a Claim — trU-Access Campus Registry</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap">
<style>
:root {
  --clf-primary      : #1a56e8;
  --clf-primary-hover: #1241c4;
  --clf-sidebar-bg   : #0d1b38;
  --clf-sidebar-mid  : #1a3060;
  --clf-sidebar-txt  : #8fa3c4;
  --clf-sidebar-w    : 240px;
  --clf-bg           : #eef2f9;
  --clf-surface      : #ffffff;
  --clf-border       : #dde3ef;
  --clf-muted        : #64748b;
  --clf-radius       : 12px;
  --clf-radius-sm    : 8px;
  --clf-shadow       : 0 2px 12px rgba(15,23,42,.07);
}
*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0; background: var(--clf-bg);
  color: #1e293b; font-family: 'DM Sans', sans-serif; min-height: 100vh;
}
.clf-sidebar {
  position: fixed; inset: 0 auto 0 0;
  width: var(--clf-sidebar-w); background: var(--clf-sidebar-bg);
  padding: 1.75rem 1rem; z-index: 200; display: flex; flex-direction: column;
}
.clf-sidebar .brand {
  display: flex; align-items: center; gap: .6rem;
  color: #fff; font-size: 1rem; font-weight: 700;
  margin-bottom: 2.25rem; text-decoration: none; line-height: 1.3;
}
.clf-sidebar .brand small {
  display: block; font-size: .7rem; font-weight: 400;
  color: var(--clf-sidebar-txt); margin-top: 1px;
}
.clf-sidebar .brand-icon {
  width: 34px; height: 34px; border-radius: 8px; background: var(--clf-primary);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; font-size: 1rem; color: #fff;
}
.clf-nav-label {
  font-size: .65rem; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: #445a78;
  padding: .5rem .75rem .3rem; margin-top: .75rem;
}
.clf-nav-link {
  display: flex; align-items: center; gap: .6rem;
  color: var(--clf-sidebar-txt); font-size: .85rem; font-weight: 500;
  padding: .55rem .85rem; border-radius: var(--clf-radius-sm);
  margin-bottom: .1rem; text-decoration: none;
  transition: background .2s, color .2s;
}
.clf-nav-link i { font-size: .95rem; flex-shrink: 0; }
.clf-nav-link:hover, .clf-nav-link.active { background: var(--clf-sidebar-mid); color: #fff; }
.clf-main { margin-left: var(--clf-sidebar-w); padding: 2rem 2.25rem; min-height: 100vh; }
.page-header { margin-bottom: 1.75rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 .25rem; }
.page-header p  { font-size: .85rem; color: var(--clf-muted); margin: 0; }
.clf-card {
  background: var(--clf-surface); border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius); box-shadow: var(--clf-shadow); overflow: hidden;
}
.clf-card-header {
  padding: 1rem 1.5rem; border-bottom: 1px solid var(--clf-border);
  font-weight: 600; font-size: .95rem; color: #0f172a; background: #fafbfd;
}
.clf-card-body { padding: 1.5rem; }
.clf-badge {
  display: inline-flex; align-items: center;
  font-size: .72rem; font-weight: 700; letter-spacing: .03em;
  text-transform: uppercase; padding: .22rem .65rem; border-radius: 999px; line-height: 1;
}
.clf-badge.found { background: #f0fdf4; color: #16a34a; }
.clf-badge.lost  { background: #fef2f2; color: #dc2626; }
.clf-badge.active{ background: #eff6ff; color: #1a56e8; }
.form-label { font-weight: 600; font-size: .85rem; color: #374151; margin-bottom: .4rem; }
.form-control, .form-select {
  border: 1.5px solid var(--clf-border); border-radius: var(--clf-radius-sm);
  font-family: 'DM Sans', sans-serif; font-size: .875rem; color: #0f172a;
  background: #f8fafc; padding: .52rem .85rem;
  transition: border-color .2s, box-shadow .2s;
}
.form-control:focus { border-color: var(--clf-primary); background: #fff; box-shadow: 0 0 0 3px rgba(26,86,232,.12); outline: none; }
.form-control.is-invalid { border-color: #dc2626; }
.invalid-feedback { font-size: .78rem; color: #dc2626; margin-top: .25rem; }
.btn-clf-primary {
  background: var(--clf-primary); color: #fff; border: none;
  border-radius: var(--clf-radius-sm); font-family: 'DM Sans', sans-serif;
  font-size: .875rem; font-weight: 600; padding: .6rem 1.5rem; cursor: pointer;
  transition: background .2s, box-shadow .2s;
}
.btn-clf-primary:hover { background: var(--clf-primary-hover); box-shadow: 0 4px 12px rgba(26,86,232,.3); }
.btn-clf-secondary {
  background: transparent; color: var(--clf-muted); border: 1.5px solid var(--clf-border);
  border-radius: var(--clf-radius-sm); font-family: 'DM Sans', sans-serif;
  font-size: .875rem; font-weight: 600; padding: .6rem 1.5rem;
  text-decoration: none; display: inline-block; transition: border-color .2s, color .2s;
}
.btn-clf-secondary:hover { border-color: #94a3b8; color: #334155; }
.item-thumb { width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem; }
.thumb-placeholder {
  width: 100%; height: 160px; background: #e2e8f0; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #94a3b8; font-size: 2rem; margin-bottom: 1rem;
}
.item-meta { font-size: .8rem; color: var(--clf-muted); display: flex; flex-direction: column; gap: .3rem; }
.item-meta span { display: flex; align-items: center; gap: .4rem; }
.proof-counter { font-size: .78rem; color: var(--clf-muted); }
.clf-alert-success {
  background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--clf-radius-sm);
  padding: 1rem 1.25rem; color: #166534; font-size: .875rem; font-weight: 500;
  display: flex; align-items: center; gap: .6rem; margin-bottom: 1.25rem;
}
.clf-alert-warning {
  background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--clf-radius-sm);
  padding: 1rem 1.25rem; color: #92400e; font-size: .875rem; font-weight: 500;
  display: flex; align-items: center; gap: .6rem; margin-bottom: 1.25rem;
}
/* Sidebar footer */
.clf-sidebar-spacer { flex: 1; }
.clf-sidebar-footer { border-top: 1px solid rgba(255,255,255,.08); padding-top: 1rem; margin-top: .5rem; }
.clf-user-pill { display: flex; align-items: center; gap: .6rem; padding: .45rem .6rem; border-radius: var(--clf-radius-sm); margin-bottom: .4rem; }
.clf-user-avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg,#1a56e8,#4f8eff); display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.clf-user-name { font-size: .78rem; font-weight: 600; color: #c8d9f5; line-height: 1.2; }
.clf-user-role { font-size: .68rem; color: var(--clf-sidebar-txt); }
.clf-logout-btn { display: flex; align-items: center; gap: .6rem; width: 100%; padding: .55rem .85rem; border-radius: var(--clf-radius-sm); background: none; border: none; cursor: pointer; color: var(--clf-sidebar-txt); font-size: .85rem; font-weight: 500; font-family: 'DM Sans', sans-serif; transition: background .2s, color .2s; text-decoration: none; }
.clf-logout-btn:hover { background: rgba(239,68,68,.12); color: #f87171; }
.clf-logout-btn i { font-size: .95rem; flex-shrink: 0; }

@media (max-width: 991.98px) {
  :root { --clf-sidebar-w: 0px; }
  .clf-sidebar { display: none; }
  .clf-main { margin-left: 0; padding: 1.25rem; }
}
</style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<aside class="clf-sidebar">
  <a href="index.php" class="brand">
    <div class="brand-icon"><i class="bi bi-search-heart-fill"></i></div>
    <div>trU-Access<small>Campus Registry</small></div>
  </a>
  <nav>
    <div class="clf-nav-label">Main</div>
    <a href="index.php"      class="clf-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="items_list.php" class="clf-nav-link active"><i class="bi bi-list-ul"></i> Browse Items</a>
    <a href="add_item.php"   class="clf-nav-link"><i class="bi bi-plus-circle"></i> Report Item</a>
    <?php if (is_admin_or_staff()): ?>
    <div class="clf-nav-label">Admin</div>
    <a href="admin_workflow.php" class="clf-nav-link"><i class="bi bi-shield-check"></i> Claims Workflow</a>
    <?php endif; ?>
  </nav>

  <div class="clf-sidebar-spacer"></div>

  <div class="clf-sidebar-footer">
    <?php
      $current_user = $_SESSION['user'] ?? [];
      $initials = '';
      if (!empty($current_user['full_name'])) {
        $parts    = explode(' ', trim($current_user['full_name']));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
      }
    ?>
    <div class="clf-user-pill">
      <div class="clf-user-avatar"><?= $initials ?: 'U' ?></div>
      <div>
        <div class="clf-user-name"><?= e($current_user['full_name'] ?? 'User') ?></div>
        <div class="clf-user-role"><?= ucfirst(e($current_user['role'] ?? '')) ?></div>
      </div>
    </div>
    <a href="logout.php" class="clf-logout-btn">
      <i class="bi bi-box-arrow-left"></i> Sign Out
    </a>
  </div>
</aside>

<!-- ═══ MAIN ═══ -->
<main class="clf-main">

  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="items_list.php" class="btn-clf-secondary d-inline-flex align-items-center gap-2">
      <i class="bi bi-arrow-left"></i> Back to Items
    </a>
    <?php if (is_admin_or_staff()): ?>
    <a href="edit_item.php?item_id=<?= $item_id ?>" class="btn-clf-secondary d-inline-flex align-items-center gap-2">
      <i class="bi bi-pencil-square"></i> Edit Item
    </a>
    <?php endif; ?>
  </div>

  <div class="row g-4">

    <!-- ── Left: Item Details ── -->
    <div class="col-md-5">
      <div class="clf-card h-100">
        <div class="clf-card-header">
          <i class="bi bi-tag-fill me-2 text-success"></i>Found Item Details
        </div>
        <div class="clf-card-body">

          <?php if (!empty($item['photo_path']) && file_exists($item['photo_path'])): ?>
            <img src="<?= e($item['photo_path']) ?>" alt="Item photo" class="item-thumb">
          <?php else: ?>
            <div class="thumb-placeholder"><i class="bi bi-image"></i></div>
          <?php endif; ?>

          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="clf-badge found">Found</span>
            <span class="clf-badge active"><?= e($item['status']) ?></span>
          </div>

          <h5 style="font-weight:700;font-size:1rem;margin:.5rem 0;">
            <?= e($item['item_name']) ?>
          </h5>
          <p style="font-size:.83rem;color:var(--clf-muted);line-height:1.6;">
            <?= nl2br(e($item['description'])) ?>
          </p>

          <div class="item-meta mt-3">
            <span><i class="bi bi-tag"></i><?= e($item['category_name']) ?></span>
            <span><i class="bi bi-geo-alt"></i><?= e($item['building_name'] . ' — ' . $item['room_or_area']) ?></span>
            <span><i class="bi bi-person"></i>Reported by <?= e($item['reporter']) ?></span>
            <span><i class="bi bi-calendar3"></i><?= e(date('F j, Y', strtotime($item['date_reported']))) ?></span>
          </div>

        </div>
      </div>
    </div>

    <!-- ── Right: Claim Form ── -->
    <div class="col-md-7">
      <div class="clf-card h-100">
        <div class="clf-card-header">
          <i class="bi bi-hand-index-thumb-fill me-2 text-primary"></i>File a Claim
        </div>
        <div class="clf-card-body">

          <p style="font-size:.85rem;color:var(--clf-muted);margin-bottom:1.5rem;">
            Describe how you can prove this item belongs to you — unique markings,
            what's inside, purchase details, etc. An admin will review and contact you.
          </p>

          <!-- Success -->
          <?php if ($success): ?>
            <div class="clf-alert-success">
              <i class="bi bi-check-circle-fill fs-5"></i>
              <div>
                <strong>Claim submitted!</strong><br>
                <span style="font-size:.82rem;">An admin will review your proof shortly.</span>
              </div>
            </div>
            <a href="items_list.php" class="btn-clf-secondary">
              <i class="bi bi-arrow-left me-1"></i>Back to Items
            </a>

          <!-- Already claimed -->
          <?php elseif ($already_claimed && !$success): ?>
            <div class="clf-alert-warning">
              <i class="bi bi-exclamation-triangle-fill fs-5"></i>
              <div>You have already filed a claim on this item. Please wait for admin review.</div>
            </div>

          <!-- Claim Form -->
          <?php else: ?>

            <?php if (!empty($errors['general'])): ?>
              <div class="alert alert-danger mb-3"><?= e($errors['general']) ?></div>
            <?php endif; ?>
            <?php if (!empty($errors['db'])): ?>
              <div class="alert alert-danger mb-3"><?= e($errors['db']) ?></div>
            <?php endif; ?>

            <form id="claimForm" method="POST"
                  action="claim_request.php?item_id=<?= $item_id ?>"
                  enctype="multipart/form-data" novalidate>

              <input type="hidden" name="item_id" value="<?= $item_id ?>">

              <!-- Proof description -->
              <div class="mb-3">
                <label for="proof_description" class="form-label">
                  Ownership Proof Description *
                </label>
                <textarea id="proof_description" name="proof_description"
                          rows="6" maxlength="1000"
                          class="form-control <?= isset($errors['proof']) ? 'is-invalid' : '' ?>"
                          placeholder="e.g. The bag has my name written inside the front pocket. It also has a Pikachu keychain attached to the zipper…"><?= e($_POST['proof_description'] ?? '') ?></textarea>
                <?php if (isset($errors['proof'])): ?>
                  <div class="invalid-feedback"><?= e($errors['proof']) ?></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mt-1">
                  <span style="font-size:.75rem;color:var(--clf-muted);">Minimum 20 characters.</span>
                  <span class="proof-counter" id="proofCounter">0 / 1000</span>
                </div>
              </div>

              <!-- Optional proof photo -->
              <div class="mb-4">
                <label for="proof_photo" class="form-label">
                  Proof Photo <span style="font-weight:400;color:var(--clf-muted);">(Optional)</span>
                </label>
                <input type="file" id="proof_photo" name="proof_photo"
                       accept="image/*" class="form-control
                       <?= isset($errors['proof_photo']) ? 'is-invalid' : '' ?>">
                <?php if (isset($errors['proof_photo'])): ?>
                  <div class="invalid-feedback"><?= e($errors['proof_photo']) ?></div>
                <?php endif; ?>
                <div style="font-size:.75rem;color:var(--clf-muted);margin-top:.3rem;">
                  Upload a photo as supporting proof (max 5 MB).
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn-clf-primary">
                  <i class="bi bi-send me-1"></i>Submit Claim
                </button>
                <a href="items_list.php" class="btn-clf-secondary">Cancel</a>
              </div>

            </form>

          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Character counter ─────────────────────────────────────
const proofTA  = document.getElementById('proof_description');
const counter  = document.getElementById('proofCounter');
if (proofTA && counter) {
  const update = () => {
    const len = proofTA.value.length;
    counter.textContent = len + ' / 1000';
    counter.style.color = len > 900 ? '#dc2626' : '#64748b';
  };
  proofTA.addEventListener('input', update);
  update();
}

// ── Client-side validation ─────────────────────────────────
const claimForm = document.getElementById('claimForm');
if (claimForm) {
  claimForm.addEventListener('submit', function (e) {
    const proof = document.getElementById('proof_description');
    const val   = proof ? proof.value.trim() : '';
    let valid   = true;

    if (val === '') {
      markInvalid(proof, 'Please describe your ownership proof.');
      valid = false;
    } else if (val.length < 20) {
      markInvalid(proof, 'Description must be at least 20 characters.');
      valid = false;
    } else {
      markValid(proof);
    }

    if (!valid) e.preventDefault();
  });
}

function markInvalid(el, msg) {
  if (!el) return;
  el.classList.add('is-invalid');
  let fb = el.parentNode.querySelector('.invalid-feedback');
  if (!fb) {
    fb = document.createElement('div');
    fb.className = 'invalid-feedback';
    el.after(fb);
  }
  fb.textContent = msg;
}
function markValid(el) {
  if (!el) return;
  el.classList.remove('is-invalid');
  const fb = el.parentNode.querySelector('.invalid-feedback');
  if (fb) fb.textContent = '';
}
</script>
</body>
</html>
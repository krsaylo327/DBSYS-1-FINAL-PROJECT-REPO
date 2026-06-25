<?php
// ============================================================
//  add_item.php — Report a Lost or Found Item (self-contained)
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

if (!function_exists('e')) {
    function e($val): string { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }
}

$user = require_login();
$reporter_id = (int)$user['user_id'];

$errors  = [];
$success = false;
$old     = [];

// ── Fetch dropdowns ───────────────────────────────────────
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
$locations  = $conn->query("SELECT location_id, CONCAT(building_name, ' — ', room_or_area) AS location_label FROM locations ORDER BY building_name, room_or_area");

// ── Handle POST ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old         = $_POST;
    $item_name   = trim($_POST['item_name']   ?? '');
    $description = trim($_POST['description'] ?? '');
    $item_type   = $_POST['item_type']        ?? '';
    $category_id = $_POST['category_id']      ?? '';
    $location_id = $_POST['location_id']      ?? '';
    $date_reported = $_POST['date_reported']  ?? date('Y-m-d');

    // ── Server-side validation ────────────────────────────
    if ($item_name === '')
        $errors['item_name'] = 'Item title is required.';
    elseif (mb_strlen($item_name) > 150)
        $errors['item_name'] = 'Title must not exceed 150 characters.';

    if ($description === '')
        $errors['description'] = 'Description is required.';

    if (!in_array($item_type, ['lost', 'found']))
        $errors['item_type'] = 'Please select Lost or Found.';

    if (!ctype_digit($category_id) || (int)$category_id <= 0)
        $errors['category_id'] = 'Please select a category.';

    if (!ctype_digit($location_id) || (int)$location_id <= 0)
        $errors['location_id'] = 'Please select a location.';

    if (empty($date_reported) || !strtotime($date_reported))
        $errors['date_reported'] = 'Please enter a valid date.';

    // ── File upload ───────────────────────────────────────
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['photo'] = 'File upload error. Please try again.';
        } else {
            $finfo         = new finfo(FILEINFO_MIME_TYPE);
            $mime          = $finfo->file($file['tmp_name']);
            $allowed_types = ['image/jpeg','image/png','image/webp','image/gif'];

            if (!in_array($mime, $allowed_types)) {
                $errors['photo'] = 'Only JPG, PNG, WEBP, or GIF images are allowed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors['photo'] = 'File size must not exceed 5 MB.';
            } else {
                $upload_dir = 'uploads/items/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $safe_name = uniqid('item_', true) . '.' . $ext;
                $dest      = $upload_dir . $safe_name;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $errors['photo'] = 'Could not save the uploaded file.';
                } else {
                    $photo_path = $dest;
                }
            }
        }
    }

    // ── Insert ────────────────────────────────────────────
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO items
                (reported_by, category_id, location_id, item_type, item_name,
                 description, photo_path, status, date_reported)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?)
        ");
        $stmt->bind_param('iiisssss',
            $reporter_id, $category_id, $location_id,
            $item_type, $item_name, $description, $photo_path, $date_reported
        );
        if ($stmt->execute()) {
            $success = true;
            $old     = [];
        } else {
            $errors['db'] = 'Database error. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Report Item — Campus Lost &amp; Found</title>
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
  margin: 0;
  background: var(--clf-bg);
  color: #1e293b;
  font-family: 'DM Sans', sans-serif;
  min-height: 100vh;
}

/* ── Sidebar ── */
.clf-sidebar {
  position: fixed; inset: 0 auto 0 0;
  width: var(--clf-sidebar-w);
  background: var(--clf-sidebar-bg);
  padding: 1.75rem 1rem;
  z-index: 200; display: flex; flex-direction: column;
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
  width: 34px; height: 34px; border-radius: 8px;
  background: var(--clf-primary);
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
.clf-nav-link:hover, .clf-nav-link.active {
  background: var(--clf-sidebar-mid); color: #fff;
}

/* ── Main ── */
.clf-main {
  margin-left: var(--clf-sidebar-w);
  padding: 2rem 2.25rem; min-height: 100vh;
}
.page-header { margin-bottom: 1.75rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 .25rem; }
.page-header p  { font-size: .85rem; color: var(--clf-muted); margin: 0; }

/* ── Form card ── */
.clf-card {
  background: var(--clf-surface);
  border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius);
  box-shadow: var(--clf-shadow);
  overflow: hidden;
}
.clf-card-header {
  padding: 1.1rem 1.5rem;
  border-bottom: 1px solid var(--clf-border);
  font-weight: 600; font-size: .95rem; color: #0f172a;
  background: #fafbfd;
}
.clf-card-body { padding: 1.5rem; }

/* ── Form controls ── */
.form-label { font-weight: 600; font-size: .85rem; color: #374151; margin-bottom: .4rem; }
.form-control, .form-select {
  border: 1.5px solid var(--clf-border);
  border-radius: var(--clf-radius-sm);
  font-family: 'DM Sans', sans-serif;
  font-size: .875rem; color: #0f172a;
  background: #f8fafc;
  padding: .52rem .85rem;
  transition: border-color .2s, box-shadow .2s;
}
.form-control:focus, .form-select:focus {
  border-color: var(--clf-primary);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(26,86,232,.12);
  outline: none;
}
.form-control.is-invalid, .form-select.is-invalid {
  border-color: #dc2626;
}
.invalid-feedback { font-size: .78rem; color: #dc2626; margin-top: .25rem; }

/* ── Type toggle ── */
.type-toggle { display: flex; gap: .75rem; }
.type-option {
  flex: 1; border: 1.5px solid var(--clf-border);
  border-radius: var(--clf-radius-sm); padding: .75rem 1rem;
  cursor: pointer; display: flex; align-items: center; gap: .6rem;
  font-size: .875rem; font-weight: 500; color: var(--clf-muted);
  transition: all .2s; background: #f8fafc;
}
.type-option input[type=radio] { display: none; }
.type-option.selected-lost  { border-color: #dc2626; background: #fef2f2; color: #dc2626; }
.type-option.selected-found { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
.type-option:hover { border-color: #94a3b8; }

/* ── Upload zone ── */
.upload-zone {
  border: 2px dashed var(--clf-border);
  border-radius: var(--clf-radius-sm);
  padding: 1.5rem; text-align: center;
  cursor: pointer; background: #f8fafc;
  transition: border-color .2s, background .2s;
}
.upload-zone:hover { border-color: var(--clf-primary); background: #eff6ff; }
.upload-zone i { font-size: 1.75rem; color: #94a3b8; display: block; margin-bottom: .5rem; }
.upload-zone .upload-label { font-size: .85rem; font-weight: 600; color: var(--clf-primary); }
.upload-zone .upload-sub   { font-size: .75rem; color: var(--clf-muted); margin-top: .25rem; }
#preview-wrap img { max-height: 180px; border-radius: 8px; margin-top: .75rem; }

/* ── Buttons ── */
.btn-clf-primary {
  background: var(--clf-primary); color: #fff;
  border: none; border-radius: var(--clf-radius-sm);
  font-family: 'DM Sans', sans-serif;
  font-size: .875rem; font-weight: 600;
  padding: .6rem 1.5rem; cursor: pointer;
  transition: background .2s, box-shadow .2s;
}
.btn-clf-primary:hover {
  background: var(--clf-primary-hover);
  box-shadow: 0 4px 12px rgba(26,86,232,.3);
}
.btn-clf-secondary {
  background: transparent; color: var(--clf-muted);
  border: 1.5px solid var(--clf-border);
  border-radius: var(--clf-radius-sm);
  font-family: 'DM Sans', sans-serif;
  font-size: .875rem; font-weight: 600;
  padding: .6rem 1.5rem; cursor: pointer;
  text-decoration: none; display: inline-block;
  transition: border-color .2s, color .2s;
}
.btn-clf-secondary:hover { border-color: #94a3b8; color: #334155; }

/* ── Alert ── */
.clf-alert-success {
  background: #f0fdf4; border: 1px solid #bbf7d0;
  border-radius: var(--clf-radius-sm); padding: 1rem 1.25rem;
  color: #166534; font-size: .875rem; font-weight: 500;
  display: flex; align-items: center; gap: .6rem; margin-bottom: 1.25rem;
}

/* Sidebar footer */
.clf-sidebar-spacer { flex: 1; }
.clf-sidebar-footer {
  border-top: 1px solid rgba(255,255,255,.08);
  padding-top: 1rem; margin-top: .5rem;
}
.clf-user-pill {
  display: flex; align-items: center; gap: .6rem;
  padding: .45rem .6rem; border-radius: var(--clf-radius-sm);
  margin-bottom: .4rem;
}
.clf-user-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: linear-gradient(135deg,#1a56e8,#4f8eff);
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.clf-user-name { font-size: .78rem; font-weight: 600; color: #c8d9f5; line-height: 1.2; }
.clf-user-role { font-size: .68rem; color: var(--clf-sidebar-txt); }
.clf-logout-btn {
  display: flex; align-items: center; gap: .6rem;
  width: 100%; padding: .55rem .85rem; border-radius: var(--clf-radius-sm);
  background: none; border: none; cursor: pointer;
  color: var(--clf-sidebar-txt); font-size: .85rem; font-weight: 500;
  font-family: 'DM Sans', sans-serif;
  transition: background .2s, color .2s; text-decoration: none;
}
.clf-logout-btn:hover { background: rgba(239,68,68,.12); color: #f87171; }
.clf-logout-btn i { font-size: .95rem; flex-shrink: 0; }

@media (max-width: 991.98px) {
  :root { --clf-sidebar-w: 0px; }
  .clf-sidebar { display: none; }
  .clf-main { margin-left: 0; padding: 1.25rem; }
  .type-toggle { flex-direction: column; }
}
</style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<aside class="clf-sidebar">
  <a href="index.php" class="brand">
    <div class="brand-icon"><i class="bi bi-search-heart-fill"></i></div>
    <div>trU-Access <small>Campus Registry</small></div>
  </a>
  <nav>
    <div class="clf-nav-label">Main</div>
    <a href="index.php"      class="clf-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="items_list.php" class="clf-nav-link"><i class="bi bi-list-ul"></i> Browse Items</a>
    <a href="add_item.php"   class="clf-nav-link active"><i class="bi bi-plus-circle"></i> Report Item</a>
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
<main class="clf-main">
  <div class="page-header">
    <h1>Report an Item</h1>
    <p>Log a lost or found item with details and an optional photo.</p>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

      <?php if ($success): ?>
        <div class="clf-alert-success">
          <i class="bi bi-check-circle-fill fs-5"></i>
          <div>
            Item reported successfully!
            <a href="items_list.php" class="ms-2 fw-semibold" style="color:#166534;">View all items →</a>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors['db'])): ?>
        <div class="alert alert-danger mb-3"><?= e($errors['db']) ?></div>
      <?php endif; ?>

      <div class="clf-card">
        <div class="clf-card-header">
          <i class="bi bi-pencil-square me-2 text-primary"></i>Item Details
        </div>
        <div class="clf-card-body">
          <form id="addItemForm" method="POST" action="add_item.php"
                enctype="multipart/form-data" novalidate>

            <!-- Item Type -->
            <div class="mb-4">
              <label class="form-label">Item Type *</label>
              <div class="type-toggle" id="typeToggle">
                <label class="type-option <?= (($old['item_type'] ?? '') === 'lost') ? 'selected-lost' : '' ?>"
                       id="labelLost">
                  <input type="radio" name="item_type" value="lost"
                         <?= (($old['item_type'] ?? '') === 'lost') ? 'checked' : '' ?>>
                  <i class="bi bi-exclamation-triangle-fill"></i> Lost Item
                </label>
                <label class="type-option <?= (($old['item_type'] ?? '') === 'found') ? 'selected-found' : '' ?>"
                       id="labelFound">
                  <input type="radio" name="item_type" value="found"
                         <?= (($old['item_type'] ?? '') === 'found') ? 'checked' : '' ?>>
                  <i class="bi bi-hand-thumbs-up-fill"></i> Found Item
                </label>
              </div>
              <?php if (isset($errors['item_type'])): ?>
                <div class="invalid-feedback d-block"><?= e($errors['item_type']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Item Name -->
            <div class="mb-3">
              <label for="item_name" class="form-label">Item Title *</label>
              <input type="text" id="item_name" name="item_name" maxlength="150"
                     class="form-control <?= isset($errors['item_name']) ? 'is-invalid' : '' ?>"
                     value="<?= e($old['item_name'] ?? '') ?>"
                     placeholder="e.g. Black Jansport Backpack, Blue Umbrella…">
              <?php if (isset($errors['item_name'])): ?>
                <div class="invalid-feedback"><?= e($errors['item_name']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="mb-3">
              <label for="description" class="form-label">Description *</label>
              <textarea id="description" name="description" rows="4"
                        class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                        placeholder="Describe the item — colour, brand, markings, contents, etc."><?= e($old['description'] ?? '') ?></textarea>
              <?php if (isset($errors['description'])): ?>
                <div class="invalid-feedback"><?= e($errors['description']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Category & Location -->
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="category_id" class="form-label">Category *</label>
                <select id="category_id" name="category_id"
                        class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>">
                  <option value="">— Select Category —</option>
                  <?php
                  if ($categories) {
                    while ($cat = $categories->fetch_assoc()) {
                      $sel = ((int)($old['category_id'] ?? 0) === (int)$cat['category_id']) ? 'selected' : '';
                      echo '<option value="' . (int)$cat['category_id'] . '" ' . $sel . '>'
                           . e($cat['category_name']) . '</option>';
                    }
                  }
                  ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                  <div class="invalid-feedback"><?= e($errors['category_id']) ?></div>
                <?php endif; ?>
              </div>
              <div class="col-md-6">
                <label for="location_id" class="form-label">Location *</label>
                <select id="location_id" name="location_id"
                        class="form-select <?= isset($errors['location_id']) ? 'is-invalid' : '' ?>">
                  <option value="">— Select Location —</option>
                  <?php
                  if ($locations) {
                    while ($loc = $locations->fetch_assoc()) {
                      $sel = ((int)($old['location_id'] ?? 0) === (int)$loc['location_id']) ? 'selected' : '';
                      echo '<option value="' . (int)$loc['location_id'] . '" ' . $sel . '>'
                           . e($loc['location_label']) . '</option>';
                    }
                  }
                  ?>
                </select>
                <?php if (isset($errors['location_id'])): ?>
                  <div class="invalid-feedback"><?= e($errors['location_id']) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Date Reported -->
            <div class="mb-3">
              <label for="date_reported" class="form-label">Date Reported *</label>
              <input type="date" id="date_reported" name="date_reported"
                     class="form-control <?= isset($errors['date_reported']) ? 'is-invalid' : '' ?>"
                     value="<?= e($old['date_reported'] ?? date('Y-m-d')) ?>"
                     max="<?= date('Y-m-d') ?>">
              <?php if (isset($errors['date_reported'])): ?>
                <div class="invalid-feedback"><?= e($errors['date_reported']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Photo Upload -->
            <div class="mb-4">
              <label class="form-label">Photo <span class="fw-normal text-muted">(Optional)</span></label>
              <div class="upload-zone" id="uploadZone"
                   onclick="document.getElementById('photo').click()">
                <i class="bi bi-cloud-arrow-up"></i>
                <div class="upload-label">Click to upload or drag &amp; drop</div>
                <div class="upload-sub">JPEG, PNG, WebP, or GIF — max 5 MB</div>
              </div>
              <input type="file" id="photo" name="photo" accept="image/*"
                     style="display:none"
                     class="<?= isset($errors['photo']) ? 'is-invalid' : '' ?>">
              <?php if (isset($errors['photo'])): ?>
                <div class="invalid-feedback d-block"><?= e($errors['photo']) ?></div>
              <?php endif; ?>
              <div id="preview-wrap" class="text-center"></div>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-2">
              <button type="submit" class="btn-clf-primary">
                <i class="bi bi-send me-1"></i>Submit Report
              </button>
              <a href="items_list.php" class="btn-clf-secondary">Cancel</a>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Type toggle visual ─────────────────────────────────────
document.querySelectorAll('input[name="item_type"]').forEach(radio => {
  radio.addEventListener('change', function () {
    document.getElementById('labelLost').className  = 'type-option';
    document.getElementById('labelFound').className = 'type-option';
    if (this.value === 'lost')
      document.getElementById('labelLost').classList.add('selected-lost');
    else
      document.getElementById('labelFound').classList.add('selected-found');
  });
});

// ── Drag & drop on upload zone ─────────────────────────────
const zone  = document.getElementById('uploadZone');
const input = document.getElementById('photo');

zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = '#1a56e8'; });
zone.addEventListener('dragleave', ()=> { zone.style.borderColor = ''; });
zone.addEventListener('drop', e => {
  e.preventDefault();
  zone.style.borderColor = '';
  if (e.dataTransfer.files.length) {
    input.files = e.dataTransfer.files;
    showPreview(e.dataTransfer.files[0]);
  }
});
input.addEventListener('change', function () {
  if (this.files[0]) showPreview(this.files[0]);
});

function showPreview(file) {
  const wrap = document.getElementById('preview-wrap');
  wrap.innerHTML = '';
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.createElement('img');
    img.src = e.target.result;
    img.alt = 'Preview';
    wrap.appendChild(img);
  };
  reader.readAsDataURL(file);
  // Update upload zone label
  zone.querySelector('.upload-label').textContent = file.name;
}

// ── Client-side validation ─────────────────────────────────
document.getElementById('addItemForm').addEventListener('submit', function (e) {
  let valid = true;

  const typeSelected = document.querySelector('input[name="item_type"]:checked');
  if (!typeSelected) {
    document.querySelector('.type-toggle').insertAdjacentHTML('afterend',
      '<div class="invalid-feedback d-block">Please select Lost or Found.</div>');
    valid = false;
  }

  const name = document.getElementById('item_name');
  if (!name.value.trim()) {
    name.classList.add('is-invalid'); valid = false;
  } else { name.classList.remove('is-invalid'); }

  const desc = document.getElementById('description');
  if (!desc.value.trim()) {
    desc.classList.add('is-invalid'); valid = false;
  } else { desc.classList.remove('is-invalid'); }

  const cat = document.getElementById('category_id');
  if (!cat.value) { cat.classList.add('is-invalid'); valid = false; }
  else            { cat.classList.remove('is-invalid'); }

  const loc = document.getElementById('location_id');
  if (!loc.value) { loc.classList.add('is-invalid'); valid = false; }
  else            { loc.classList.remove('is-invalid'); }

  const photo = document.getElementById('photo');
  if (photo.files.length > 0 && photo.files[0].size > 5 * 1024 * 1024) {
    alert('Photo must not exceed 5 MB.'); valid = false;
  }

  if (!valid) e.preventDefault();
});
</script>
</body>
</html>
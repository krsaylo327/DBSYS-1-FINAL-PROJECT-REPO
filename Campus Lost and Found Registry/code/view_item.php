<?php
// ============================================================
//  view_item.php — View Full Item Record (self-contained)
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';
$user = require_login();

if (!function_exists('e')) {
    function e($val): string { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }
}

// ── Validate item_id ──────────────────────────────────────
$item_id = isset($_GET['item_id']) && ctype_digit($_GET['item_id'])
           ? (int)$_GET['item_id'] : 0;

if ($item_id <= 0) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#dc2626;">
         Invalid item ID. <a href="items_list.php">Go back</a></div>');
}

// ── Fetch item ────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT
        i.item_id,
        i.item_name,
        i.description,
        i.photo_path,
        i.item_type,
        i.status,
        i.date_reported,
        i.created_at,
        c.category_name,
        l.building_name,
        l.room_or_area,
        l.campus_zone,
        u.full_name       AS reporter_name,
        u.email           AS reporter_email,
        u.contact_number  AS reporter_contact
    FROM items i
    INNER JOIN categories c ON i.category_id = c.category_id
    INNER JOIN locations  l ON i.location_id  = l.location_id
    INNER JOIN users      u ON i.reported_by  = u.user_id
    WHERE i.item_id = ?
    LIMIT 1
");
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#dc2626;">
         Item not found. <a href="items_list.php">Go back</a></div>');
}

// ── Fetch claims on this item (for admin visibility) ──────
$claims = [];
$stmt2  = $conn->prepare("
    SELECT cl.claim_id, cl.proof_description, cl.status,
           u.full_name AS claimant_name, u.email AS claimant_email
    FROM   claims cl
    INNER JOIN users u ON cl.claimant_id = u.user_id
    WHERE  cl.item_id = ?
    ORDER  BY cl.claim_id DESC
");
$stmt2->bind_param('i', $item_id);
$stmt2->execute();
$claims = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($item['item_name']) ?> — trU-Access Campus Registry</title>
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
body { margin:0; background:var(--clf-bg); color:#1e293b; font-family:'DM Sans',sans-serif; min-height:100vh; }

/* Sidebar */
.clf-sidebar { position:fixed; inset:0 auto 0 0; width:var(--clf-sidebar-w); background:var(--clf-sidebar-bg); padding:1.75rem 1rem; z-index:200; display:flex; flex-direction:column; }
.clf-sidebar .brand { display:flex; align-items:center; gap:.6rem; color:#fff; font-size:1rem; font-weight:700; margin-bottom:2.25rem; text-decoration:none; line-height:1.3; }
.clf-sidebar .brand small { display:block; font-size:.7rem; font-weight:400; color:var(--clf-sidebar-txt); margin-top:1px; }
.clf-sidebar .brand-icon { width:34px; height:34px; border-radius:8px; background:var(--clf-primary); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1rem; color:#fff; }
.clf-nav-label { font-size:.65rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#445a78; padding:.5rem .75rem .3rem; margin-top:.75rem; }
.clf-nav-link { display:flex; align-items:center; gap:.6rem; color:var(--clf-sidebar-txt); font-size:.85rem; font-weight:500; padding:.55rem .85rem; border-radius:var(--clf-radius-sm); margin-bottom:.1rem; text-decoration:none; transition:background .2s,color .2s; }
.clf-nav-link i { font-size:.95rem; flex-shrink:0; }
.clf-nav-link:hover, .clf-nav-link.active { background:var(--clf-sidebar-mid); color:#fff; }

/* Main */
.clf-main { margin-left:var(--clf-sidebar-w); padding:2rem 2.25rem; min-height:100vh; }

/* Cards */
.clf-card { background:var(--clf-surface); border:1px solid var(--clf-border); border-radius:var(--clf-radius); box-shadow:var(--clf-shadow); overflow:hidden; }
.clf-card-header { padding:1rem 1.5rem; border-bottom:1px solid var(--clf-border); font-weight:600; font-size:.95rem; color:#0f172a; background:#fafbfd; }
.clf-card-body { padding:1.5rem; }

/* Badges */
.clf-badge { display:inline-flex; align-items:center; font-size:.72rem; font-weight:700; letter-spacing:.03em; text-transform:uppercase; padding:.22rem .65rem; border-radius:999px; line-height:1; }
.clf-badge.lost     { background:#fef2f2; color:#dc2626; }
.clf-badge.found    { background:#f0fdf4; color:#16a34a; }
.clf-badge.active   { background:#eff6ff; color:#1a56e8; }
.clf-badge.claimed  { background:#f5f3ff; color:#7c3aed; }
.clf-badge.archived { background:#f1f5f9; color:#64748b; }
.clf-badge.pending  { background:#fffbeb; color:#d97706; }
.clf-badge.approved { background:#f0fdf4; color:#16a34a; }
.clf-badge.rejected { background:#fef2f2; color:#dc2626; }

/* Item photo */
.item-photo { width:100%; max-height:320px; object-fit:cover; border-radius:10px; margin-bottom:1.25rem; }
.photo-placeholder { width:100%; height:220px; background:linear-gradient(135deg,#e2e8f0,#f1f5f9); border-radius:10px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:3rem; margin-bottom:1.25rem; }

/* Detail rows */
.detail-row { display:flex; gap:.5rem; align-items:flex-start; padding:.6rem 0; border-bottom:1px solid #f1f5f9; font-size:.85rem; }
.detail-row:last-child { border-bottom:none; }
.detail-label { min-width:130px; font-weight:600; color:#374151; display:flex; align-items:center; gap:.4rem; }
.detail-label i { color:#94a3b8; font-size:.9rem; }
.detail-value { color:#1e293b; flex:1; }

/* Claim rows */
.claim-row { background:#f8fafc; border:1px solid var(--clf-border); border-radius:var(--clf-radius-sm); padding:1rem; margin-bottom:.75rem; }
.claim-row:last-child { margin-bottom:0; }

/* Buttons */
.btn-clf-primary { background:var(--clf-primary); color:#fff; border:none; border-radius:var(--clf-radius-sm); font-family:'DM Sans',sans-serif; font-size:.875rem; font-weight:600; padding:.6rem 1.5rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; transition:background .2s; }
.btn-clf-primary:hover { background:var(--clf-primary-hover); color:#fff; }
.btn-clf-secondary { background:transparent; color:var(--clf-muted); border:1.5px solid var(--clf-border); border-radius:var(--clf-radius-sm); font-family:'DM Sans',sans-serif; font-size:.875rem; font-weight:600; padding:.6rem 1.5rem; text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; transition:border-color .2s,color .2s; }
.btn-clf-secondary:hover { border-color:#94a3b8; color:#334155; }

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

@media (max-width:991.98px) {
  :root { --clf-sidebar-w:0px; }
  .clf-sidebar { display:none; }
  .clf-main { margin-left:0; padding:1.25rem; }
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

  <!-- Back button + breadcrumb -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="items_list.php" class="btn-clf-secondary">
      <i class="bi bi-arrow-left"></i> Back to Items
    </a>
    <?php if (is_admin_or_staff()): ?>
    <a href="edit_item.php?item_id=<?= $item_id ?>" class="btn-clf-secondary">
      <i class="bi bi-pencil-square"></i> Edit Item
    </a>
    <?php endif; ?>
    <span style="font-size:.82rem;color:var(--clf-muted);">
      Item #<?= $item_id ?>
    </span>
  </div>

  <div class="row g-4">

    <!-- ── Left: Photo + quick info ── -->
    <div class="col-lg-4">
      <div class="clf-card">
        <div class="clf-card-body">

          <?php if (!empty($item['photo_path']) && file_exists($item['photo_path'])): ?>
            <img src="<?= e($item['photo_path']) ?>" alt="Item photo" class="item-photo">
          <?php else: ?>
            <div class="photo-placeholder"><i class="bi bi-image"></i></div>
          <?php endif; ?>

          <!-- Type + Status badges -->
          <div class="d-flex gap-2 mb-3">
            <span class="clf-badge <?= e($item['item_type']) ?>"><?= e($item['item_type']) ?></span>
            <span class="clf-badge <?= e($item['status'])    ?>"><?= e($item['status'])    ?></span>
          </div>

          <h4 style="font-weight:700;font-size:1.15rem;color:#0f172a;margin-bottom:.75rem;">
            <?= e($item['item_name']) ?>
          </h4>

          <!-- Action button -->
          <?php if ($item['status'] === 'active' && $item['item_type'] === 'found'): ?>
            <a href="claim_request.php?item_id=<?= $item_id ?>" class="btn-clf-primary w-100 justify-content-center">
              <i class="bi bi-hand-index-thumb"></i> File a Claim
            </a>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- ── Right: Full details ── -->
    <div class="col-lg-8">

      <!-- Item Details Card -->
      <div class="clf-card mb-4">
        <div class="clf-card-header">
          <i class="bi bi-info-circle-fill me-2 text-primary"></i>Item Details
        </div>
        <div class="clf-card-body">

          <div class="detail-row">
            <div class="detail-label"><i class="bi bi-tag"></i>Category</div>
            <div class="detail-value"><?= e($item['category_name']) ?></div>
          </div>
          <div class="detail-row">
            <div class="detail-label"><i class="bi bi-geo-alt"></i>Location</div>
            <div class="detail-value">
              <?= e($item['building_name'] . ' — ' . $item['room_or_area']) ?>
              <?php if (!empty($item['campus_zone'])): ?>
                <span style="font-size:.75rem;color:var(--clf-muted);margin-left:.4rem;">
                  (<?= e($item['campus_zone']) ?>)
                </span>
              <?php endif; ?>
            </div>
          </div>
          <div class="detail-row">
            <div class="detail-label"><i class="bi bi-calendar3"></i>Date Reported</div>
            <div class="detail-value"><?= e(date('F j, Y', strtotime($item['date_reported']))) ?></div>
          </div>
          <div class="detail-row">
            <div class="detail-label"><i class="bi bi-person"></i>Reported By</div>
            <div class="detail-value">
              <?= e($item['reporter_name']) ?>
              <?php if (!empty($item['reporter_contact'])): ?>
                <span style="font-size:.78rem;color:var(--clf-muted);margin-left:.4rem;">
                  · <?= e($item['reporter_contact']) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
          <div class="detail-row">
            <div class="detail-label"><i class="bi bi-card-text"></i>Description</div>
            <div class="detail-value" style="line-height:1.65;white-space:pre-line;">
              <?= e($item['description']) ?>
            </div>
          </div>

        </div>
      </div>

      <!-- Claims on this item -->
      <?php if (!empty($claims)): ?>
      <div class="clf-card">
        <div class="clf-card-header">
          <i class="bi bi-people-fill me-2 text-warning"></i>
          Claims Filed
          <span class="clf-badge pending ms-2" style="font-size:.65rem;"><?= count($claims) ?></span>
        </div>
        <div class="clf-card-body">
          <?php foreach ($claims as $cl): ?>
          <div class="claim-row">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span style="font-weight:600;font-size:.85rem;"><?= e($cl['claimant_name']) ?></span>
              <span class="clf-badge <?= e($cl['status']) ?>"><?= e($cl['status']) ?></span>
            </div>
            <?php if (!empty($cl['claimant_email'])): ?>
              <div style="font-size:.78rem;color:var(--clf-muted);margin-bottom:.4rem;">
                <i class="bi bi-envelope me-1"></i><?= e($cl['claimant_email']) ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($cl['proof_description'])): ?>
              <div style="font-size:.82rem;color:#334155;background:#fff;border:1px solid var(--clf-border);
                          border-left:3px solid var(--clf-primary);border-radius:6px;padding:.6rem .85rem;margin-top:.4rem;">
                <?= e($cl['proof_description']) ?>
              </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
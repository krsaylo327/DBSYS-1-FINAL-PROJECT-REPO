<?php
// ============================================================
//  index.php — Dashboard (self-contained, CSS embedded)
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

$user = require_login(); // Uncomment when auth is ready

$page_title = 'Dashboard';

// ── Helper: safe HTML output ──────────────────────────────
//if (!function_exists('e')) {
  //  function e($val) { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }
//}

// ── Query 1: Total active items ───────────────────────────
$res_active   = $conn->query("SELECT COUNT(*) AS cnt FROM items WHERE status = 'active'");
$total_active = $res_active ? (int)$res_active->fetch_assoc()['cnt'] : 0;

// ── Query 2: Pending claims ───────────────────────────────
$res_pending   = $conn->query("SELECT COUNT(*) AS cnt FROM claims WHERE status = 'pending'");
$total_pending = $res_pending ? (int)$res_pending->fetch_assoc()['cnt'] : 0;

// ── Query 3: Total reported items (all time) ──────────────
$res_total   = $conn->query("SELECT COUNT(*) AS cnt FROM items");
$total_items = $res_total ? (int)$res_total->fetch_assoc()['cnt'] : 0;

// ── Query 4: Category breakdown (with fallback if VIEW missing) ──
$cat_rows    = [];
$view_result = $conn->query("SELECT * FROM active_items_dashboard_summary LIMIT 8");
if ($view_result) {
    while ($row = $view_result->fetch_assoc()) { $cat_rows[] = $row; }
} else {
    $fallback = $conn->query("
        SELECT c.category_name,
               COUNT(i.item_id) AS total_active_items,
               SUM(CASE WHEN i.item_type='lost'  THEN 1 ELSE 0 END) AS total_lost,
               SUM(CASE WHEN i.item_type='found' THEN 1 ELSE 0 END) AS total_found
        FROM categories c
        LEFT JOIN items i ON c.category_id = i.category_id AND i.status = 'active'
        GROUP BY c.category_id, c.category_name
        ORDER BY total_active_items DESC
        LIMIT 8
    ");
    if ($fallback) {
        while ($row = $fallback->fetch_assoc()) { $cat_rows[] = $row; }
    }
}

// ── Query 5: Recent activity (last 8 items) ───────────────
$recent_items = [];
$recent_stmt  = $conn->prepare("
    SELECT i.item_id, i.item_name, i.item_type, i.status, i.date_reported,
           c.category_name,
           l.building_name, l.room_or_area,
           u.full_name AS reporter_name
    FROM   items i
    INNER JOIN categories c ON i.category_id = c.category_id
    INNER JOIN locations  l ON i.location_id  = l.location_id
    INNER JOIN users      u ON i.reported_by  = u.user_id
    ORDER  BY i.date_reported DESC, i.item_id DESC
    LIMIT 8
");
if ($recent_stmt) {
    $recent_stmt->execute();
    $recent_items = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $recent_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> — Campus Lost &amp; Found</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap">

<style>
/* ================================================================
   DESIGN TOKENS
   ================================================================ */
:root {
  --clf-primary        : #1a56e8;
  --clf-primary-hover  : #1241c4;
  --clf-primary-light  : #e8eeff;

  --clf-sidebar-bg     : #0d1b38;
  --clf-sidebar-mid    : #1a3060;
  --clf-sidebar-txt    : #8fa3c4;
  --clf-sidebar-w      : 240px;

  --clf-bg             : #eef2f9;
  --clf-surface        : #ffffff;
  --clf-border         : #dde3ef;
  --clf-muted          : #64748b;

  --clf-green          : #16a34a;
  --clf-green-bg       : #f0fdf4;
  --clf-amber          : #d97706;
  --clf-amber-bg       : #fffbeb;
  --clf-blue           : #1a56e8;
  --clf-blue-bg        : #eff6ff;

  --clf-lost-color     : #dc2626;
  --clf-lost-bg        : #fef2f2;
  --clf-found-color    : #16a34a;
  --clf-found-bg       : #f0fdf4;
  --clf-active-color   : #1a56e8;
  --clf-active-bg      : #eff6ff;
  --clf-claimed-color  : #7c3aed;
  --clf-claimed-bg     : #f5f3ff;
  --clf-archived-color : #64748b;
  --clf-archived-bg    : #f1f5f9;

  --clf-radius         : 12px;
  --clf-radius-sm      : 8px;
  --clf-shadow         : 0 2px 12px rgba(15,23,42,.07);
  --clf-shadow-hover   : 0 6px 24px rgba(15,23,42,.12);
  --clf-transition     : .2s ease;
}

/* ================================================================
   BASE
   ================================================================ */
*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--clf-bg);
  color: #1e293b;
  font-family: 'DM Sans', sans-serif;
  min-height: 100vh;
}

/* ================================================================
   SIDEBAR
   ================================================================ */
.clf-sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  width: var(--clf-sidebar-w);
  background: var(--clf-sidebar-bg);
  padding: 1.75rem 1rem;
  z-index: 200;
  display: flex;
  flex-direction: column;
}
.clf-sidebar .brand {
  display: flex;
  align-items: center;
  gap: .6rem;
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 2.25rem;
  text-decoration: none;
  line-height: 1.3;
}
.clf-sidebar .brand small {
  display: block;
  font-size: .7rem;
  font-weight: 400;
  color: var(--clf-sidebar-txt);
  margin-top: 1px;
}
.clf-sidebar .brand-icon {
  width: 34px; height: 34px;
  border-radius: 8px;
  background: var(--clf-primary);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  font-size: 1rem; color: #fff;
}
.clf-nav-label {
  font-size: .65rem;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: #445a78;
  padding: .5rem .75rem .3rem;
  margin-top: .75rem;
}
.clf-nav-link {
  display: flex;
  align-items: center;
  gap: .6rem;
  color: var(--clf-sidebar-txt);
  font-size: .85rem;
  font-weight: 500;
  padding: .55rem .85rem;
  border-radius: var(--clf-radius-sm);
  margin-bottom: .1rem;
  text-decoration: none;
  transition: background var(--clf-transition), color var(--clf-transition);
}
.clf-nav-link i { font-size: .95rem; flex-shrink: 0; }
.clf-nav-link:hover,
.clf-nav-link.active {
  background: var(--clf-sidebar-mid);
  color: #fff;
}

/* ================================================================
   MAIN CONTENT
   ================================================================ */
.clf-main {
  margin-left: var(--clf-sidebar-w);
  padding: 2rem 2.25rem;
  min-height: 100vh;
}

/* ================================================================
   PAGE HEADER
   ================================================================ */
.page-header { margin-bottom: 1.75rem; }
.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 .25rem;
}
.page-header p {
  font-size: .85rem;
  color: var(--clf-muted);
  margin: 0;
}

/* ================================================================
   STAT CARDS
   ================================================================ */
.stat-card {
  background: var(--clf-surface);
  border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius);
  box-shadow: var(--clf-shadow);
  padding: 1.25rem 1.4rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  transition: box-shadow var(--clf-transition), transform var(--clf-transition), border-color var(--clf-transition);
}
.stat-card:hover {
  box-shadow: var(--clf-shadow-hover);
  transform: translateY(-2px);
  border-color: var(--clf-primary);
  color: inherit;
}
.stat-icon {
  width: 50px; height: 50px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.stat-icon.green   { background: var(--clf-green-bg);  color: var(--clf-green); }
.stat-icon.amber   { background: var(--clf-amber-bg);  color: var(--clf-amber); }
.stat-icon.blue    { background: var(--clf-blue-bg);   color: var(--clf-blue);  }
.stat-val {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1;
}
.stat-label {
  font-size: .8rem;
  color: var(--clf-muted);
  margin-top: .25rem;
  font-weight: 500;
}

/* ================================================================
   GENERIC CARD
   ================================================================ */
.clf-card {
  background: var(--clf-surface);
  border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius);
  box-shadow: var(--clf-shadow);
  overflow: hidden;
}
.clf-card .card-body { padding: 1.25rem 1.4rem; }

/* ================================================================
   BADGES
   ================================================================ */
.clf-badge {
  display: inline-flex;
  align-items: center;
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .03em;
  text-transform: uppercase;
  padding: .22rem .65rem;
  border-radius: 999px;
  line-height: 1;
}
.clf-badge.lost      { background: var(--clf-lost-bg);     color: var(--clf-lost-color);     }
.clf-badge.found     { background: var(--clf-found-bg);    color: var(--clf-found-color);    }
.clf-badge.active    { background: var(--clf-active-bg);   color: var(--clf-active-color);   }
.clf-badge.claimed   { background: var(--clf-claimed-bg);  color: var(--clf-claimed-color);  }
.clf-badge.archived  { background: var(--clf-archived-bg); color: var(--clf-archived-color); }
.clf-badge.pending   { background: #fffbeb;                color: #d97706;                   }

/* ================================================================
   PROGRESS BAR
   ================================================================ */
.clf-progress {
  height: 6px;
  border-radius: 10px;
  background: #e2e8f0;
  overflow: hidden;
}
.clf-progress-fill {
  height: 100%;
  border-radius: 10px;
  background: var(--clf-primary);
  transition: width .4s ease;
}

/* ================================================================
   TABLE
   ================================================================ */
.clf-table { width: 100%; border-collapse: collapse; }
.clf-table thead tr { border-bottom: 1px solid var(--clf-border); }
.clf-table th {
  padding: .7rem 1.25rem;
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--clf-muted);
  background: #f8fafc;
  white-space: nowrap;
}
.clf-table td {
  padding: .75rem 1.25rem;
  font-size: .83rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.clf-table tbody tr:last-child td { border-bottom: none; }
.clf-table tbody tr:hover { background: #fafbfd; }

/* ================================================================
   PRIMARY BUTTON
   ================================================================ */
.btn-clf-primary {
  background: var(--clf-primary);
  color: #fff !important;
  border: none;
  border-radius: var(--clf-radius-sm);
  font-size: .8rem;
  font-weight: 600;
  text-decoration: none;
  transition: background var(--clf-transition), box-shadow var(--clf-transition);
}
.btn-clf-primary:hover {
  background: var(--clf-primary-hover);
  box-shadow: 0 4px 12px rgba(26,86,232,.3);
}

/* ================================================================
   ALERTS
   ================================================================ */
.clf-alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: var(--clf-radius-sm);
  padding: .9rem 1.2rem;
  color: #b91c1c;
  font-size: .875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: .6rem;
  margin-bottom: 1.5rem;
}
.clf-alert-error i { font-size: 1rem; flex-shrink: 0; }

/* ================================================================
   SIDEBAR FOOTER (user pill + logout)
   ================================================================ */
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
  transition: background var(--clf-transition), color var(--clf-transition);
  text-decoration: none;
}
.clf-logout-btn:hover { background: rgba(239,68,68,.12); color: #f87171; }
.clf-logout-btn i { font-size: .95rem; flex-shrink: 0; }

/* ================================================================
   RESPONSIVE
   ================================================================ */
@media (max-width: 991.98px) {
  :root { --clf-sidebar-w: 0px; }
  .clf-sidebar { display: none; }
  .clf-main { margin-left: 0; padding: 1.25rem; }
}
</style>
</head>
<body>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="clf-sidebar">
  <a href="index.php" class="brand">
    <div class="brand-icon"><i class="bi bi-search-heart-fill"></i></div>
    <div>
      trU-Access
      <small>Campus Registry</small>
    </div>
  </a>
  <nav>
    <div class="clf-nav-label">Main</div>
    <a href="index.php"          class="clf-nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="items_list.php"     class="clf-nav-link"><i class="bi bi-list-ul"></i> Browse Items</a>
    <a href="add_item.php"       class="clf-nav-link"><i class="bi bi-plus-circle"></i> Report Item</a>
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

<!-- ═══════════════ MAIN CONTENT ═══════════════ -->
<main class="clf-main">

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="clf-alert-error">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div><?= e($_SESSION['flash_error']) ?></div>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <h1>Dashboard</h1>
    <p>Overview of the trU-Access Campus Lost &amp; Found Registry — <?= date('l, F j, Y') ?></p>
  </div>

  <!-- ── Stat Cards ─────────────────────────────────────── -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <a href="items_list.php" class="stat-card">
        <div class="stat-icon green"><i class="bi bi-box-seam"></i></div>
        <div>
          <div class="stat-val"><?= $total_active ?></div>
          <div class="stat-label">Total Active Items</div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="admin_workflow.php" class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
        <div>
          <div class="stat-val"><?= $total_pending ?></div>
          <div class="stat-label">Pending Claim Requests</div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="items_list.php" class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-archive"></i></div>
        <div>
          <div class="stat-val"><?= $total_items ?></div>
          <div class="stat-label">Total Items Reported</div>
        </div>
      </a>
    </div>
  </div>

  <!-- ── Category Breakdown + Recent Activity ───────────── -->
  <div class="row g-4">

    <!-- Category Breakdown -->
    <div class="col-lg-5">
      <div class="clf-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0" style="font-size:1rem;font-weight:600;">
              Active Items by Category
            </h5>
            <span class="clf-badge active">Live</span>
          </div>

          <?php if (empty($cat_rows)): ?>
            <p class="text-muted" style="font-size:.85rem;">No data available yet.</p>
          <?php else: ?>
            <?php
            $max_count = max(array_column($cat_rows, 'total_active_items') ?: [1]);
            foreach ($cat_rows as $cat):
              $count = (int)$cat['total_active_items'];
              $pct   = $max_count > 0 ? round(($count / $max_count) * 100) : 0;
            ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span style="font-size:.82rem;font-weight:500;">
                  <?= e($cat['category_name']) ?>
                </span>
                <span class="d-flex align-items-center gap-1"
                      style="font-size:.78rem;color:var(--clf-muted);">
                  <?= $count ?> active &nbsp;
                  <span class="clf-badge lost"  style="font-size:.63rem;"><?= (int)($cat['total_lost']  ?? 0) ?> lost</span>
                  <span class="clf-badge found" style="font-size:.63rem;"><?= (int)($cat['total_found'] ?? 0) ?> found</span>
                </span>
              </div>
              <div class="clf-progress">
                <div class="clf-progress-fill" style="width:<?= $pct ?>%"></div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="col-lg-7">
      <div class="clf-card h-100">
        <div class="card-body p-0">
          <div class="d-flex align-items-center justify-content-between px-4 py-3"
               style="border-bottom:1px solid var(--clf-border);">
            <h5 class="mb-0" style="font-size:1rem;font-weight:600;">Recent Activity</h5>
            <a href="items_list.php" class="btn-clf-primary btn"
               style="padding:6px 14px;font-size:.78rem;">
              View All
            </a>
          </div>

          <div class="table-responsive">
            <table class="clf-table">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Category</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Reported</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recent_items)): ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      No items recorded yet.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recent_items as $item): ?>
                  <tr>
                    <td>
                      <span style="font-weight:500;">
                        <?= e($item['item_name']) ?>
                      </span><br>
                      <small style="color:var(--clf-muted);">
                        <?= e($item['building_name'] . ' — ' . $item['room_or_area']) ?>
                      </small>
                    </td>
                    <td><?= e($item['category_name']) ?></td>
                    <td>
                      <span class="clf-badge <?= e($item['item_type']) ?>">
                        <?= e($item['item_type']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="clf-badge <?= e($item['status']) ?>">
                        <?= e($item['status']) ?>
                      </span>
                    </td>
                    <td style="font-size:.8rem;color:var(--clf-muted);">
                      <?= e(date('M j, Y', strtotime($item['date_reported']))) ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

  </div><!-- /.row -->
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
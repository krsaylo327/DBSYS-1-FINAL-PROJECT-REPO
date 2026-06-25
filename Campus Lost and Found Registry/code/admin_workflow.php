<?php
// ============================================================
//  admin_workflow.php — Claims Workflow (self-contained)
//  Shows all pending claims, allows Approve / Reject
//  Calls stored procedure: ProcessClaimApproval(claim_id, item_id)
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

if (!function_exists('e')) {
    function e($val): string { return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); }
}

$user = require_login();

// Students/regular users may not access the Claims Workflow at all.
// Bounce them back to their own dashboard with a clear explanation.
if (!is_admin_or_staff()) {
    $_SESSION['flash_error'] = 'You do not have permission to access the Claims Workflow.';
    header('Location: index.php');
    exit;
}

// ── Handle Approve action ─────────────────────────────────
$action_msg   = '';
$action_type  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']   ?? '';
    $claim_id = isset($_POST['claim_id']) && ctype_digit($_POST['claim_id'])
                ? (int)$_POST['claim_id'] : 0;
    $item_id  = isset($_POST['item_id'])  && ctype_digit($_POST['item_id'])
                ? (int)$_POST['item_id']  : 0;

    if ($claim_id <= 0 || $item_id <= 0) {
        $action_msg  = 'Invalid claim or item ID.';
        $action_type = 'danger';
    } elseif ($action === 'approve') {
        // Call stored procedure ProcessClaimApproval
        $stmt = $conn->prepare("CALL ProcessClaimApproval(?, ?)");
        if ($stmt) {
            $stmt->bind_param('ii', $claim_id, $item_id);
            if ($stmt->execute()) {
                $action_msg  = 'Claim approved successfully. Item marked as claimed.';
                $action_type = 'success';
            } else {
                // Fallback: run the logic manually if procedure doesn't exist
                $conn->query("UPDATE claims SET status = 'approved' WHERE claim_id = $claim_id");
                $conn->query("UPDATE items   SET status = 'claimed'  WHERE item_id  = $item_id");
                $conn->query("UPDATE claims SET status = 'rejected'
                              WHERE item_id = $item_id
                              AND claim_id != $claim_id
                              AND status = 'pending'");
                $action_msg  = 'Claim approved (fallback mode). Item marked as claimed.';
                $action_type = 'success';
            }
            $stmt->close();
        } else {
            // Procedure doesn't exist — run manually
            $conn->query("UPDATE claims SET status = 'approved' WHERE claim_id = $claim_id");
            $conn->query("UPDATE items   SET status = 'claimed'  WHERE item_id  = $item_id");
            $conn->query("UPDATE claims SET status = 'rejected'
                          WHERE item_id = $item_id
                          AND claim_id != $claim_id
                          AND status = 'pending'");
            $action_msg  = 'Claim approved. Item marked as claimed.';
            $action_type = 'success';
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE claims SET status = 'rejected' WHERE claim_id = ?");
        $stmt->bind_param('i', $claim_id);
        if ($stmt->execute()) {
            $action_msg  = 'Claim rejected.';
            $action_type = 'warning';
        } else {
            $action_msg  = 'Database error. Please try again.';
            $action_type = 'danger';
        }
        $stmt->close();
    }
}

// ── Fetch pending claims ──────────────────────────────────
$pending_claims = [];
$stmt = $conn->prepare("
    SELECT
        cl.claim_id,
        cl.item_id,
        cl.claimant_id,
        cl.proof_description,
        cl.proof_photo_path,
        cl.status                  AS claim_status,
        i.item_name,
        i.item_type,
        i.status                   AS item_status,
        i.date_reported,
        c.category_name,
        l.building_name,
        l.room_or_area,
        u.full_name                AS claimant_name,
        u.email                    AS claimant_email,
        u.contact_number           AS claimant_contact
    FROM claims cl
    INNER JOIN items     i  ON cl.item_id     = i.item_id
    INNER JOIN users     u  ON cl.claimant_id = u.user_id
    INNER JOIN categories c ON i.category_id  = c.category_id
    INNER JOIN locations  l ON i.location_id  = l.location_id
    WHERE cl.status = 'pending'
    ORDER BY cl.claim_id DESC
");
if ($stmt) {
    $stmt->execute();
    $pending_claims = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ── Fetch recently resolved claims (last 10) ─────────────
$resolved_claims = [];
$stmt2 = $conn->prepare("
    SELECT
        cl.claim_id,
        cl.status   AS claim_status,
        i.item_name,
        i.item_type,
        u.full_name AS claimant_name,
        c.category_name,
        l.building_name,
        l.room_or_area
    FROM claims cl
    INNER JOIN items      i  ON cl.item_id     = i.item_id
    INNER JOIN users      u  ON cl.claimant_id = u.user_id
    INNER JOIN categories c  ON i.category_id  = c.category_id
    INNER JOIN locations  l  ON i.location_id  = l.location_id
    WHERE cl.status IN ('approved','rejected')
    ORDER BY cl.claim_id DESC
    LIMIT 10
");
if ($stmt2) {
    $stmt2->execute();
    $resolved_claims = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Claims Workflow — Campus Lost &amp; Found</title>
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

/* ── Sidebar ── */
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
.clf-main { margin-left: var(--clf-sidebar-w); padding: 2rem 2.25rem; min-height: 100vh; }
.page-header { margin-bottom: 1.75rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 .25rem; }
.page-header p  { font-size: .85rem; color: var(--clf-muted); margin: 0; }

/* ── Cards ── */
.clf-card {
  background: var(--clf-surface); border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius); box-shadow: var(--clf-shadow); overflow: hidden;
}
.clf-card-header {
  padding: 1rem 1.5rem; border-bottom: 1px solid var(--clf-border);
  font-weight: 600; font-size: .95rem; color: #0f172a; background: #fafbfd;
  display: flex; align-items: center; justify-content: space-between;
}

/* ── Claim card ── */
.claim-card {
  background: var(--clf-surface); border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius); box-shadow: var(--clf-shadow);
  padding: 1.25rem 1.4rem; margin-bottom: 1rem;
  transition: box-shadow .2s;
}
.claim-card:hover { box-shadow: 0 4px 20px rgba(15,23,42,.1); }

/* ── Badges ── */
.clf-badge {
  display: inline-flex; align-items: center;
  font-size: .72rem; font-weight: 700; letter-spacing: .03em;
  text-transform: uppercase; padding: .22rem .65rem;
  border-radius: 999px; line-height: 1;
}
.clf-badge.lost      { background: #fef2f2; color: #dc2626; }
.clf-badge.found     { background: #f0fdf4; color: #16a34a; }
.clf-badge.pending   { background: #fffbeb; color: #d97706; }
.clf-badge.approved  { background: #f0fdf4; color: #16a34a; }
.clf-badge.rejected  { background: #fef2f2; color: #dc2626; }
.clf-badge.active    { background: #eff6ff; color: #1a56e8; }
.clf-badge.claimed   { background: #f5f3ff; color: #7c3aed; }

/* ── Meta rows ── */
.claim-meta {
  display: flex; flex-wrap: wrap; gap: .5rem 1.5rem;
  font-size: .8rem; color: var(--clf-muted); margin: .6rem 0;
}
.claim-meta span { display: flex; align-items: center; gap: .3rem; }

/* ── Proof box ── */
.proof-box {
  background: #f8fafc; border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius-sm); padding: .75rem 1rem;
  font-size: .82rem; color: #334155; margin: .75rem 0;
  border-left: 3px solid var(--clf-primary);
}

/* ── Action buttons ── */
.btn-approve {
  background: #16a34a; color: #fff; border: none;
  border-radius: var(--clf-radius-sm); padding: .45rem 1.1rem;
  font-family: 'DM Sans', sans-serif; font-size: .82rem; font-weight: 600;
  cursor: pointer; transition: background .2s;
}
.btn-approve:hover { background: #15803d; }

.btn-reject {
  background: transparent; color: #dc2626;
  border: 1.5px solid #dc2626; border-radius: var(--clf-radius-sm);
  padding: .45rem 1.1rem; font-family: 'DM Sans', sans-serif;
  font-size: .82rem; font-weight: 600; cursor: pointer; transition: background .2s, color .2s;
}
.btn-reject:hover { background: #fef2f2; }

/* ── Table ── */
.clf-table { width: 100%; border-collapse: collapse; }
.clf-table thead tr { border-bottom: 1px solid var(--clf-border); }
.clf-table th {
  padding: .7rem 1.25rem; font-size: .72rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .06em;
  color: var(--clf-muted); background: #f8fafc; white-space: nowrap;
}
.clf-table td {
  padding: .75rem 1.25rem; font-size: .83rem;
  border-bottom: 1px solid #f1f5f9; vertical-align: middle;
}
.clf-table tbody tr:last-child td { border-bottom: none; }
.clf-table tbody tr:hover { background: #fafbfd; }

/* ── Empty state ── */
.empty-state {
  text-align: center; padding: 3rem 1rem;
  color: var(--clf-muted);
}
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: .75rem; color: #cbd5e1; }
.empty-state p { font-size: .9rem; margin: 0; }

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
    <a href="add_item.php"   class="clf-nav-link"><i class="bi bi-plus-circle"></i> Report Item</a>
    <div class="clf-nav-label">Admin</div>
    <a href="admin_workflow.php" class="clf-nav-link active"><i class="bi bi-shield-check"></i> Claims Workflow</a>
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

  <div class="page-header d-flex justify-content-between align-items-start">
    <div>
      <h1>Claims Workflow</h1>
      <p>Review and process pending ownership claims.</p>
    </div>
    <span class="clf-badge pending" style="font-size:.8rem;padding:.4rem .9rem;">
      <?= count($pending_claims) ?> Pending
    </span>
  </div>

  <!-- Action feedback -->
  <?php if ($action_msg !== ''): ?>
    <?php $icon = $action_type === 'success' ? 'check-circle-fill' : ($action_type === 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill'); ?>
    <div class="alert alert-<?= e($action_type) ?> d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-<?= $icon ?>"></i>
      <?= e($action_msg) ?>
    </div>
  <?php endif; ?>

  <!-- ── Pending Claims ──────────────────────────────────── -->
  <div class="mb-2">
    <h5 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:1rem;">
      <i class="bi bi-hourglass-split me-2 text-warning"></i>Pending Claims
    </h5>
  </div>

  <?php if (empty($pending_claims)): ?>
    <div class="clf-card mb-4">
      <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>No pending claims at this time. All caught up!</p>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($pending_claims as $claim): ?>
    <div class="claim-card">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

        <!-- Left: claim info -->
        <div style="flex:1;min-width:260px;">
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="clf-badge <?= e($claim['item_type']) ?>"><?= e($claim['item_type']) ?></span>
            <span class="clf-badge <?= e($claim['item_status']) ?>"><?= e($claim['item_status']) ?></span>
            <span style="font-size:.72rem;color:var(--clf-muted);">Claim #<?= (int)$claim['claim_id'] ?></span>
          </div>

          <h6 style="font-weight:700;font-size:.95rem;margin:.25rem 0;">
            <?= e($claim['item_name']) ?>
          </h6>

          <div class="claim-meta">
            <span><i class="bi bi-tag-fill"></i><?= e($claim['category_name']) ?></span>
            <span><i class="bi bi-geo-alt-fill"></i><?= e($claim['building_name'] . ' — ' . $claim['room_or_area']) ?></span>
            <span><i class="bi bi-calendar3"></i><?= e(date('M j, Y', strtotime($claim['date_reported']))) ?></span>
          </div>

          <!-- Claimant info -->
          <div style="font-size:.82rem;margin-bottom:.5rem;">
            <span style="font-weight:600;">Claimant:</span>
            <?= e($claim['claimant_name']) ?>
            <?php if (!empty($claim['claimant_email'])): ?>
              &nbsp;·&nbsp; <a href="mailto:<?= e($claim['claimant_email']) ?>"
                               style="color:var(--clf-primary);">
                <?= e($claim['claimant_email']) ?>
              </a>
            <?php endif; ?>
            <?php if (!empty($claim['claimant_contact'])): ?>
              &nbsp;·&nbsp; <?= e($claim['claimant_contact']) ?>
            <?php endif; ?>
          </div>

          <!-- Proof -->
          <?php if (!empty($claim['proof_description'])): ?>
            <div class="proof-box">
              <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                          letter-spacing:.05em;color:var(--clf-primary);margin-bottom:.3rem;">
                Ownership Proof
              </div>
              <?= e($claim['proof_description']) ?>
            </div>
          <?php endif; ?>

          <!-- Proof photo -->
          <?php if (!empty($claim['proof_photo_path']) && file_exists($claim['proof_photo_path'])): ?>
            <img src="<?= e($claim['proof_photo_path']) ?>" alt="Proof photo"
                 style="max-height:120px;border-radius:8px;margin-top:.5rem;">
          <?php endif; ?>
        </div>

        <!-- Right: action buttons -->
        <div class="d-flex flex-column gap-2" style="min-width:130px;">
          <!-- Approve -->
          <form method="POST" action="admin_workflow.php"
                onsubmit="return confirm('Approve this claim? All other claims on this item will be rejected.')">
            <input type="hidden" name="action"   value="approve">
            <input type="hidden" name="claim_id" value="<?= (int)$claim['claim_id'] ?>">
            <input type="hidden" name="item_id"  value="<?= (int)$claim['item_id'] ?>">
            <button type="submit" class="btn-approve w-100">
              <i class="bi bi-check-lg me-1"></i>Approve
            </button>
          </form>
          <!-- Reject -->
          <form method="POST" action="admin_workflow.php"
                onsubmit="return confirm('Reject this claim?')">
            <input type="hidden" name="action"   value="reject">
            <input type="hidden" name="claim_id" value="<?= (int)$claim['claim_id'] ?>">
            <input type="hidden" name="item_id"  value="<?= (int)$claim['item_id'] ?>">
            <button type="submit" class="btn-reject w-100">
              <i class="bi bi-x-lg me-1"></i>Reject
            </button>
          </form>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- ── Resolved Claims ────────────────────────────────── -->
  <div class="clf-card mt-4">
    <div class="clf-card-header">
      <span><i class="bi bi-clock-history me-2 text-primary"></i>Recently Resolved Claims</span>
      <span style="font-size:.78rem;color:var(--clf-muted);font-weight:400;">Last 10</span>
    </div>

    <?php if (empty($resolved_claims)): ?>
      <div class="empty-state">
        <i class="bi bi-clipboard-x"></i>
        <p>No resolved claims yet.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="clf-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Item</th>
              <th>Category</th>
              <th>Location</th>
              <th>Claimant</th>
              <th>Type</th>
              <th>Result</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resolved_claims as $r): ?>
            <tr>
              <td style="color:var(--clf-muted);font-size:.78rem;"><?= (int)$r['claim_id'] ?></td>
              <td style="font-weight:600;"><?= e($r['item_name']) ?></td>
              <td><?= e($r['category_name']) ?></td>
              <td style="font-size:.8rem;color:var(--clf-muted);">
                <?= e($r['building_name'] . ' — ' . $r['room_or_area']) ?>
              </td>
              <td><?= e($r['claimant_name']) ?></td>
              <td><span class="clf-badge <?= e($r['item_type']) ?>"><?= e($r['item_type']) ?></span></td>
              <td><span class="clf-badge <?= e($r['claim_status']) ?>"><?= e($r['claim_status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
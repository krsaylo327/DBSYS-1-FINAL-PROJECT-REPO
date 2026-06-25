<?php
// ═══════════════════════════════════════════════════════════════════════════
// items_list.php — trU-Access Campus Registry
// Filterable item directory: search tags, segment tabs, dropdowns, card grid
// ═══════════════════════════════════════════════════════════════════════════
require_once 'db_connect.php';
$user = require_login();
$search      = trim($_GET['search']      ?? '');
$item_type   = in_array($_GET['item_type'] ?? '', ['lost', 'found']) ? $_GET['item_type'] : '';
$category_id = (isset($_GET['category_id']) && ctype_digit($_GET['category_id']))
               ? (int)$_GET['category_id'] : 0;
$location_id = (isset($_GET['location_id']) && ctype_digit($_GET['location_id']))
               ? (int)$_GET['location_id'] : 0;

// Tags stored as array in GET: tags[]=Library&tags[]=Electronics
$tags = [];
if (!empty($_GET['tags']) && is_array($_GET['tags'])) {
    foreach ($_GET['tags'] as $t) {
        $clean = trim($t);
        if ($clean !== '' && mb_strlen($clean) <= 80) {
            $tags[] = $clean;
        }
    }
    $tags = array_unique($tags);
}

// ── 2. Load dropdown data ─────────────────────────────────────────────────
$res_cats = $conn->query(
    "SELECT category_id, category_name FROM categories ORDER BY category_name"
);
$res_locs = $conn->query(
    "SELECT location_id, building_name, room_or_area FROM locations ORDER BY building_name, room_or_area"
);

// ── 3. Build prepared-statement query ─────────────────────────────────────
$sql = "
    SELECT
        i.item_id,
        i.item_name,
        i.description,
        i.photo_path,
        i.item_type,
        i.status,
        i.date_reported,
        c.category_name,
        l.building_name,
        l.room_or_area
    FROM items i
    INNER JOIN categories c ON i.category_id = c.category_id
    INNER JOIN locations  l ON i.location_id  = l.location_id
    WHERE 1=1
";
$params = [];
$types  = '';

if ($item_type !== '') {
    $sql    .= " AND i.item_type = ?";
    $params[] = $item_type;
    $types   .= 's';
}
if ($category_id > 0) {
    $sql    .= " AND i.category_id = ?";
    $params[] = $category_id;
    $types   .= 'i';
}
if ($location_id > 0) {
    $sql    .= " AND i.location_id = ?";
    $params[] = $location_id;
    $types   .= 'i';
}
if ($search !== '') {
    $sql    .= " AND i.item_name LIKE ?";
    $like     = '%' . $search . '%';
    $params[] = $like;
    $types   .= 's';
}
foreach ($tags as $tag) {
    $sql    .= " AND (i.item_name LIKE ? OR i.description LIKE ?)";
    $like     = '%' . $tag . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}
$sql .= " ORDER BY i.date_reported DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result     = $stmt->get_result();
$total_rows = $result->num_rows;
$stmt->close();

// ── 4. Build display-tag array ────────────────────────────────────────────
$display_tags = $tags;
if ($search !== '' && !in_array($search, $display_tags)) {
    array_unshift($display_tags, $search);
}

$clear_url = 'items_list.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Browse Items — trU-Access Campus Registry</title>
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
  --clf-text         : #1e293b;
  --clf-radius       : 12px;
  --clf-radius-sm    : 8px;
  --clf-shadow       : 0 2px 12px rgba(15,23,42,.07);
}
*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--clf-bg);
  color: var(--clf-text);
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

/* Sidebar footer (user + logout) */
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
  width: 100%; padding: .52rem .85rem; border-radius: var(--clf-radius-sm);
  background: none; border: none; cursor: pointer;
  color: var(--clf-sidebar-txt); font-size: .85rem; font-weight: 500;
  transition: background .2s, color .2s; text-decoration: none;
}
.clf-logout-btn:hover { background: rgba(239,68,68,.12); color: #f87171; }
.clf-logout-btn i { font-size: .95rem; }

/* ── Main ── */
.clf-main {
  margin-left: var(--clf-sidebar-w);
  padding: 2rem 2.25rem; min-height: 100vh;
}
.page-header { margin-bottom: 1.75rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 .25rem; }
.page-header p  { font-size: .85rem; color: var(--clf-muted); margin: 0; }

/* ── Filter panel ── */
.clf-filter-panel {
  background: var(--clf-surface);
  border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius);
  padding: 1.1rem 1.25rem 1rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--clf-shadow);
}

/* Segment tabs */
.clf-seg-tabs { display: flex; gap: .35rem; margin-bottom: 1rem; }
.clf-seg-tab {
  padding: .38rem .95rem; border-radius: 20px;
  border: 1.5px solid var(--clf-border);
  background: transparent; color: var(--clf-muted);
  font-size: .82rem; font-weight: 600; cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: all .17s;
}
.clf-seg-tab:hover { border-color: var(--clf-primary); color: var(--clf-primary); }
.clf-seg-tab.active { background: var(--clf-primary); border-color: var(--clf-primary); color: #fff; }

/* Search row */
.clf-search-row { display: flex; gap: .65rem; align-items: center; flex-wrap: wrap; }
.clf-search-wrap { flex: 1; min-width: 180px; position: relative; }
.clf-search-wrap .search-icon {
  position: absolute; left: .85rem; top: 50%; transform: translateY(-50%);
  color: #94a3b8; font-size: .85rem; pointer-events: none;
}
.clf-search-input {
  width: 100%; padding: .52rem .9rem .52rem 2.3rem;
  border: 1.5px solid var(--clf-border); border-radius: var(--clf-radius-sm);
  font-size: .875rem; font-family: 'DM Sans', sans-serif; color: #0f172a;
  background: #f8fafc; outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.clf-search-input:focus {
  border-color: var(--clf-primary);
  box-shadow: 0 0 0 3px rgba(26,86,232,.12); background: #fff;
}
.clf-search-clear {
  position: absolute; right: .6rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: #94a3b8; cursor: pointer;
  font-size: .8rem; display: none; padding: 0;
}
.clf-search-clear.visible { display: block; }
.clf-select {
  padding: .52rem .8rem; border: 1.5px solid var(--clf-border);
  border-radius: var(--clf-radius-sm); font-size: .875rem;
  font-family: 'DM Sans', sans-serif; color: #0f172a;
  background: #f8fafc; outline: none; cursor: pointer;
  transition: border-color .2s;
}
.clf-select:focus { border-color: var(--clf-primary); box-shadow: 0 0 0 3px rgba(26,86,232,.12); }
.clf-btn-search {
  padding: .52rem 1.15rem; border-radius: var(--clf-radius-sm);
  background: var(--clf-primary); color: #fff;
  border: none; font-size: .875rem; font-weight: 600;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer; transition: background .2s, box-shadow .2s; white-space: nowrap;
}
.clf-btn-search:hover { background: var(--clf-primary-hover); box-shadow: 0 4px 12px rgba(26,86,232,.3); }

/* Tag pills */
.clf-tag-row { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .75rem; }
.clf-tag {
  display: inline-flex; align-items: center; gap: .3rem;
  background: #eff6ff; border: 1px solid #bfdbfe;
  color: #1d4ed8; border-radius: 20px;
  padding: .22rem .65rem; font-size: .78rem; font-weight: 600;
}
.tag-close { background: none; border: none; color: #93c5fd; cursor: pointer; padding: 0; font-size: .7rem; }
.tag-close:hover { color: #1d4ed8; }
.clf-tag-remove-all {
  background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
  border-radius: 20px; padding: .22rem .65rem;
  font-size: .78rem; font-weight: 600; cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  display: inline-flex; align-items: center; gap: .25rem;
}
.clf-tag-remove-all:hover { background: #fee2e2; }

/* Results toolbar */
.clf-results-toolbar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1rem; font-size: .83rem; color: var(--clf-muted);
}
.clf-results-toolbar strong { color: var(--clf-text); }

/* ── Item cards ── */
.clf-card {
  background: var(--clf-surface);
  border: 1px solid var(--clf-border);
  border-radius: var(--clf-radius);
  box-shadow: var(--clf-shadow);
  overflow: hidden;
  display: flex; flex-direction: column;
  transition: box-shadow .2s, transform .2s; height: 100%;
}
.clf-card:hover { box-shadow: 0 8px 24px rgba(15,23,42,.12); transform: translateY(-2px); }
.clf-card-thumb { width: 100%; height: 170px; object-fit: cover; }
.clf-card-thumb-placeholder {
  width: 100%; height: 170px;
  background: linear-gradient(135deg, #e8edf8, #f1f5fb);
  display: flex; align-items: center; justify-content: center;
  color: #a0b4d0; font-size: 2rem;
}
.clf-card-body { padding: .9rem 1rem .5rem; flex: 1; }
.clf-type-badge {
  display: inline-block; font-size: .7rem; font-weight: 700;
  padding: .18rem .6rem; border-radius: 20px; margin-bottom: .45rem;
  text-transform: uppercase; letter-spacing: .04em;
}
.clf-badge-lost  { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
.clf-badge-found { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.clf-card-title  { font-size: .93rem; font-weight: 700; margin: 0 0 .3rem; color: #0f172a; }
.clf-card-desc   {
  font-size: .8rem; color: var(--clf-muted); margin: 0 0 .5rem; line-height: 1.5;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.clf-category-chip {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: .73rem; color: #475569; background: #f1f5f9;
  border-radius: 6px; padding: .18rem .55rem;
}
.clf-card-footer { padding: .7rem 1rem .9rem; border-top: 1px solid var(--clf-border); }
.clf-card-meta { display: flex; flex-direction: column; gap: .22rem; margin-bottom: .7rem; }
.clf-card-meta span { font-size: .76rem; color: var(--clf-muted); display: flex; align-items: center; gap: .35rem; }
.clf-card-meta i { color: #94a3b8; font-size: .78rem; }
.clf-status-dot { display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; font-weight: 600; }
.clf-status-dot::before { content:''; width:7px; height:7px; border-radius:50%; display:inline-block; }
.clf-status-active::before   { background: #22c55e; }
.clf-status-claimed::before  { background: #3b82f6; }
.clf-status-archived::before { background: #94a3b8; }
.clf-btn-action {
  display: block; width: 100%; text-align: center;
  padding: .52rem; border-radius: var(--clf-radius-sm);
  font-size: .875rem; font-weight: 600; text-decoration: none;
  font-family: 'DM Sans', sans-serif;
  border: 1.5px solid var(--clf-border); color: var(--clf-muted);
  transition: all .2s;
}
.clf-btn-action:hover { border-color: var(--clf-primary); color: var(--clf-primary); background: #eff6ff; }
.clf-btn-action.solid {
  background: var(--clf-primary); border-color: var(--clf-primary); color: #fff;
  box-shadow: 0 4px 12px rgba(26,86,232,.25);
}
.clf-btn-action.solid:hover { background: var(--clf-primary-hover); }

/* Page header */
.clf-page-header { margin-bottom: 1.75rem; }
.clf-page-header h4 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 .25rem; }
.clf-page-header .subtitle { font-size: .85rem; color: var(--clf-muted); margin: 0; }

/* Empty state */
.clf-empty-state { text-align: center; padding: 4rem 1rem; }
.clf-empty-icon  { width: 140px; height: auto; opacity: .7; margin-bottom: 1.25rem; }
.clf-empty-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 .4rem; }
.clf-empty-sub   { font-size: .84rem; color: var(--clf-muted); margin: 0 0 1.25rem; line-height: 1.65; }
.clf-empty-clear {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: .875rem; font-weight: 600; color: var(--clf-primary);
  text-decoration: none; padding: .52rem 1.25rem;
  border: 1.5px solid var(--clf-primary); border-radius: var(--clf-radius-sm);
  transition: background .2s;
}
.clf-empty-clear:hover { background: #eff6ff; }

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

<!-- ═══════════════ MAIN ═══════════════ -->
<main class="clf-main">

  <!-- Page header -->
  <div class="page-header d-flex justify-content-between align-items-start">
    <div>
      <h1>Item Directory</h1>
      <p>Search, filter, and browse all recorded lost &amp; found items.</p>
    </div>
    <a href="add_item.php" class="btn-clf-primary" style="display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;">
      <i class="bi bi-plus-lg"></i> Report Item
    </a>
  </div>

  <!-- ════════════ FILTER PANEL ════════════ -->
  <div class="clf-filter-panel">

    <!-- Segment tabs -->
    <div class="clf-seg-tabs" role="tablist">
      <button type="button"
              class="clf-seg-tab <?= $item_type === '' ? 'active' : '' ?>"
              onclick="setTab('')">All Items</button>
      <button type="button"
              class="clf-seg-tab <?= $item_type === 'lost' ? 'active' : '' ?>"
              onclick="setTab('lost')">Lost Reports</button>
      <button type="button"
              class="clf-seg-tab <?= $item_type === 'found' ? 'active' : '' ?>"
              onclick="setTab('found')">Found Reports</button>
    </div>

    <form id="filterForm" method="GET" action="items_list.php" novalidate>

      <input type="hidden" id="hiddenItemType" name="item_type"
             value="<?= htmlspecialchars($item_type) ?>">

      <div id="hiddenTagsContainer">
        <?php foreach ($tags as $t): ?>
          <input type="hidden" name="tags[]" value="<?= htmlspecialchars($t) ?>">
        <?php endforeach; ?>
      </div>

      <!-- Search + dropdowns -->
      <div class="clf-search-row">

        <div class="clf-search-wrap">
          <i class="bi bi-search search-icon"></i>
          <input type="text" id="searchInput" class="clf-search-input"
                 placeholder="Search item name…" autocomplete="off"
                 value="<?= htmlspecialchars($search) ?>">
          <button type="button"
                  class="clf-search-clear <?= $search !== '' ? 'visible' : '' ?>"
                  id="clearSearchBtn" title="Clear">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <select name="category_id" class="clf-select" id="categorySelect">
          <option value="">Select Categories</option>
          <?php
          if ($res_cats) {
            while ($cat = $res_cats->fetch_assoc()) {
              $sel = ($category_id === (int)$cat['category_id']) ? 'selected' : '';
              printf('<option value="%d" %s>%s</option>',
                (int)$cat['category_id'], $sel,
                htmlspecialchars($cat['category_name']));
            }
          }
          ?>
        </select>

        <select name="location_id" class="clf-select" id="locationSelect">
          <option value="">Select Locations</option>
          <?php
          if ($res_locs) {
            while ($loc = $res_locs->fetch_assoc()) {
              $sel   = ($location_id === (int)$loc['location_id']) ? 'selected' : '';
              $label = htmlspecialchars($loc['building_name'] . ' — ' . $loc['room_or_area']);
              printf('<option value="%d" %s>%s</option>',
                (int)$loc['location_id'], $sel, $label);
            }
          }
          ?>
        </select>

        <button type="button" class="clf-btn-search" id="doSearchBtn">
          <i class="bi bi-search me-1"></i>Search
        </button>

      </div><!-- /.clf-search-row -->

      <!-- Tag pill row -->
      <div class="clf-tag-row" id="tagRow">
        <?php foreach ($display_tags as $tag): ?>
          <span class="clf-tag" data-tag="<?= htmlspecialchars($tag) ?>">
            <?= htmlspecialchars($tag) ?>
            <button type="button" class="tag-close" aria-label="Remove tag"
                    onclick="removeTag('<?= htmlspecialchars(addslashes($tag)) ?>')">
              <i class="bi bi-x-lg"></i>
            </button>
          </span>
        <?php endforeach; ?>
        <?php if (count($display_tags) > 1): ?>
          <button type="button" class="clf-tag-remove-all" onclick="clearAllTags()">
            Remove All <i class="bi bi-x ms-1"></i>
          </button>
        <?php endif; ?>
      </div>

    </form>
  </div><!-- /.clf-filter-panel -->

  <!-- ════════════ RESULTS TOOLBAR ════════════ -->
  <div class="clf-results-toolbar">
    <span class="clf-results-count">
      Showing <strong><?= $total_rows ?></strong>
      item<?= $total_rows !== 1 ? 's' : '' ?>
      <?php if ($item_type !== ''): ?>
        &mdash;
        <span style="color:<?= $item_type === 'lost'
          ? 'var(--color-lost)' : 'var(--color-found)' ?>;font-weight:600">
          <?= ucfirst($item_type) ?> Reports
        </span>
      <?php endif; ?>
    </span>
    <?php if ($search || $category_id || $location_id || !empty($tags) || $item_type): ?>
      <a href="<?= $clear_url ?>"
         style="font-size:.82rem;color:#64748b;text-decoration:none;">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Clear all filters
      </a>
    <?php endif; ?>
  </div>

  <!-- ════════════ CARD GRID ════════════ -->
  <?php if ($total_rows > 0): ?>
  <div class="row g-3" id="cardGrid">
    <?php while ($item = $result->fetch_assoc()):
      $is_found   = $item['item_type'] === 'found';
      $badge_cls  = $is_found ? 'clf-badge-found' : 'clf-badge-lost';
      $badge_txt  = $is_found ? 'Found' : 'Lost';
      $status_cls = match($item['status']) {
        'claimed'  => 'clf-status-claimed',
        'archived' => 'clf-status-archived',
        default    => 'clf-status-active',
      };
      $can_claim    = ($item['status'] === 'active' && $is_found);
      $action_label = $can_claim ? 'File a Claim' : 'View Record';
      $action_href  = $can_claim
        ? 'claim_request.php?item_id=' . (int)$item['item_id']
        : 'view_item.php?item_id=' . (int)$item['item_id'];
      $action_cls   = $can_claim ? 'solid' : '';
      $location_lbl = htmlspecialchars($item['building_name'] . ' — ' . $item['room_or_area']);
      $date_fmt     = date('M j, Y', strtotime($item['date_reported']));
    ?>
    <div class="col-sm-6 col-lg-4">
      <div class="clf-card">

        <?php if (!empty($item['photo_path']) && file_exists($item['photo_path'])): ?>
          <img src="<?= htmlspecialchars($item['photo_path']) ?>"
               alt="<?= htmlspecialchars($item['item_name']) ?>"
               class="clf-card-thumb">
        <?php else: ?>
          <div class="clf-card-thumb-placeholder">
            <i class="bi bi-image"></i>
          </div>
        <?php endif; ?>

        <div class="clf-card-body">
          <span class="clf-type-badge <?= $badge_cls ?>"><?= $badge_txt ?></span>
          <h6 class="clf-card-title"><?= htmlspecialchars($item['item_name']) ?></h6>
          <p class="clf-card-desc"><?= htmlspecialchars($item['description']) ?></p>
          <span class="clf-category-chip">
            <i class="bi bi-tag-fill" style="font-size:.68rem"></i>
            <?= htmlspecialchars($item['category_name']) ?>
          </span>
        </div>

        <div class="clf-card-footer">
          <div class="clf-card-meta">
            <span><i class="bi bi-geo-alt-fill"></i><?= $location_lbl ?></span>
            <span><i class="bi bi-calendar3"></i><?= $date_fmt ?></span>
            <span>
              <span class="clf-status-dot <?= $status_cls ?>">
                <?= ucfirst($item['status']) ?>
              </span>
            </span>
          </div>
          <a href="<?= htmlspecialchars($action_href) ?>"
             class="clf-btn-action <?= $action_cls ?>">
            <?php if ($can_claim): ?>
              <i class="bi bi-hand-index-thumb me-1"></i>
            <?php else: ?>
              <i class="bi bi-eye me-1"></i>
            <?php endif; ?>
            <?= $action_label ?>
          </a>
        </div>

      </div><!-- /.clf-card -->
    </div><!-- /.col -->
    <?php endwhile; ?>
  </div><!-- /#cardGrid -->

  <?php else: ?>
  <!-- ════════════ EMPTY STATE ════════════ -->
  <div class="clf-empty-state visible" id="emptyState">
    <svg class="clf-empty-icon" viewBox="0 0 200 180" fill="none"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="40" y="80" width="120" height="85" rx="10" fill="#dbeafe"/>
      <path d="M40 80 L100 105 L160 80 L100 55 Z" fill="#93c5fd"/>
      <circle cx="130" cy="55" r="30" fill="#fff" stroke="#1a56e8" stroke-width="5"/>
      <circle cx="130" cy="55" r="19" fill="#eff6ff"/>
      <line x1="122" y1="47" x2="138" y2="63" stroke="#1a56e8" stroke-width="4.5"
            stroke-linecap="round"/>
      <line x1="138" y1="47" x2="122" y2="63" stroke="#1a56e8" stroke-width="4.5"
            stroke-linecap="round"/>
      <line x1="152" y1="77" x2="166" y2="93" stroke="#1a56e8" stroke-width="6"
            stroke-linecap="round"/>
    </svg>
    <p class="clf-empty-title">No results found</p>
    <p class="clf-empty-sub">
      No items match your current search or filter selection.<br>
      Try adjusting your keywords or clearing all filters.
    </p>
    <a href="<?= $clear_url ?>" class="clf-empty-clear">
      <i class="bi bi-arrow-counterclockwise me-1"></i>Clear all filters
    </a>
  </div>
  <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════════════════
// Client-side: segment tabs, tag management, keyword search
// ═══════════════════════════════════════════════════════════════════════════

let activeTags    = <?= json_encode(array_values($tags)) ?>;
let currentSearch = <?= json_encode($search) ?>;

const filterForm     = document.getElementById('filterForm');
const searchInput    = document.getElementById('searchInput');
const hiddenItemType = document.getElementById('hiddenItemType');
const hiddenTagsCont = document.getElementById('hiddenTagsContainer');
const clearSearchBtn = document.getElementById('clearSearchBtn');
const tagRow         = document.getElementById('tagRow');

// ── Segment tab ────────────────────────────────────────────────────────────
function setTab(value) {
  hiddenItemType.value = value;
  submitForm();
}

// ── Sync hidden tag inputs before submit ───────────────────────────────────
function syncHiddenTags() {
  hiddenTagsCont.innerHTML = '';
  activeTags.forEach(tag => {
    const inp = document.createElement('input');
    inp.type  = 'hidden';
    inp.name  = 'tags[]';
    inp.value = tag;
    hiddenTagsCont.appendChild(inp);
  });
}

// ── Re-render pill row ─────────────────────────────────────────────────────
function renderTagRow() {
  tagRow.innerHTML = '';
  activeTags.forEach(tag => {
    const pill = document.createElement('span');
    pill.className  = 'clf-tag';
    pill.dataset.tag = tag;
    pill.innerHTML  = escHtml(tag) +
      ` <button type="button" class="tag-close" aria-label="Remove"
          onclick="removeTag('${escHtml(tag).replace(/'/g,"\\'")}')">
          <i class="bi bi-x-lg"></i></button>`;
    tagRow.appendChild(pill);
  });

  if (activeTags.length > 1) {
    const btn = document.createElement('button');
    btn.type      = 'button';
    btn.className = 'clf-tag-remove-all';
    btn.innerHTML = 'Remove All <i class="bi bi-x ms-1"></i>';
    btn.onclick   = clearAllTags;
    tagRow.appendChild(btn);
  }
}

// ── Add keyword as tag on Enter ────────────────────────────────────────────
searchInput.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const val = this.value.trim();
    if (val && !activeTags.includes(val)) {
      activeTags.push(val);
      this.value = '';
      clearSearchBtn.classList.remove('visible');
      syncHiddenTags();
      renderTagRow();
      submitForm();
    } else if (val) {
      submitForm();
    }
  }
});

searchInput.addEventListener('input', function () {
  clearSearchBtn.classList.toggle('visible', this.value.trim() !== '');
});

clearSearchBtn.addEventListener('click', function () {
  searchInput.value = '';
  this.classList.remove('visible');
});

// ── Remove a single tag ────────────────────────────────────────────────────
function removeTag(tag) {
  activeTags = activeTags.filter(t => t !== tag);
  if (searchInput.value.trim() === tag) {
    searchInput.value = '';
    clearSearchBtn.classList.remove('visible');
  }
  syncHiddenTags();
  renderTagRow();
  submitForm();
}

function clearAllTags() {
  activeTags = [];
  searchInput.value = '';
  clearSearchBtn.classList.remove('visible');
  syncHiddenTags();
  renderTagRow();
  submitForm();
}

// ── Search button ──────────────────────────────────────────────────────────
document.getElementById('doSearchBtn').addEventListener('click', function () {
  const val = searchInput.value.trim();
  if (val && !activeTags.includes(val)) {
    activeTags.push(val);
    searchInput.value = '';
    clearSearchBtn.classList.remove('visible');
    syncHiddenTags();
  }
  submitForm();
});

function submitForm() {
  let hs = filterForm.querySelector('input[name="search"]');
  if (!hs) {
    hs = document.createElement('input');
    hs.type = 'hidden';
    hs.name = 'search';
    filterForm.appendChild(hs);
  }
  hs.value = searchInput.value.trim();
  syncHiddenTags();
  filterForm.submit();
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>
</body>
</html>
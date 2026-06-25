<?php
session_start();
// ============================================================
//  debug.php — Run this FIRST to find all errors
//  Place in: C:\xampp\htdocs\campus_lost_found\debug.php
//  Open at:  http://localhost/campus_lost_found/debug.php
//  DELETE this file after fixing everything
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<style>
  body { font-family: monospace; padding: 20px; background: #f8f9fa; }
  .ok   { color: green;  font-weight: bold; }
  .fail { color: red;    font-weight: bold; }
  .warn { color: orange; font-weight: bold; }
  .box  { background: #fff; border: 1px solid #ddd; border-radius: 8px;
          padding: 16px; margin-bottom: 16px; }
  h2    { margin: 0 0 12px; font-size: 1rem; text-transform: uppercase;
          letter-spacing: .05em; color: #333; }
</style>";

echo "<h1 style='font-family:sans-serif;'>🔍 Campus Lost & Found — Debug Report</h1>";

// ════════════════════════════════════════════
// 1. PHP VERSION
// ════════════════════════════════════════════
echo "<div class='box'><h2>1. PHP Version</h2>";
$ver = phpversion();
echo "PHP Version: <span class='" . (version_compare($ver,'7.4','>=') ? 'ok' : 'fail') . "'>$ver</span><br>";
echo "MySQLi extension: <span class='" . (extension_loaded('mysqli') ? 'ok' : 'fail') . "'>"
   . (extension_loaded('mysqli') ? 'Loaded ✓' : 'NOT LOADED ✗') . "</span><br>";
echo "GD extension (image uploads): <span class='" . (extension_loaded('gd') ? 'ok' : 'warn') . "'>"
   . (extension_loaded('gd') ? 'Loaded ✓' : 'Not loaded (uploads may fail)') . "</span>";
echo "</div>";

// ════════════════════════════════════════════
// 2. DB CONNECTION
// ════════════════════════════════════════════
echo "<div class='box'><h2>2. Database Connection</h2>";
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'campus_lost_found';

$conn = @new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo "<span class='fail'>Connection FAILED ✗</span><br>";
    echo "Error: " . htmlspecialchars($conn->connect_error) . "<br>";
    echo "<br><b>Common fixes:</b><ul>
      <li>Make sure MySQL is started in XAMPP</li>
      <li>Make sure the database name is exactly <code>campus_lost_found</code></li>
      <li>Check db_connect.php has the right credentials</li>
    </ul>";
} else {
    echo "<span class='ok'>Connected successfully ✓</span><br>";
    echo "Host: $host | User: $user | Database: $dbname";
}
echo "</div>";

// ════════════════════════════════════════════
// 3. CHECK TABLES EXIST
// ════════════════════════════════════════════
if (!$conn->connect_error) {
    echo "<div class='box'><h2>3. Tables Check</h2>";
    $expected = ['users','categories','locations','items','claims'];
    foreach ($expected as $table) {
        $r = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = ($r && $r->num_rows > 0);
        echo "Table <code>$table</code>: <span class='" . ($exists ? 'ok' : 'fail') . "'>"
           . ($exists ? 'EXISTS ✓' : 'MISSING ✗') . "</span><br>";
    }
    echo "</div>";

// ════════════════════════════════════════════
// 4. CHECK COLUMNS MATCH SCHEMA
// ════════════════════════════════════════════
    echo "<div class='box'><h2>4. Column Names Check</h2>";

    $checks = [
        'items'  => ['item_id','item_name','description','photo_path','item_type',
                     'status','date_reported','reported_by','category_id','location_id'],
        'claims' => ['claim_id','item_id','claimant_id','proof_description',
                     'proof_photo_path','status'],
        'users'  => ['user_id','full_name','email','password','role','contact_number'],
        'categories' => ['category_id','category_name','description'],
        'locations'  => ['location_id','building_name','room_or_area','campus_zone'],
    ];

    foreach ($checks as $table => $cols) {
        $r = $conn->query("SHOW COLUMNS FROM `$table`");
        if (!$r) {
            echo "<b>$table</b>: <span class='fail'>Cannot read (table missing?) ✗</span><br>";
            continue;
        }
        $actual = [];
        while ($row = $r->fetch_assoc()) { $actual[] = $row['Field']; }
        foreach ($cols as $col) {
            $found = in_array($col, $actual);
            echo "<code>$table.$col</code>: <span class='" . ($found ? 'ok' : 'fail') . "'>"
               . ($found ? '✓' : '✗ NOT FOUND — actual columns: ' . implode(', ', $actual)) . "</span><br>";
        }
        echo "<br>";
    }
    echo "</div>";

// ════════════════════════════════════════════
// 5. CHECK VIEW EXISTS
// ════════════════════════════════════════════
    echo "<div class='box'><h2>5. Database View Check</h2>";
    $vr = $conn->query("SELECT 1 FROM active_items_dashboard_summary LIMIT 1");
    if ($vr) {
        echo "<span class='ok'>View 'active_items_dashboard_summary' EXISTS ✓</span>";
    } else {
        echo "<span class='warn'>View 'active_items_dashboard_summary' MISSING ✗</span><br>";
        echo "This is okay — index.php has a fallback query. But you can create it in phpMyAdmin.";
    }
    echo "</div>";

// ════════════════════════════════════════════
// 6. ROW COUNTS
// ════════════════════════════════════════════
    echo "<div class='box'><h2>6. Row Counts (is there any data?)</h2>";
    foreach (['users','categories','locations','items','claims'] as $table) {
        $r = $conn->query("SELECT COUNT(*) AS cnt FROM `$table`");
        if ($r) {
            $cnt = (int)$r->fetch_assoc()['cnt'];
            $cls = $cnt > 0 ? 'ok' : 'warn';
            echo "Table <code>$table</code>: <span class='$cls'>$cnt row(s)</span><br>";
        }
    }
    echo "</div>";

// ════════════════════════════════════════════
// 7. TEST THE EXACT QUERIES FROM index.php
// ════════════════════════════════════════════
    echo "<div class='box'><h2>7. Test Queries from index.php</h2>";

    $tests = [
        "Active items count"   => "SELECT COUNT(*) AS cnt FROM items WHERE status = 'active'",
        "Pending claims count" => "SELECT COUNT(*) AS cnt FROM claims WHERE status = 'pending'",
        "Total items count"    => "SELECT COUNT(*) AS cnt FROM items",
        "Recent items JOIN"    => "SELECT i.item_id, i.item_name, i.item_type, i.status,
                                          i.date_reported, c.category_name,
                                          l.building_name, l.room_or_area, u.full_name
                                   FROM items i
                                   INNER JOIN categories c ON i.category_id = c.category_id
                                   INNER JOIN locations  l ON i.location_id  = l.location_id
                                   INNER JOIN users      u ON i.reported_by  = u.user_id
                                   LIMIT 1",
    ];

    foreach ($tests as $label => $sql) {
        $r = $conn->query($sql);
        if ($r) {
            echo "<b>$label</b>: <span class='ok'>Query OK ✓</span><br>";
        } else {
            echo "<b>$label</b>: <span class='fail'>FAILED ✗ — " . htmlspecialchars($conn->error) . "</span><br>";
        }
    }
    echo "</div>";
}

// ════════════════════════════════════════════
// 8. CHECK db_connect.php EXISTS
// ════════════════════════════════════════════
echo "<div class='box'><h2>8. File Check</h2>";
$files = ['db_connect.php','index.php','items_list.php','add_item.php',
          'claim_request.php','admin_workflow.php'];
foreach ($files as $f) {
    $exists = file_exists(__DIR__ . '/' . $f);
    echo "File <code>$f</code>: <span class='" . ($exists ? 'ok' : 'warn') . "'>"
       . ($exists ? 'Found ✓' : 'Not found') . "</span><br>";
}
echo "</div>";

echo "<div class='box' style='background:#fff3cd;'>";
echo "<h2>⚠️ What to do next</h2>";
echo "Fix any <span class='fail'>red ✗ items</span> above first, then reload index.php.<br>";
echo "<b>When everything is green, delete this debug.php file.</b>";
echo "</div>";
?>
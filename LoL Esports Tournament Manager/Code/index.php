<?php
// 1. INITIALIZE SESSION MANAGER BEFORE ANY OUTPUT IS RENDERED
session_start();

// 2. ESTABLISH CONNECTIVITY HOOKS TO THE DATABASE
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esports_tournament";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Handshake Corrupted: " . $conn->connect_error);
}

// 3. AUTO-INITIALIZE RELATIONAL DATABASE SCHEMA STRUCTURAL VIEWS (IF NOT EXISTING)
$conn->query("CREATE TABLE IF NOT EXISTS teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL UNIQUE,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    points INT DEFAULT 0
) ENGINE=InnoDB");

$conn->query("CREATE TABLE IF NOT EXISTS players (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    ign VARCHAR(100) NOT NULL,
    mvp_points INT DEFAULT 0,
    FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE
) ENGINE=InnoDB");

$conn->query("CREATE TABLE IF NOT EXISTS games (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    team1_id INT,
    team2_id INT,
    schedule_date DATETIME NOT NULL,
    venue VARCHAR(100) NOT NULL,
    is_completed TINYINT DEFAULT 0,
    FOREIGN KEY (team1_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    FOREIGN KEY (team2_id) REFERENCES teams(team_id) ON DELETE CASCADE
) ENGINE=InnoDB");

$conn->query("CREATE TABLE IF NOT EXISTS game_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT UNIQUE,
    winner_team_id INT NOT NULL,
    team1_score INT DEFAULT 0,
    team2_score INT DEFAULT 0,
    FOREIGN KEY (game_id) REFERENCES games(game_id) ON DELETE CASCADE
) ENGINE=InnoDB");

$conn->query("CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'viewer') DEFAULT 'viewer'
) ENGINE=InnoDB");

$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus eSports Tournament Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>LoL ESPORTS TOURNAMENT MANAGER</h1>
        <p>DBSYS 1 FINAL PROJECT</p>
    </header>

    <div class="container">
        <div class="panel status-panel">
            <div>
                User Session Identity: <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Unauthenticated Guest"; ?></strong> 
                <span>(Permission Tier: <?php echo isset($_SESSION['role']) ? strtoupper($_SESSION['role']) : "VIEW-ONLY"; ?>)</span>
            </div>
            <div>
                <?php if (isset($_SESSION['role'])): ?>
                    <a href="logout.php" class="logout-btn">[ Clear Session Log ]</a>
                <?php else: ?>
                    <a href="login.php" class="login-btn">[ Admin Sign-In ]</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container">
        
        <div class="flex-panels">
            
            <?php if ($is_admin): ?>
                
                <div class="panel">
                    <h2>Register Clan & Roster</h2>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="register_team">
                        <div class="form-group">
                            <label>New Organization Name</label>
                            <input type="text" name="team_name" required placeholder="e.g., Sentinels">
                        </div>
                        <div class="form-group">
                            <label>Roster In-Game Name Handle (Optional)</label>
                            <input type="text" name="player_ign" placeholder="e.g., TenZ">
                        </div>
                        <button type="submit">Deploy Roster Records</button>
                    </form>
                </div>

                <div class="panel">
                    <h2>Schedule Upcoming Game</h2>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="schedule_game">
                        <div class="form-group">
                            <label>Home Selection (Team 1)</label>
                            <select name="team1_id" required>
                                <option value="">-- Choose Home Clan --</option>
                                <?php
                                $teams = $conn->query("SELECT team_id, team_name FROM teams ORDER BY team_name ASC");
                                while($t = $teams->fetch_assoc()) echo "<option value='{$t['team_id']}'>{$t['team_name']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Away Selection (Team 2)</label>
                            <select name="team2_id" required>
                                <option value="">-- Choose Away Clan --</option>
                                <?php
                                $teams->data_seek(0);
                                while($t = $teams->fetch_assoc()) echo "<option value='{$t['team_id']}'>{$t['team_name']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Regional Server Venue / Arena Location</label>
                            <input type="text" name="venue" required placeholder="e.g., Tokyo Arena">
                        </div>
                        <div class="form-group">
                            <label>Execution Date & Time</label>
                            <input type="datetime-local" name="schedule_date" required>
                        </div>
                        <button type="submit">Commit Match Schedule</button>
                    </form>
                </div>

                <div class="panel">
                    <h2>Record Match Score</h2>
                    <form action="process.php" method="POST">
                        <input type="hidden" name="action" value="record_score">
                        <div class="form-group">
                            <label>Target Active Game Record</label>
                            <select name="game_id" required>
                                <option value="">-- Select Pending Game Instance --</option>
                                <?php
                                $games = $conn->query("SELECT g.game_id, t1.team_name AS t1, t2.team_name AS t2 FROM games g JOIN teams t1 ON g.team1_id = t1.team_id JOIN teams t2 ON g.team2_id = t2.team_id WHERE g.is_completed = 0");
                                while($g = $games->fetch_assoc()) echo "<option value='{$g['game_id']}'>ID: {$g['game_id']} — {$g['t1']} vs {$g['t2']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Team 1 (Home) Scored Points</label>
                            <input type="number" name="team1_score" min="0" required placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Team 2 (Away) Scored Points</label>
                            <input type="number" name="team2_score" min="0" required placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Nominate Match MVP Player</label>
                            <select name="mvp_player_id">
                                <option value="">-- No Nomination --</option>
                                <?php
                                $players = $conn->query("SELECT p.player_id, p.ign, t.team_name FROM players p JOIN teams t ON p.team_id = t.team_id ORDER BY t.team_name ASC");
                                while($p = $players->fetch_assoc()) echo "<option value='{$p['player_id']}'>{$p['ign']} ({$p['team_name']})</option>";
                                ?>
                            </select>
                        </div>
                        <button type="submit">Finalize Match Results</button>
                    </form>
                </div>

            <?php else: ?>
                <div class="panel locked-panel">
                    <div class="lock-icon">🔒</div>
                    <h2>Control Deck Encrypted</h2>
                    <p>You are accessing the pipeline via secondary viewer clearance vectors. Form manipulation capabilities are frozen.</p>
                    <a href="login.php" class="button">Unlock Admin Panels</a>
                </div>
            <?php endif; ?>

        </div>

        <div class="flex-panels grid-wide">
            
            <div class="panel">
                <h2>Live Leaderboard Standings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Clan Organization</th>
                            <th>Wins</th>
                            <th>Losses</th>
                            <th>League Points</th>
                            <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rank = 1;
                        $standings = $conn->query("SELECT team_id, team_name, wins, losses, points FROM teams ORDER BY points DESC, wins DESC");
                        if ($standings && $standings->num_rows > 0) {
                            while($row = $standings->fetch_assoc()) {
                                echo "<tr>
                                    <td><strong>#{$rank}</strong></td>
                                    <td class='highlight-text'>{$row['team_name']}</td>
                                    <td>{$row['wins']}</td>
                                    <td>{$row['losses']}</td>
                                    <td class='points-text'>{$row['points']} pts</td>";
                                if ($is_admin) {
                                    echo "<td>
                                        <form action='process.php' method='POST' style='display:inline;' onsubmit='return confirm(\"Deleting this team deletes all its players and matches! Proceed?\");'>
                                            <input type='hidden' name='action' value='delete_team'>
                                            <input type='hidden' name='id' value='{$row['team_id']}'>
                                            <button type='submit' class='delete-btn' style='padding:4px 8px; font-size:11px; background:#ff4c4c;'>Delete</button>
                                        </form>
                                    </td>";
                                }
                                echo "</tr>";
                                $rank++;
                            }
                        } else {
                            echo "<tr><td colspan='".($is_admin ? 6 : 5)."'>No active roster statistics recorded inside core data fields.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <h2>Tournament Match Schedule</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Match Profile Battle</th>
                            <th>Venue Server</th>
                            <th>Status Outcome</th>
                            <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $scheduleQuery = "
                            SELECT g.game_id, g.schedule_date, g.venue, g.is_completed, t1.team_name AS t1, t2.team_name AS t2, gr.team1_score, gr.team2_score 
                            FROM games g 
                            JOIN teams t1 ON g.team1_id = t1.team_id 
                            JOIN teams t2 ON g.team2_id = t2.team_id 
                            LEFT JOIN game_results gr ON g.game_id = gr.game_id 
                            ORDER BY g.schedule_date ASC";
                        $schedule = $conn->query($scheduleQuery);
                        if ($schedule && $schedule->num_rows > 0) {
                            while($row = $schedule->fetch_assoc()) {
                                $date = date("M d, Y @ H:i", strtotime($row['schedule_date']));
                                $status = $row['is_completed'] 
                                    ? "<span class='score-badge'>[{$row['team1_score']} - {$row['team2_score']}]</span>" 
                                    : "<span class='upcoming-text'>Upcoming</span>";
                                echo "<tr>
                                    <td class='date-text'>{$date}</td>
                                    <td><strong>{$row['t1']}</strong> vs <strong>{$row['t2']}</strong></td>
                                    <td>{$row['venue']}</td>
                                    <td>{$status}</td>";
                                if ($is_admin) {
                                    echo "<td>
                                        <form action='process.php' method='POST' style='display:inline;'>
                                            <input type='hidden' name='action' value='delete_game'>
                                            <input type='hidden' name='id' value='{$row['game_id']}'>
                                            <button type='submit' class='delete-btn' style='padding:4px 8px; font-size:11px; background:#ff4c4c;'>Delete</button>
                                        </form>
                                    </td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='".($is_admin ? 5 : 4)."'>No scheduled matches mapped to upcoming matrix arrays.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <h2>Individual MVP Nominations Tracker</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Player IGN</th>
                            <th>Belongs To Clan</th>
                            <th>Star Nominations</th>
                            <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // CHANGED: Added "WHERE p.mvp_points >= 1" to hide players with 0 MVPs
                        $mvps = $conn->query("SELECT p.player_id, p.ign, p.mvp_points, t.team_name 
                                              FROM players p 
                                              JOIN teams t ON p.team_id = t.team_id 
                                              WHERE p.mvp_points >= 1 
                                              ORDER BY p.mvp_points DESC, p.ign ASC 
                                              LIMIT 10");
                                              
                        if ($mvps && $mvps->num_rows > 0) {
                            while($row = $mvps->fetch_assoc()) {
                                // Add a distinctive CSS class to highlight active MVP achievers
                                echo "<tr class='mvp-highlight-row'>
                                    <td class='mvp-text'>🌟 {$row['ign']}</td>
                                    <td>{$row['team_name']}</td>
                                    <td class='points-text' style='color: #ffd700; font-weight: bold;'>{$row['mvp_points']} Match MVPs</td>";
                                if ($is_admin) {
                                    echo "<td>
                                        <form action='process.php' method='POST' style='display:inline;'>
                                            <input type='hidden' name='action' value='delete_player'>
                                            <input type='hidden' name='id' value='{$row['player_id']}'>
                                            <button type='submit' class='delete-btn' style='padding:4px 8px; font-size:11px; background:#ff4c4c;'>Delete</button>
                                        </form>
                                    </td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            // Clean fallback message if no matches have been played/recorded yet
                            echo "<tr><td colspan='".($is_admin ? 4 : 3)."'>No active player nominations found with 1 or more MVPs.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>
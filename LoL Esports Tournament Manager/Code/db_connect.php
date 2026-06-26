<?php
// 1. START SESSION TO VALIDATE ADMIN ROLE
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esports_tournament";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Lost: " . $conn->connect_error);
}

// 2. SECURITY CHECK: Ensure user is authorized
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die("Unauthorized Access: Admin privileges required.");
    }

    $action = $_POST['action'];

    // =========================================================================
    // MODULE A: REGISTER TEAM & PLAYER ROSTER
    // =========================================================================
    if ($action === 'register_team') {
        $team_name = isset($_POST['team_name']) ? trim($_POST['team_name']) : '';
        $raw_players = isset($_POST['player_ign']) ? trim($_POST['player_ign']) : '';

        if (!empty($team_name)) {
            $stmt = $conn->prepare("INSERT INTO teams (team_name) VALUES (?) ON DUPLICATE KEY UPDATE team_name=team_name");
            $stmt->bind_param("s", $team_name);
            $stmt->execute();
            
            $team_id = $stmt->insert_id;
            $stmt->close();

            if ($team_id == 0) {
                $lookup = $conn->prepare("SELECT team_id FROM teams WHERE team_name = ?");
                $lookup->bind_param("s", $team_name);
                $lookup->execute();
                $res = $lookup->get_result()->fetch_assoc();
                if ($res) {
                    $team_id = $res['team_id'];
                }
                $lookup->close();
            }

            if (!empty($raw_players) && $team_id > 0) {
                $player_array = explode(',', $raw_players);
                $player_stmt = $conn->prepare("INSERT INTO players (team_id, ign) VALUES (?, ?)");

                foreach ($player_array as $single_player) {
                    $trimmed_ign = trim($single_player);
                    if (!empty($trimmed_ign)) {
                        $player_stmt->bind_param("is", $team_id, $trimmed_ign);
                        $player_stmt->execute();
                    }
                }
                $player_stmt->close();
            }
        }
        header("Location: index.php");
        exit();
    }

    // =========================================================================
    // MODULE B: SCHEDULE GAMES
    // =========================================================================
    if ($action === 'schedule_game') {
        $team1_id = intval($_POST['team1_id']);
        $team2_id = intval($_POST['team2_id']);
        $venue = trim($_POST['venue']);
        $schedule_date = $_POST['schedule_date'];

        if ($team1_id !== $team2_id && !empty($venue) && !empty($schedule_date)) {
            $stmt = $conn->prepare("INSERT INTO games (team1_id, team2_id, venue, schedule_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $team1_id, $team2_id, $venue, $schedule_date);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: index.php");
        exit();
    }

    // =========================================================================
    // MODULE C: RECORD SCORES
    // =========================================================================
    if ($action === 'record_score') {
        $game_id = intval($_POST['game_id']);
        $team1_score = intval($_POST['team1_score']);
        $team2_score = intval($_POST['team2_score']);
        $mvp_player_id = isset($_POST['mvp_player_id']) ? intval($_POST['mvp_player_id']) : 0;

        $game_info = $conn->query("SELECT team1_id, team2_id FROM games WHERE game_id = $game_id")->fetch_assoc();

        if ($game_info) {
            $t1_id = $game_info['team1_id'];
            $t2_id = $game_info['team2_id'];
            
            if ($team1_score > $team2_score) {
                $winner_id = $t1_id;
                $conn->query("UPDATE teams SET wins = wins + 1, points = points + 3 WHERE team_id = $t1_id");
                $conn->query("UPDATE teams SET losses = losses + 1 WHERE team_id = $t2_id");
            } else {
                $winner_id = $t2_id;
                $conn->query("UPDATE teams SET wins = wins + 1, points = points + 3 WHERE team_id = $t2_id");
                $conn->query("UPDATE teams SET losses = losses + 1 WHERE team_id = $t1_id");
            }

            $stmt = $conn->prepare("INSERT INTO game_results (game_id, winner_team_id, team1_score, team2_score) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $game_id, $winner_id, $team1_score, $team2_score);
            
            if ($stmt->execute()) {
                $conn->query("UPDATE games SET is_completed = 1 WHERE game_id = $game_id");

                if ($mvp_player_id > 0) {
                    $conn->query("UPDATE players SET mvp_points = mvp_points + 1 WHERE player_id = $mvp_player_id");
                }
            }
            $stmt->close();
        }
        header("Location: index.php");
        exit();
    }

    // =========================================================================
    // MODULE D: SECURE ADMINISTRATIVE DELETION DISPATCHERS
    // =========================================================================
    
    // 1. Delete Team Row
    if ($action === 'delete_team') {
        $team_id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM teams WHERE team_id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }

    // 2. Delete Match Row
    if ($action === 'delete_game') {
        $game_id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM games WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }

    // 3. Delete Player Row
    if ($action === 'delete_player') {
        $player_id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM players WHERE player_id = ?");
        $stmt->bind_param("i", $player_id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit();
    }
}

$conn->close();
header("Location: index.php");
exit();
?>
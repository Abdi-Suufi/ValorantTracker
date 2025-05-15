<?php
require_once 'api_handler.php';
require_once 'config.php';

$pageTitle = "Leaderboard";
$leaderboardData = null;
$apiErrorDetails = null;
$selectedRegion = isset($_GET['region']) ? $_GET['region'] : DEFAULT_REGION;

$params = [];

// Try v1 for leaderboard
$leaderboardEndpoint = "/v1/leaderboard/{$selectedRegion}"; 
$response = callValorantApi($leaderboardEndpoint, $params);

if (isset($response['error'])) {
    $apiErrorDetails = $response;
} elseif (isset($response['status']) && $response['status'] != 200) {
    $apiErrorDetails = ['error' => 'API Error', 'message' => ($response['errors'][0]['message'] ?? 'Failed to retrieve leaderboard'), 'details' => $response];
// Check if the response itself is an array of players (older API style)
} elseif (is_array($response) && (empty($response) || isset($response[0]['PlayerCardID']) || isset($response[0]['puuid']))) {
    if (empty($response)) {
        $apiErrorDetails = ['error' => 'No Data', 'message' => 'Leaderboard is empty for the selected region.'];
    } else {
        $leaderboardData = ['data' => $response]; // Wrap it for consistent handling
    }
} elseif (isset($response['data'])) { // Standard check for data key
    if (!empty($response['data'])){
        $leaderboardData = $response;
    } else {
        $apiErrorDetails = ['error' => 'No Data', 'message' => 'Leaderboard is empty for the selected region (data key was empty).'];
    }
} else {
    $apiErrorDetails = ['error' => 'Unexpected Response', 'message' => 'No leaderboard data found or API response format was unexpected.', 'details' => $response];
}

$availableRegions = ['eu' => 'Europe', 'na' => 'North America', 'ap' => 'Asia Pacific', 'kr' => 'Korea', 'latam' => 'LATAM', 'br' => 'Brazil'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Valorant Stats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Valorant Stats</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="player_stats.php">Player Stats</a></li>
                <li><a href="leaderboard.php" class="active">Leaderboard</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="leaderboard-view">
            <h2><?php echo $pageTitle; ?> - <?php echo htmlspecialchars($availableRegions[$selectedRegion] ?? strtoupper($selectedRegion)); ?></h2>
            
            <form method="GET" action="leaderboard.php" class="leaderboard-form">
                <div class="form-group">
                    <label for="region">Select Region:</label>
                    <select id="region" name="region" onchange="this.form.submit()">
                        <?php foreach ($availableRegions as $code => $name): ?>
                            <option value="<?php echo $code; ?>" <?php echo ($selectedRegion === $code) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="submit" value="View Leaderboard">
            </form>

            <?php if ($apiErrorDetails): ?>
                 <div class="error-message">
                    <h4>API Error Details:</h4>
                    <p><strong>Error Type:</strong> <?php echo htmlspecialchars($apiErrorDetails['error'] ?? 'Unknown'); ?></p>
                    <?php if (isset($apiErrorDetails['message'])): ?>
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($apiErrorDetails['message']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($apiErrorDetails['http_code'])): ?>
                        <p><strong>HTTP Code:</strong> <?php echo htmlspecialchars($apiErrorDetails['http_code']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($apiErrorDetails['details']) && is_string($apiErrorDetails['details'])):
                    ?>    <p><strong>Details:</strong> <?php echo htmlspecialchars($apiErrorDetails['details']); ?></p>
                    <?php elseif (isset($apiErrorDetails['details']) && is_array($apiErrorDetails['details'])):
                    ?>  <p><strong>Full Response/Details:</strong></p>
                        <pre><?php echo htmlspecialchars(print_r($apiErrorDetails['details'], true)); ?></pre>
                    <?php endif; ?>
                     <?php if (isset($apiErrorDetails['errors']) && is_array($apiErrorDetails['errors'])):
                    ?>  <p><strong>API Errors Array:</strong></p>
                        <pre><?php echo htmlspecialchars(print_r($apiErrorDetails['errors'], true)); ?></pre>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($leaderboardData && isset($leaderboardData['data']) && !empty($leaderboardData['data'])): ?>
                <p>Displaying top <?php echo count($leaderboardData['data']); ?> players. API might paginate for full list.</p>
                <p>Season: Current/Latest (default)</p> 
                <div class="table-responsive-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Player</th>
                                <th>Ranked Rating</th>
                                <th>Wins</th>
                                <th>Tier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboardData['data'] as $player): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($player['leaderboardRank'] ?? ($player['rank'] ?? 'N/A')); // API v1 uses 'rank' ?></td>
                                    <td>
                                        <?php 
                                        $playerNameDisplay = 'N/A';
                                        if (!empty($player['gameName']) && isset($player['tagLine'])) {
                                            $playerNameDisplay = htmlspecialchars($player['gameName'] . '#' . $player['tagLine']);
                                        } elseif (isset($player['PlayerName'])) { // Older key
                                            $playerNameDisplay = htmlspecialchars($player['PlayerName']);
                                        }  elseif (!empty($player['game_name']) && isset($player['tag_line'])) { // another possible variation
                                            $playerNameDisplay = htmlspecialchars($player['game_name'] . '#' . $player['tag_line']);
                                        }
                                        echo $playerNameDisplay;
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($player['rankedRating'] ?? ($player['rr'] ?? 'N/A')); // API v1 uses 'rr' ?></td>
                                    <td><?php echo htmlspecialchars($player['numberOfWins'] ?? ($player['wins'] ?? 'N/A'));// API v1 uses 'wins' ?></td>
                                    <td><?php echo htmlspecialchars($player['competitiveTier'] ?? ($player['tier'] ?? 'N/A')); // API v1 uses 'tier' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (!$apiErrorDetails): ?>
                <p>Select a region to view the leaderboard. If it remains empty, there might be no data for the selection.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Valorant Stats Website. API by HenrikDev.</p>
    </footer>
</body>
</html> 
<?php
require_once 'api_handler.php';
require_once 'config.php'; // Ensure config is loaded for potential default values

$pageTitle = "Player Stats";
$playerStatsDisplay = null; // Renamed from playerData for clarity, this will hold the processed stats for display
$accountInfo = null; // To store account name/tag for display even if stats fetch fails
$apiErrorDetails = null; // To store detailed error info
$attemptedAccountEndpoint = '';
$attemptedMmrEndpoint = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['player_name_tag'])) {
    $playerNameAndTag = trim($_POST['player_name_tag']);
    $userRegion = isset($_POST['region']) ? trim($_POST['region']) : DEFAULT_REGION;

    // Remove whitespace from within the name and tag combination
    $playerNameAndTag = str_replace(' ', '', $playerNameAndTag);

    if (empty($playerNameAndTag) || strpos($playerNameAndTag, '#') === false) {
        $apiErrorDetails = ['error' => 'Input Error', 'message' => 'Invalid format. Please use "PlayerName#TAG".'];
    } else {
        list($playerName, $playerTag) = explode('#', $playerNameAndTag, 2);

        if (empty($playerName) || empty($playerTag)) {
            $apiErrorDetails = ['error' => 'Input Error', 'message' => 'Player name and tag cannot be empty. Ensure format is "PlayerName#TAG".'];
        } else {
            $accountInfo = ['name' => $playerName, 'tag' => $playerTag, 'region' => $userRegion]; // Store for display

            // Step 1: Get account data (including PUUID and authoritative region) using /v1/account/
            $attemptedAccountEndpoint = "/v1/account/{$playerName}/{$playerTag}";
            $accountDataResponse = callValorantApi($attemptedAccountEndpoint);

            if (isset($accountDataResponse['error'])) {
                $apiErrorDetails = $accountDataResponse; 
                $apiErrorDetails['attempted_endpoint'] = $attemptedAccountEndpoint;
            } elseif (isset($accountDataResponse['data']['puuid']) && isset($accountDataResponse['data']['region'])) {
                $puuid = $accountDataResponse['data']['puuid'];
                $accountRegion = $accountDataResponse['data']['region']; // Use region from this API call
                
                // Update accountInfo with all data from this call (name, tag, puuid, card, etc.)
                $accountInfo = $accountDataResponse['data']; 
                // Ensure the originally submitted name/tag persist if API doesn't return them or if they differ (e.g. case sensitivity)
                $accountInfo['name'] = $accountDataResponse['data']['name'] ?? $playerName;
                $accountInfo['tag'] = $accountDataResponse['data']['tag'] ?? $playerTag;

                // Step 2: Get MMR data using /v2/mmr/ with the region from the /v1/account call
                $attemptedMmrEndpoint = "/v2/mmr/{$accountRegion}/{$playerName}/{$playerTag}";
                $mmrDataResponse = callValorantApi($attemptedMmrEndpoint);

                if (isset($mmrDataResponse['error'])) {
                    $apiErrorDetails = $mmrDataResponse; 
                    $apiErrorDetails['attempted_endpoint'] = $attemptedMmrEndpoint;
                } elseif (isset($mmrDataResponse['data'])) {
                    // Process and structure the data for display based on your working script's logic
                    $playerStatsDisplay = [];
                    $data = $mmrDataResponse['data'];

                    $playerStatsDisplay['current_rank'] = $data['current_data']['currenttierpatched'] ?? 'N/A';
                    $playerStatsDisplay['current_rank_image'] = $data['current_data']['images']['large'] ?? null;
                    $playerStatsDisplay['current_elo'] = $data['current_data']['elo'] ?? 'N/A';
                    $playerStatsDisplay['ranking_in_tier'] = $data['current_data']['ranking_in_tier'] ?? 0;
                    $playerStatsDisplay['mmr_change'] = $data['current_data']['mmr_change_to_last_game'] ?? 0;

                    $playerStatsDisplay['highest_rank'] = $data['highest_rank']['patched_tier'] ?? 'N/A';
                    $playerStatsDisplay['highest_rank_season'] = $data['highest_rank']['season'] ?? 'Unknown Season';
                    if (isset($data['highest_rank']['tier'])) {
                         $playerStatsDisplay['highest_rank_image'] = "https://media.valorant-api.com/competitivetiers/03621f52-342b-cf4e-4f86-9350a49c6d04/" . $data['highest_rank']['tier'] . "/largeicon.png";
                    } else {
                        $playerStatsDisplay['highest_rank_image'] = null;
                    }

                    // Seasonal Data (simplified for now, can be expanded to show all seasons like mmr-history)
                    $playerStatsDisplay['by_season'] = [];
                    if (isset($data['by_season']) && is_array($data['by_season'])) {
                        foreach($data['by_season'] as $season_id => $season_data) {
                            if (isset($season_data['error'])) continue; // Skip error seasons
                            $playerStatsDisplay['by_season'][$season_id] = [
                                'season_id' => $season_id,
                                'wins' => $season_data['wins'] ?? 0,
                                'games' => $season_data['number_of_games'] ?? 0,
                                'rank' => $season_data['final_rank_patched'] ?? ($season_data['rank'] ?? 'N/A'),
                                // 'elo' => $season_data['elo'] ?? 'N/A', // V2/mmr doesn't provide ELO per season like v3/mmr-history
                            ];
                        }
                         krsort($playerStatsDisplay['by_season']); // Sort by season ID descending (latest first)
                    }

                } else {
                    $apiErrorDetails = ['error' => 'MMR Data Error', 'message' => 'No data key in MMR response or unexpected API response format.', 'details' => $mmrDataResponse];
                    $apiErrorDetails['attempted_endpoint'] = $attemptedMmrEndpoint;
                }
            } else {
                $apiErrorDetails = ['error' => 'Account Error', 'message' => 'Could not retrieve account details (PUUID or region missing in response). Ensure name, tag are correct.', 'details' => $accountDataResponse];
                $apiErrorDetails['attempted_endpoint'] = $attemptedAccountEndpoint;
            }
        }
    }
}

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
                <li><a href="player_stats.php" class="active">Player Stats</a></li>
                <li><a href="leaderboard.php">Leaderboard</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="player-search">
            <h2>Search Player Stats</h2>
            <form method="POST" action="player_stats.php">
                <div>
                    <label for="player_name_tag">Player Name & Tag (e.g., PlayerName#TAG):</label>
                    <input type="text" id="player_name_tag" name="player_name_tag" required value="<?php echo isset($_POST['player_name_tag']) ? htmlspecialchars($_POST['player_name_tag']) : ''; ?>">
                </div>
                <input type="submit" value="Search Stats">
            </form>

            <?php if ($apiErrorDetails): ?>
                <div class="error-message">
                    <h4>API Error Details (Player Stats):</h4>
                    <?php if (!empty($apiErrorDetails['attempted_endpoint'])): ?>
                         <p><strong>Attempted Endpoint(s):</strong><br>
                            <?php echo htmlspecialchars($attemptedAccountEndpoint);?><br>
                            <?php if($attemptedMmrEndpoint) echo htmlspecialchars($attemptedMmrEndpoint); ?></p>
                    <?php endif; ?>
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
                    ?>  <p><strong>API Errors:</strong></p>
                        <pre><?php echo htmlspecialchars(print_r($apiErrorDetails['errors'], true)); ?></pre>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($playerStatsDisplay && !$apiErrorDetails): ?>
                <h3>Stats for <?php echo htmlspecialchars($accountInfo['name'] ?? '') . "#" . htmlspecialchars($accountInfo['tag'] ?? ''); ?> 
                    (Region: <?php echo htmlspecialchars(strtoupper($accountInfo['region'] ?? 'N/A')); ?>, 
                     Level: <?php echo htmlspecialchars($accountInfo['account_level'] ?? 'N/A'); ?>)
                </h3>

                <!-- Sub Navigation -->
                <div class="sub-nav">
                    <ul>
                        <li><a href="#overview" class="active" onclick="showSection('overview')">Overview</a></li>
                        <li><a href="#match-history" onclick="showSection('match-history')">Match History</a></li>
                    </ul>
                </div>

                <!-- Overview Section -->
                <div id="overview" class="stats-section">
                    <div class="card-container">
                        <!-- Combined Player Card + Current Rank -->
                        <div class="card"> 
                            <?php if (isset($accountInfo['card']['small'])): ?>
                                <img src="<?php echo htmlspecialchars($accountInfo['card']['small']); ?>" alt="Player Card" class="card-img-top" style="max-width: 150px; width: 100%; height: auto; display: block; margin-left: auto; margin-right: auto; padding-top: 15px; padding-bottom: 10px; border-radius: 4px;">
                            <?php endif; ?>
                            <div class="card-body" style="text-align: center;">
                                <h4>Current Rank</h4>
                                <?php if($playerStatsDisplay['current_rank_image']): ?>
                                    <img src="<?php echo htmlspecialchars($playerStatsDisplay['current_rank_image']); ?>" alt="<?php echo htmlspecialchars($playerStatsDisplay['current_rank']); ?>" style="width:80px;height:80px; display: block; margin: 10px auto;">
                                <?php endif; ?>
                                <p><strong>Rank:</strong> <?php echo htmlspecialchars($playerStatsDisplay['current_rank']); ?></p>
                                <p><strong>Elo:</strong> <?php echo htmlspecialchars($playerStatsDisplay['current_elo']); ?></p>
                                <p><strong>RR in Tier:</strong> <?php echo htmlspecialchars($playerStatsDisplay['ranking_in_tier']); ?>/100 rr</p>
                                <p><strong>Last Match MMR Change:</strong> <?php echo htmlspecialchars($playerStatsDisplay['mmr_change']); ?></p>
                            </div>
                        </div>

                        <div class="card" style="text-align: center;">
                            <h4>Highest Rank Achieved</h4>
                             <?php if($playerStatsDisplay['highest_rank_image']): ?>
                                <img src="<?php echo htmlspecialchars($playerStatsDisplay['highest_rank_image']); ?>" alt="<?php echo htmlspecialchars($playerStatsDisplay['highest_rank']); ?>" style="width:80px;height:80px; display: block; margin: 10px auto;">
                            <?php endif; ?>
                            <p><strong>Rank:</strong> <?php echo htmlspecialchars($playerStatsDisplay['highest_rank']); ?></p>
                            <p><strong>Season:</strong> <?php echo htmlspecialchars($playerStatsDisplay['highest_rank_season']); ?></p>
                        </div>
                    </div>

                    <?php if(!empty($playerStatsDisplay['by_season'])): ?>
                        <h4>Seasonal Performance (from /v2/mmr)</h4>
                        <div class="card-container">
                            <?php foreach($playerStatsDisplay['by_season'] as $season_id => $season_details): ?>
                            <div class="card">
                                <h5>Season: <?php echo htmlspecialchars($season_id);?></h5>
                                <p><strong>Rank:</strong> <?php echo htmlspecialchars($season_details['rank']); ?></p>
                                <p><strong>Wins:</strong> <?php echo htmlspecialchars($season_details['wins']); ?></p>
                                <p><strong>Games Played:</strong> <?php echo htmlspecialchars($season_details['games']); ?></p>
                                <?php if ($season_details['games'] > 0): ?>
                                    <p><strong>Win Rate:</strong> <?php echo round(($season_details['wins'] / $season_details['games']) * 100, 1); ?>%</p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Current Act Stats -->
                    <?php
                    if (isset($accountInfo['name']) && isset($accountInfo['tag']) && isset($accountInfo['region'])) {
                        $matchesEndpoint = "/v3/matches/{$accountInfo['region']}/{$accountInfo['name']}/{$accountInfo['tag']}?size=20";
                        $matchesResponse = callValorantApi($matchesEndpoint);
                        
                        if (!isset($matchesResponse['error']) && isset($matchesResponse['data'])) {
                            $totalKills = 0;
                            $totalDeaths = 0;
                            $totalHeadshots = 0;
                            $totalShots = 0;
                            $gamesCounted = 0;
                            
                            foreach ($matchesResponse['data'] as $match) {
                                if ($match['metadata']['mode'] === 'Competitive') {
                                    foreach ($match['players']['all_players'] as $player) {
                                        if ($player['name'] === $accountInfo['name'] && $player['tag'] === $accountInfo['tag']) {
                                            $totalKills += $player['stats']['kills'];
                                            $totalDeaths += $player['stats']['deaths'];
                                            $totalHeadshots += $player['stats']['headshots'];
                                            // Only add shots_fired if it exists
                                            if (isset($player['stats']['shots_fired'])) {
                                                $totalShots += $player['stats']['shots_fired'];
                                            }
                                            $gamesCounted++;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            if ($gamesCounted > 0) {
                                $kdRatio = $totalDeaths > 0 ? round($totalKills / $totalDeaths, 2) : $totalKills;
                                // Only calculate headshot percentage if we have shots data
                                $headshotPercentage = $totalShots > 0 ? round(($totalHeadshots / $totalShots) * 100, 1) : 'N/A';
                                ?>
                                <div class="card">
                                    <h4>Current Act Performance</h4>
                                    <p><strong>K/D Ratio:</strong> <?php echo $kdRatio; ?></p>
                                    <p><strong>Headshot Percentage:</strong> <?php echo $headshotPercentage; ?>%</p>
                                    <p><strong>Games Analyzed:</strong> <?php echo $gamesCounted; ?></p>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>

                <!-- Match History Section -->
                <div id="match-history" class="stats-section" style="display: none;">
                    <?php
                    if (isset($accountInfo['name']) && isset($accountInfo['tag']) && isset($accountInfo['region'])) {
                        $matchHistoryEndpoint = "/v3/matches/{$accountInfo['region']}/{$accountInfo['name']}/{$accountInfo['tag']}?size=10";
                        $matchHistoryResponse = callValorantApi($matchHistoryEndpoint);
                        
                        if (isset($matchHistoryResponse['error'])) {
                            echo '<div class="error-message">';
                            echo '<p>Error fetching match history: ' . htmlspecialchars($matchHistoryResponse['error']) . '</p>';
                            echo '</div>';
                        } elseif (isset($matchHistoryResponse['data'])) {
                            echo '<div class="match-history-container">';
                            // Sort matches by date (most recent first)
                            $matches = $matchHistoryResponse['data'];
                            // Filter for competitive games only
                            $matches = array_filter($matches, function($match) {
                                return $match['metadata']['mode'] === 'Competitive';
                            });
                            // Sort the filtered matches
                            usort($matches, function($a, $b) {
                                return strtotime($b['metadata']['game_start']) - strtotime($a['metadata']['game_start']);
                            });
                            
                            if (empty($matches)) {
                                echo '<div class="no-matches">No competitive matches found in recent history.</div>';
                            } else {
                                foreach ($matches as $match) {
                                    $playerStats = null;
                                    foreach ($match['players']['all_players'] as $player) {
                                        if ($player['name'] === $accountInfo['name'] && $player['tag'] === $accountInfo['tag']) {
                                            $playerStats = $player;
                                            break;
                                        }
                                    }
                                    
                                    if ($playerStats) {
                                        echo '<div class="match-card">';
                                        echo '<div class="match-header">';
                                        // Add date and time
                                        $matchDate = date('M d, Y H:i', strtotime($match['metadata']['game_start']));
                                        echo '<div class="match-info">';
                                        echo '<span class="map-name">' . htmlspecialchars($match['metadata']['map']) . '</span>';
                                        echo '<span class="match-date">' . htmlspecialchars($matchDate) . '</span>';
                                        echo '</div>';
                                        
                                        // Fix team result check
                                        $playerTeam = $playerStats['team'];
                                        $hasWon = isset($match['teams'][$playerTeam]) && $match['teams'][$playerTeam]['has_won'];
                                        echo '<span class="match-result ' . ($hasWon ? 'win' : 'loss') . '">';
                                        echo $hasWon ? 'Victory' : 'Defeat';
                                        echo '</span>';
                                        echo '</div>';
                                        
                                        echo '<div class="match-stats">';
                                        echo '<div class="player-performance">';
                                        echo '<span>K/D/A: ' . htmlspecialchars($playerStats['stats']['kills']) . '/' . 
                                             htmlspecialchars($playerStats['stats']['deaths']) . '/' . 
                                             htmlspecialchars($playerStats['stats']['assists']) . '</span>';
                                        echo '</div>';
                                        
                                        echo '<div class="team-performance">';
                                        echo '<div class="team team-' . strtolower($playerTeam) . '">';
                                        echo '<h4>Your Team</h4>';
                                        foreach ($match['players']['all_players'] as $teammate) {
                                            if ($teammate['team'] === $playerTeam) {
                                                echo '<div class="teammate">';
                                                echo '<span class="name">' . htmlspecialchars($teammate['name']) . '</span>';
                                                echo '<span class="kda">' . htmlspecialchars($teammate['stats']['kills']) . '/' . 
                                                     htmlspecialchars($teammate['stats']['deaths']) . '/' . 
                                                     htmlspecialchars($teammate['stats']['assists']) . '</span>';
                                                echo '</div>';
                                            }
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            <?php elseif (!$apiErrorDetails): ?>
                <p>Enter a player name and tag to search for their stats.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Valorant Stats Website. API by HenrikDev.</p>
    </footer>

    <script>
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.stats-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Show selected section
        document.getElementById(sectionId).style.display = 'block';
        
        // Update active state in sub-nav
        document.querySelectorAll('.sub-nav a').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`.sub-nav a[href="#${sectionId}"]`).classList.add('active');
    }
    </script>
</body>
</html> 
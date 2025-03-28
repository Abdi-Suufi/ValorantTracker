<?php
require_once __DIR__ . '/vendor/autoload.php'; // Path to autoload.php of Composer

use Dotenv\Dotenv;

// Load environment variables from .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$usernameTag = $input['usernameTag'];

// Remove any whitespace characters from the usernameTag
$usernameTag = str_replace(' ', '', $usernameTag);

if (!$usernameTag || strpos($usernameTag, '#') === false) {
    echo json_encode(["error" => "Invalid format. Use 'username#tag'"]);
    exit();
}

list($username, $tag) = explode('#', $usernameTag, 2);

$account_url = "https://api.henrikdev.xyz/valorant/v1/account/$username/$tag";
$headers = [
    "accept: application/json",
    "Authorization: " . $_ENV['API_KEY']  // Access API key from environment variable
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $account_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(["error" => "HTTP error occurred while fetching account info", "code" => $http_code]);
    exit();
}

$account_data = json_decode($response, true);

if (!isset($account_data['data']['region'])) {
    echo json_encode(["error" => "Region not found in account data"]);
    exit();
}

$region = $account_data['data']['region'];

$rank_url = "https://api.henrikdev.xyz/valorant/v2/mmr/$region/$username/$tag";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $rank_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(["error" => "HTTP error occurred while fetching rank info", "code" => $http_code]);
    exit();
}

$data = json_decode($response, true);

if (isset($data['data']['current_data']['currenttierpatched'])) {
    $current_rank = $data['data']['current_data']['currenttierpatched'];
    $current_rank_image = $data['data']['current_data']['images']['large'];
    $current_elo = $data['data']['current_data']['elo'];
    $ranking_in_tier = $data['data']['current_data']['ranking_in_tier'] ?? 0;
    $mmr_change = $data['data']['current_data']['mmr_change_to_last_game'] ?? 0;
    
    // Get current season stats
    $current_season = array_key_first($data['data']['by_season']);
    $current_season_stats = $data['data']['by_season'][$current_season] ?? null;
    $season_wins = $current_season_stats['wins'] ?? 0;
    $season_games = $current_season_stats['number_of_games'] ?? 0;
    $season_winrate = $season_games > 0 ? round(($season_wins / $season_games) * 100, 1) : 0;

    $highest_rank = $data['data']['highest_rank']['patched_tier'] ?? 'N/A';
    $highest_rank_image = isset($data['data']['highest_rank']['tier'])
        ? "https://media.valorant-api.com/competitivetiers/03621f52-342b-cf4e-4f86-9350a49c6d04/{$data['data']['highest_rank']['tier']}/largeicon.png"
        : '';
    $highest_rank_season = $data['data']['highest_rank']['season'] ?? 'Unknown Season';

    echo json_encode([
        "current_rank" => $current_rank,
        "current_rank_image" => $current_rank_image,
        "current_elo" => $current_elo,
        "ranking_in_tier" => $ranking_in_tier,
        "mmr_change" => $mmr_change,
        "season_wins" => $season_wins,
        "season_games" => $season_games,
        "season_winrate" => $season_winrate,
        "highest_rank" => $highest_rank,
        "highest_rank_image" => $highest_rank_image,
        "highest_rank_season" => $highest_rank_season
    ]);
} else {
    echo json_encode(["error" => "Rank data not found in API response"]);
}

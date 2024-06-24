<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$region = $input['region'];
$username = $input['username'];
$tag = $input['tag'];

if (!$region || !$username || !$tag) {
    echo json_encode(["error" => "Missing region, username, or tag"]);
    exit();
}

$url = "https://api.henrikdev.xyz/valorant/v2/mmr/$region/$username/$tag";
$headers = [
    "accept: application/json",
    "Authorization: HDEV-0fe3cd31-144b-48b3-9841-02c1183ccbe1"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code != 200) {
    echo json_encode(["error" => "HTTP error occurred: $http_code"]);
    curl_close($ch);
    exit();
}

$data = json_decode($response, true);
curl_close($ch);

if (isset($data['data']['current_data']['currenttierpatched'])) {
    $current_rank = $data['data']['current_data']['currenttierpatched'];
    $current_rank_image = $data['data']['current_data']['images']['large'];

    $highest_rank = isset($data['data']['highest_rank']['patched_tier']) ? $data['data']['highest_rank']['patched_tier'] : 'N/A';
    $highest_rank_image = isset($data['data']['highest_rank']['tier']) ? "https://media.valorant-api.com/competitivetiers/03621f52-342b-cf4e-4f86-9350a49c6d04/{$data['data']['highest_rank']['tier']}/largeicon.png" : '';

    echo json_encode([
        "current_rank" => $current_rank,
        "current_rank_image" => $current_rank_image,
        "highest_rank" => $highest_rank,
        "highest_rank_image" => $highest_rank_image
    ]);
} else {
    echo json_encode(["error" => "Rank data not found in API response"]);
}

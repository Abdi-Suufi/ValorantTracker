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

$url = "https://api.henrikdev.xyz/valorant/v1/mmr/$region/$username/$tag";
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

if (isset($data['data']['currenttierpatched'])) {
    $current_rank = $data['data']['currenttierpatched'];
    echo json_encode(["current_rank" => $current_rank]);
} else {
    echo json_encode(["error" => "Rank data not found in API response"]);
}
?>

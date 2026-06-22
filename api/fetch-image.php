<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$url = isset($input['url']) ? trim($input['url']) : null;

if (!$url) {
    http_response_code(400);
    echo json_encode(["error" => "No URL provided"]);
    exit;
}

$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
    ]
];
$context = stream_context_create($options);

$imageData = @file_get_contents($url, false, $context);

if ($imageData === false) {
    http_response_code(500);
    echo json_encode(["error" => "The PHP backend server was unable to retrieve this protected resource."]);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageData);
$base64 = base64_encode($imageData);

echo json_encode([
    "dataURL" => "data:" . $mimeType . ";base64," . $base64
]);
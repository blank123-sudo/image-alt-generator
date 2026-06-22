<?php
// 1. Allow your frontend index.html to communicate smoothly
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// 2. Safeguard: Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

// 3. Read the incoming raw JSON payload
$input = json_decode(file_get_contents("php://input"), true);
$url = isset($input['url']) ? trim($input['url']) : null;

if (!$url) {
    http_response_code(400);
    echo json_encode(["error" => "No URL provided"]);
    exit;
}

// 4. Use cURL instead of file_get_contents for a bulletproof fetch connection
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow any URL redirects automatically
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Prevent SSL certificate handshake errors locally
curl_setopt($ch, CURLOPT_TIMEOUT, 15);           // Cut off hanging requests after 15 seconds

// Mimic a completely realistic, modern web browser to bypass strict firewalls
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

// Execute the download request
$imageData = curl_exec($ch);
$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// 5. Verify the data package came through intact
if ($imageData === false || $httpStatusCode !== 200) {
    http_response_code(500);
    echo json_encode([
        "error" => "The PHP backend server was unable to retrieve this protected resource.",
        "debug_code" => $httpStatusCode
    ]);
    exit;
}

// 6. Automatically fallback to standard image/jpeg if MIME-type is missing
$mimeType = $contentType ? $contentType : 'image/jpeg';
$base64 = base64_encode($imageData);

// 7. Hand the cleanly prepared Data URL back to index.html
echo json_encode([
    "dataURL" => "data:" . $mimeType . ";base64," . $base64
]);

<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$phoneNumber = isset($_GET['no']) ? $_GET['no'] : '';

if (empty($phoneNumber)) {
    echo json_encode([
        'author' => '@selfphp',
        'firma' => 'A101',
        'sms_durumu' => false,
        'is_success' => false,
        'error_code' => 'MISSING_PARAMETER',
        'message' => 'Telefon numarası eksik. Kullanım: a101.php?no=5555555555',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$formattedPhone = '90' . $phoneNumber;
$apiUrl = "https://rio.a101.com.tr/dbmk89vnr/CALL/MsisdnAuthenticator/sendOtp/{$formattedPhone}?__culture=tr-TR&__platform=web";
$installationId = md5(uniqid(mt_rand(), true));

$userAgents = [
    'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
    'Mozilla/5.0 (Linux; Android 13; SM-A546E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Mobile Safari/537.36',
    'Mozilla/5.0 (Linux; Android 12; Pixel 6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Mobile Safari/537.36',
    'Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Mobile Safari/537.36',
    'Mozilla/5.0 (Linux; Android 13; SAMSUNG SM-S911B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/23.0 Chrome/115.0.0.0 Mobile Safari/537.36',
    'Mozilla/5.0 (Linux; Android 14; SAMSUNG SM-A546E) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/24.0 Chrome/117.0.0.0 Mobile Safari/537.36',
    'Mozilla/5.0 (iPad; CPU OS 17_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
    'Mozilla/5.0 (iPad; CPU OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
];

$randomUserAgent = $userAgents[array_rand($userAgents)];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => '{}',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json, text/plain, */*',
        'a101-user-agent: web-3.0.2',
        'installationid: ' . $installationId,
        'Origin: https://www.a101.com.tr',
        'Referer: https://www.a101.com.tr/',
        'User-Agent: ' . $randomUserAgent,
        'sec-ch-ua: "Chromium";v="154", "Google Chrome";v="154", "Not A(Brand";v="99"',
        'sec-ch-ua-mobile: ?1',
        'sec-ch-ua-platform: "iOS"',
        'sec-fetch-dest: empty',
        'sec-fetch-mode: cors',
        'sec-fetch-site: same-site',
        'Cache-Control: no-cache',
        'Pragma: no-cache'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($response === false) {
    echo json_encode([
        'author' => '@selfphp',
        'firma' => 'A101',
        'sms_durumu' => false,
        'is_success' => false,
        'error_code' => 'CURL_ERROR',
        'message' => 'API bağlantı hatası: ' . $curlError,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if ($httpCode == 200) {
    $apiResponse = json_decode($response, true);
    $isSuccess = true;
    
    if (isset($apiResponse['error']) || (isset($apiResponse['success']) && $apiResponse['success'] === false)) {
        $isSuccess = false;
    }
    
    $customResponse = [
        'author' => '@selfphp',
        'firma' => 'A101',
        'sms_durumu' => $isSuccess,
        'telefon' => $phoneNumber,
        'formatted_phone' => $formattedPhone,
        'used_user_agent' => $randomUserAgent,
        'installation_id' => $installationId,
        'api_response' => $apiResponse
    ];
    
    echo json_encode($customResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    $apiResponse = json_decode($response, true);
    
    echo json_encode([
        'author' => '@selfphp',
        'firma' => 'A101',
        'sms_durumu' => false,
        'is_success' => false,
        'error_code' => 'HTTP_ERROR',
        'message' => 'HTTP Hata Kodu: ' . $httpCode,
        'telefon' => $phoneNumber,
        'response' => $apiResponse
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>

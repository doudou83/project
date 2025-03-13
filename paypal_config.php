<?php
// Configuration PayPal
define('PAYPAL_CLIENT_ID', 'your-client-id');
define('PAYPAL_SECRET', 'your-secret');
define('PAYPAL_BASE_URL', 'https://api.sandbox.paypal.com'); // Utiliser 'https://api.paypal.com' pour la production

// Créer une transaction PayPal
function createPaypalTransaction($amount, $currency = 'USD') {
    $url = PAYPAL_BASE_URL . '/v1/payments/payment';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . getPaypalAccessToken()
    ];

    $body = json_encode([
        'intent' => 'sale',
        'payer' => [
            'payment_method' => 'paypal'
        ],
        'transactions' => [
            [
                'amount' => [
                    'total' => $amount,
                    'currency' => $currency
                ],
                'description' => 'Paiement de la formation e-learning'
            ]
        ],
        'redirect_urls' => [
            'return_url' => 'http://yourdomain.com/success.php',
            'cancel_url' => 'http://yourdomain.com/cancel.php'
        ]
    ]);

    $response = sendHttpRequest($url, $headers, $body);
    return json_decode($response, true);
}

// Fonction pour obtenir un access token PayPal
function getPaypalAccessToken() {
    $url = PAYPAL_BASE_URL . '/v1/oauth2/token';
    $headers = [
        'Authorization: Basic ' . base64_encode(PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET),
        'Content-Type: application/x-www-form-urlencoded'
    ];
    $body = 'grant_type=client_credentials';

    $response = sendHttpRequest($url, $headers, $body, 'POST');
    $json = json_decode($response, true);
    return $json['access_token'];
}

// Fonction pour envoyer une requête HTTP
function sendHttpRequest($url, $headers, $body, $method = 'POST') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
?>
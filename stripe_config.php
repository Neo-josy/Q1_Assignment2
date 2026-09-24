<?php
$stripeSecret = require __DIR__ . '/stripe_secret.php';

define('SHOP_URL', 'http://localhost/Q1_Assignment2');

function stripeRequest($method, $path, $data = [])
{
    global $stripeSecret;

    $curl = curl_init('https://api.stripe.com/v1/' . $path);

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => $stripeSecret . ':',
        CURLOPT_TIMEOUT => 30
    ]);

    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            http_build_query($data)
        );
    }

    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);

    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('Stripe connection failed: ' . $error);
    }

    $result = json_decode($response, true);

    if ($status < 200 || $status >= 300 || !is_array($result)) {
        throw new RuntimeException('Stripe API request failed.');
    }

    return $result;
}
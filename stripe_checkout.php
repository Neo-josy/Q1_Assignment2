<?php
session_start();
require_once __DIR__ . '/stripe_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Use the checkout page to start payment.');
}

$paymentMethod = $_POST['payment_method'] ?? 'card';

if (!in_array($paymentMethod, ['card', 'alipay'], true)) {
    http_response_code(400);
    exit('Invalid payment method.');
}

$rawCart = $_POST['cart'] ?? '';
$email = $_POST['email'] ?? '';

if (
    !is_string($rawCart) ||
    !is_string($email) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    http_response_code(400);
    exit('Invalid checkout details.');
}

$cart = json_decode($rawCart, true);

// Trusted server-side prices, in cents.
$products = [
    'bronton' => ['name' => 'Bronton', 'price' => 300000],
    'ebmx' => ['name' => 'E-BMX', 'price' => 200000],
    'f65' => ['name' => 'F-65', 'price' => 70000]
];

if (
    !is_array($cart) ||
    !array_is_list($cart) ||
    count($cart) < 1 ||
    count($cart) > count($products)
) {
    http_response_code(400);
    exit('Invalid cart.');
}

$lineItems = [];
$total = 0;
$seen = [];

foreach ($cart as $item) {
    if (!is_array($item)) {
        http_response_code(400);
        exit('Invalid cart item.');
    }

    $id = $item['id'] ?? null;
    $quantity = $item['quantity'] ?? null;

    if (
        !is_string($id) ||
        !isset($products[$id]) ||
        isset($seen[$id]) ||
        !is_int($quantity) ||
        $quantity < 1 ||
        $quantity > 99
    ) {
        http_response_code(400);
        exit('Invalid product or quantity.');
    }

    $seen[$id] = true;
    $product = $products[$id];
    $total += $product['price'] * $quantity;

    $lineItems[] = [
        'price_data' => [
            'currency' => 'aud',
            'product_data' => [
                'name' => $product['name']
            ],
            'unit_amount' => $product['price']
        ],
        'quantity' => $quantity
    ];
}

try {
    $checkout = stripeRequest('POST', 'checkout/sessions', [
        'mode' => 'payment',
        'payment_method_types' => [$paymentMethod],
        'customer_email' => $email,
        'billing_address_collection' => 'required',
        'line_items' => $lineItems,
        'success_url' =>
            SHOP_URL . '/stripe_success.php'
            . '?session_id={CHECKOUT_SESSION_ID}'
    ]);

    // Remember which payment belongs to this browser session.
    $_SESSION['stripe_orders'][$checkout['id']] = [
        'total' => $total,
        'currency' => 'aud'
    ];

    header('Location: ' . $checkout['url'], true, 303);
    exit;

} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(502);
    exit('Could not start payment. Return to checkout and try again.');
}
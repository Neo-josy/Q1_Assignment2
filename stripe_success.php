<?php
session_start();
require_once __DIR__ . '/stripe_config.php';

$message = 'Payment could not be confirmed.';
$amount = null;

$id = $_GET['session_id'] ?? '';

if (
    is_string($id) &&
    isset($_SESSION['stripe_orders'][$id])
) {
    try {
        $order = $_SESSION['stripe_orders'][$id];

        $checkout = stripeRequest(
            'GET',
            'checkout/sessions/' . rawurlencode($id)
        );

        if (
            $checkout['payment_status'] === 'paid' &&
            $checkout['amount_total'] === $order['total'] &&
            $checkout['currency'] === $order['currency'] &&
            $checkout['livemode'] === false
        ) {
            $message = 'Your test payment was successful.';
            $amount = $checkout['amount_total'] / 100;
        } else {
            $message = 'Payment is not confirmed as paid.';
        }

    } catch (Throwable $error) {
        error_log($error->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment result</title>
</head>
<body>
    <h1>Payment information</h1>

    <p>
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <?php if ($amount !== null): ?>
        <p>
            Amount: AUD <?= number_format($amount, 2) ?>
        </p>
    <?php endif; ?>

    <a href="index.php">Back to products</a>
</body>
</html>
<?php
include_once 'paypal_config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checkout | Alice's Bike Shop</title>
  <link href="assets/css/bootstrap.css" rel="stylesheet">
</head>

<body>
  <main class="container">
    <h1>Billing and Payment</h1>

    <p><a href="chart.php">Return to cart</a></p>

    <h3>Order summary</h3>
    <ul id="order-items"></ul>
    <h4 id="order-total"></h4>

    <form id="billing-form">
      <div class="form-group">
        <label for="full-name">Full name</label>
        <input
          id="full-name"
          name="fullName"
          class="form-control"
          autocomplete="name"
          required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input
          id="email"
          name="email"
          type="email"
          class="form-control"
          autocomplete="email"
          required>
      </div>

      <div class="form-group">
        <label for="address">Street address</label>
        <input
          id="address"
          name="address"
          class="form-control"
          autocomplete="street-address"
          required>
      </div>

      <div class="form-group">
        <label for="city">City</label>
        <input
          id="city"
          name="city"
          class="form-control"
          autocomplete="address-level2"
          required>
      </div>

      <div class="form-group">
        <label for="state">State or territory</label>
        <input
          id="state"
          name="state"
          class="form-control"
          autocomplete="address-level1"
          required>
      </div>

      <div class="form-group">
        <label for="postcode">Australian postcode</label>
        <input
          id="postcode"
          name="postcode"
          class="form-control"
          autocomplete="postal-code"
          inputmode="numeric"
          pattern="[0-9]{4}"
          maxlength="4"
          required>
      </div>
    </form>
<!--PAYPAL-->
  <div id="paypal">
    <h3> Paypal </h3>

    <form
      id="paypal-form"
      action="<?php echo htmlspecialchars(PAYPAL_URL, ENT_QUOTES, 'UTF-8'); ?>"
      method="post">

      <input type="hidden" name="cmd" value="_xclick">

      <input
        type="hidden"
        name="business"
        value="<?php echo htmlspecialchars(PAYPAL_ID, ENT_QUOTES, 'UTF-8'); ?>">

      <input
        type="hidden"
        name="item_name"
        value="Alice's Electronic Bike Shop - Cart Order">

      <input
        type="hidden"
        name="amount"
        id="paypal-amount"
        value="">

      <input
        type="hidden"
        name="currency_code"
        value="<?php echo htmlspecialchars(PAYPAL_CURRENCY, ENT_QUOTES, 'UTF-8'); ?>">

      <input
        type="hidden"
        name="return"
        value="<?php echo htmlspecialchars(PAYPAL_RETURN_URL, ENT_QUOTES, 'UTF-8'); ?>">

      <input
        type="hidden"
        name="notify_url"
        value="<?php echo htmlspecialchars(PAYPAL_NOTIFY_URL, ENT_QUOTES, 'UTF-8'); ?>">

      <button
        id="paypal-button"
        type="submit"
        class="btn btn-primary"
        disabled>
        Continue to PayPal
      </button>
    </form>

    <p id="paypal-status" role="status"></p>
</div>
<!--GOOGLE PAY-->
<div id="Google_pay">
    <h3>Google Pay</h3>
    <p> checkout .</p>

    <div id="google-pay-container"></div>
    <p id="payment-status" role="status">Loading Google Pay...</p>
</div>

<!--VISA-->

<div id="Visa" hidden>
    <h3>Visa Card payment</h3>

    <form
        id="visa-form"
        action="stripe_checkout.php"
        method="post">

        <input type="hidden" name="payment_method" value="card">
        <input type="hidden" name="cart" id="visa-cart">
        <input type="hidden" name="email" id="visa-email">

        <button type="submit" class="btn btn-primary">
            Continue to card payment
        </button>
    </form>
</div>

<!--Alipay-->

<div id="Alipay" hidden>
    <h3>Alipay</h3>

    <form
        id="alipay-form"
        action="stripe_checkout.php"
        method="post">

        <input type="hidden" name="payment_method" value="alipay">
        <input type="hidden" name="cart" id="alipay-cart">
        <input type="hidden" name="email" id="alipay-email">

        <button type="submit" class="btn btn-primary">
            Continue to Alipay
        </button>
    </form>
</div>

</main>
  <script src="cart.js"></script>

<script>
  const selectedPayment =
    new URLSearchParams(window.location.search).get('payment');

  document.getElementById('paypal').hidden =
    selectedPayment !== 'paypal';

  document.getElementById('Google_pay').hidden =
    selectedPayment !== 'Google_pay';

  document.getElementById('Visa').hidden =
    selectedPayment !== 'Visa';
  
  document.getElementById('Alipay').hidden=
    selectedPayment !== 'Alipay';

</script>

  <script>
  
    const checkoutCart = getCart();
    const checkoutTotalCents = getTotalCents(checkoutCart);

    if (checkoutCart.length === 0) {
      window.location.replace('chart.php');
    } else {
      const list = document.getElementById('order-items');

      checkoutCart.forEach(item => {
        const product = PRODUCTS[item.id];
        const line = document.createElement('li');

        line.textContent =
          product.name + ' × ' + item.quantity + ' — ' +
          formatMoney(product.priceCents * item.quantity);

        list.appendChild(line);
      });

      document.getElementById('order-total').textContent =
        'Total: ' + formatMoney(checkoutTotalCents) + ' AUD';
    }

    document.getElementById('billing-form')
      .addEventListener('submit', event => {
        event.preventDefault();
      });
  
  const paypalForm = document.getElementById('paypal-form');
  const paypalButton = document.getElementById('paypal-button');
  const paypalAmount = document.getElementById('paypal-amount');
  const paypalStatus = document.getElementById('paypal-status');

  // Convert the cart total from cents to dollars.
  paypalAmount.value = (checkoutTotalCents / 100).toFixed(2);

  paypalButton.disabled = checkoutTotalCents <= 0;

  paypalForm.addEventListener('submit', function(event) {
    const billingForm = document.getElementById('billing-form');

    // Check all required billing fields.
    if (!billingForm.reportValidity()) {
      event.preventDefault();

      paypalStatus.textContent =
        'Please complete the required billing details.';

      return;
    }

    if (checkoutTotalCents <= 0) {
      event.preventDefault();
      paypalStatus.textContent = 'Your cart is empty.';
      return;
    }

  
    paypalAmount.value =
      (checkoutTotalCents / 100).toFixed(2);

    paypalStatus.textContent = 'Continuing to PayPal...';

  });
  </script>

<script>
  document.getElementById('visa-form')
    .addEventListener('submit', function (event) {
        const billing = document.getElementById('billing-form');
        const cart = getCart();

        if (!billing.reportValidity() || cart.length === 0) {
            event.preventDefault();
            return;
        }

        document.getElementById('visa-cart').value =
            JSON.stringify(cart);

        document.getElementById('visa-email').value =
            document.getElementById('email').value;
    });
</script>

<script>
document.getElementById('alipay-form')
    .addEventListener('submit', function (event) {
        const billing = document.getElementById('billing-form');
        const cart = getCart();

        if (!billing.reportValidity() || cart.length === 0) {
            event.preventDefault();
            return;
        }

        document.getElementById('alipay-cart').value =
            JSON.stringify(cart);

        document.getElementById('alipay-email').value =
            document.getElementById('email').value;
    });
</script>



  <script src="google_pay.js"></script>

  <script
    async
    src="https://pay.google.com/gp/p/js/pay.js"
    onload="onGooglePayLoaded()"
    onerror="showPaymentStatus('Google Pay could not load. Check your internet connection.')">
  </script>
</body>
</html>
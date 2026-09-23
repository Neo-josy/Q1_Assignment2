// Prices are stored in cents to avoid decimal rounding problems.
const PRODUCTS = {
  bronton: {
    name: 'Bronton',
    priceCents: 300000,
    image: 'assets/img/bronton.jpg'
  },
  ebmx: {
    name: 'E-BMX',
    priceCents: 200000,
    image: 'assets/img/dummyimg.jpg'
  },
  f65: {
    name: 'F-65',
    priceCents: 70000,
    image: 'assets/img/f65.jpg'
  }
};

const CART_KEY = 'alice-bike-cart';

function getCart() {
  try {
    const saved = JSON.parse(sessionStorage.getItem(CART_KEY));

    if (!Array.isArray(saved)) {
      return [];
    }

    // Only accept known products and valid quantities.
    return saved.filter(item =>
      item &&
      Object.prototype.hasOwnProperty.call(PRODUCTS, item.id) &&
      Number.isInteger(item.quantity) &&
      item.quantity >= 1 &&
      item.quantity <= 99
    );
  } catch {
    return [];
  }
}

function saveCart(cart) {
  sessionStorage.setItem(CART_KEY, JSON.stringify(cart));
}

function addToCart(id) {
  if (!Object.prototype.hasOwnProperty.call(PRODUCTS, id)) {
    return;
  }

  const cart = getCart();
  const existing = cart.find(item => item.id === id);

  if (existing) {
    existing.quantity = Math.min(existing.quantity + 1, 99);
  } else {
    cart.push({ id: id, quantity: 1 });
  }

  saveCart(cart);
  window.location.href = 'chart.php';
}

function getTotalCents(cart = getCart()) {
  return cart.reduce((total, item) =>
    total + PRODUCTS[item.id].priceCents * item.quantity, 0
  );
}

function formatMoney(cents) {
  return new Intl.NumberFormat('en-AU', {
    style: 'currency',
    currency: 'AUD'
  }).format(cents / 100);
}
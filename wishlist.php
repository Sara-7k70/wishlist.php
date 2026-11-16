<<?php

require_once __DIR__ . '/../db_connect.php';

// Temporary customer ID for testing
$customer_id = 1;
// Get customer information
$customer_sql = "SELECT full_name FROM customers WHERE customer_id = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();

$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();

$customer_name = $customer['full_name'] ?? 'Guest';

$customer_stmt->close();

// Remove product from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product'])) {

    $product_id = (int) $_POST['remove_product'];

    $delete_sql = "
        DELETE FROM favorites
        WHERE customer_id = ? AND product_id = ?
    ";

    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $customer_id, $product_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    header("Location: wishlist.php");
    exit;
}
// Get wishlist products from database
$sql = "
    SELECT 
        p.product_id,
        p.product_name,
        p.category,
        p.brand,
        p.price_sar,
        p.color,
        p.stock,
        p.rating,
        f.saved_at
    FROM favorites f
    INNER JOIN products p 
        ON p.product_id = f.product_id
    WHERE f.customer_id = ?
    ORDER BY f.saved_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();

$result = $stmt->get_result();

$savedCount = $result->num_rows;

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>EcoCart AI - My Wishlist</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

:root {

  --green: #104331;

  --green-light: #e2eee6;

  --green-dark: #0c3024;

  --cream: #f5f5f0;

  --white: #ffffff;

  --text: #202923;

  --muted: #6c756f;

  --red: #b33131;

  --border: #dedfd9;

}

* {

  box-sizing: border-box;

}

body {

  margin: 0;

  padding: 20px;

  background: #121a15;

  font-family: 'Inter', Arial, sans-serif;

  color: var(--text);

  font-size: 12px;

}

.page {

  max-width: 1200px;

  margin: 0 auto;

  background: var(--cream);

  border: 1px solid #526458;

  border-radius: 38px;

  overflow: hidden;

  min-height: calc(100vh - 40px);

}

.shipping-bar {

  height: 27px;

  background: var(--green);

  color: #e7eee8;

  display: flex;

  justify-content: center;

  align-items: center;

  font-size: 10px;

  letter-spacing: .1px;

}

.navbar {

  min-height: 56px;

  background: var(--white);

  border-bottom: 1px solid #e9e9e4;

  padding: 0 32px;

  display: flex;

  align-items: center;

  gap: 34px;

}

.brand {

  display: flex;

  align-items: center;

  gap: 8px;

  white-space: nowrap;

  font-size: 14px;

  font-weight: 700;

}

.brand-icon {

  background: #226548;

  color: white;

  width: 26px;

  height: 26px;

  border-radius: 7px;

  display: grid;

  place-items: center;

}

.ai {

  background: #e5eee6;

  color: #365b45;

  padding: 3px 5px;

  border-radius: 4px;

  font-size: 9px;

  font-weight: 700;

}

.nav-links {

  display: flex;

  align-items: center;

  gap: 25px;

  flex: 1;

}

.nav-links a {

  color: #414943;

  text-decoration: none;

  white-space: nowrap;

  cursor: pointer;

}

.nav-links a:hover {

  color: var(--green);

}

.nav-actions {

  display: flex;

  align-items: center;

  gap: 6px;

}

.icon-button {

  position: relative;

  width: 34px;

  height: 34px;

  border: 1px solid #e5e5e0;

  border-radius: 11px;

  background: white;

  color: #26332b;

  display: grid;

  place-items: center;

  cursor: pointer;

}

.count {

  position: absolute;

  top: -5px;

  right: -3px;

  width: 15px;

  height: 15px;

  border-radius: 50%;

  background: #275d43;

  color: white;

  font-size: 9px;

  display: grid;

  place-items: center;

}

.avatar {

  width: 33px;

  height: 33px;

  border-radius: 50%;

  background: #ded1be;

  color: #4b4034;

  display: grid;

  place-items: center;

  font-weight: 600;

  flex-shrink: 0;

}

main {

  padding: 24px 32px 34px;

  min-height: 795px;

}

.layout {

  display: grid;

  grid-template-columns: 218px minmax(0, 1fr);

  gap: 20px;

  align-items: start;

}

.sidebar {

  background: white;

  border: 1px solid var(--border);

  border-radius: 13px;

  padding: 17px 11px 13px;

}

.profile {

  display: flex;

  align-items: center;

  gap: 10px;

  padding: 0 7px 13px;

  border-bottom: 1px solid #eeeeea;

  margin-bottom: 8px;

}

.profile-info strong {

  display: block;

  font-size: 11px;

  margin-bottom: 5px;

}

.profile-info span {

  color: var(--muted);

  font-size: 10px;

  white-space: nowrap;

}

.side-link {

  width: 100%;

  min-height: 35px;

  display: flex;

  align-items: center;

  gap: 10px;

  padding: 0 10px;

  border-radius: 9px;

  color: #525c55;

  text-decoration: none;

  font-size: 11px;

  margin: 1px 0;

  cursor: pointer;

}

.side-link svg {

  width: 14px;

  height: 14px;

  stroke-width: 1.7;

  flex-shrink: 0;

}

.side-link.active {

  background: #e2eee5;

  color: #20563c;

  font-weight: 600;

}

.content {

  min-width: 0;

}

.heading {

  display: flex;

  justify-content: space-between;

  align-items: center;

  gap: 12px;

  margin-bottom: 18px;

}

.heading h1 {

  font-size: 18px;

  line-height: 1.3;

  margin: 3px 0 5px;

  font-weight: 700;

  letter-spacing: -.5px;

}

.heading p {

  margin: 0;

  font-size: 10px;

  color: #626a63;

}

.discount {

  display: flex;

  align-items: center;

  gap: 6px;

  background: #e4efe7;

  color: #315d44;

  padding: 7px 11px;

  border-radius: 20px;

  font-size: 10px;

  white-space: nowrap;

}

.discount svg {

  width: 13px;

  height: 13px;

}

.products {

  display: grid;

  grid-template-columns: repeat(3, minmax(0, 1fr));

  gap: 14px;

}

.product-card {

  min-width: 0;

  padding: 11px;

  background: white;

  border: 1px solid #dedfd9;

  border-radius: 14px;

  position: relative;

}

.product-image {

  height: auto;

  aspect-ratio: 1 / 1;

  border-radius: 11px;

  display: flex;

  justify-content: center;

  align-items: center;

  position: relative;

  overflow: hidden;

  margin-bottom: 11px;

}
.product-photo {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 15px;
}

.watch { background: #e5e5eb; }

.glasses { background: #e9e8d9; }

.wallet { background: #eadfd6; }

.shoes { background: #e1e8e4; }

.lamp { background: #eee9dc; }

.beanie { background: #e5e2eb; }

.product-art {

  width: 42%;

  height: 42%;

  color: #59685f;

  stroke-width: 1.25;

}

.heart {

  position: absolute;

  top: 9px;

  right: 9px;

  background: rgba(255,255,255,.94);

  border: 0;

  width: 27px;

  height: 27px;

  border-radius: 50%;

  display: grid;

  place-items: center;

  color: #b33131;

  cursor: pointer;

}

.heart svg {

  width: 13px;

  height: 13px;

  fill: #b33131;

  stroke-width: 1.5;

}

.product-name {

  font-size: 11px;

  font-weight: 650;

  margin: 0 0 7px;

  line-height: 1.3;

}

.price {

  font-size: 13px;

  font-weight: 700;

  margin-bottom: 9px;

}

.price small {

  font-size: 9px;

  font-weight: 400;

}

.product-bottom {

  display: flex;

  align-items: center;

  flex-wrap: wrap;

  gap: 9px 15px;

}

button {

  font-family: inherit;

}

.add-btn {

  min-height: 29px;

  padding: 0 12px;

  background: #176044;

  border: 1px solid #0e4b34;

  outline: 1px solid #a8c3b2;

  outline-offset: 1px;

  border-radius: 7px;

  color: white;

  font-size: 10px;

  cursor: pointer;

}

.add-btn:hover {

  background: #104331;

}

.remove-btn {

  border: 0;

  padding: 5px 0;

  background: transparent;

  color: #ad2d2d;

  font-size: 10px;

  cursor: pointer;

}

.status {

  position: absolute;

  right: 10px;

  bottom: 47px;

  border-radius: 20px;

  padding: 5px 8px;

  font-size: 9px;

  white-space: nowrap;

}

.low-stock {

  background: #fbf0df;

  color: #93611d;

}

.out-stock {

  background: #fdeaea;

  color: #b23232;

}

.dot {

  display: inline-block;

  width: 5px;

  height: 5px;

  border-radius: 50%;

  background: currentColor;

  margin-right: 4px;

  vertical-align: middle;

}

.notify-btn {

  border: 1px solid #e1e1dc;

  background: white;

  padding: 7px 11px;

  border-radius: 8px;

  font-size: 10px;

  cursor: pointer;

}

footer {

  background: #103e2d;

  color: #e5eee7;

  padding: 30px 32px 22px;

}

.footer-grid {

  display: grid;

  grid-template-columns: 1.45fr .85fr .85fr 1.2fr;

  gap: 35px;

}

.footer-brand {

  color: white;

  margin-bottom: 11px;

}

.footer-description {

  max-width: 275px;

  color: #c2d3c8;

  font-size: 10px;

  line-height: 1.7;

  margin: 0;

}

.footer-column h3 {

  font-size: 11px;

  color: white;

  margin: 3px 0 13px;

}

.footer-column a {

  display: block;

  text-decoration: none;

  color: #c8d8ce;

  font-size: 10px;

  margin-bottom: 10px;

  cursor: pointer;

}

.footer-column a:hover {

  color: white;

}

.newsletter p {

  font-size: 10px;

  line-height: 1.5;

  color: #c8d8ce;

  margin: 0 0 10px;

}

.newsletter form {

  display: flex;

  gap: 9px;

}

.newsletter input {

  min-width: 0;

  width: 100%;

  padding: 10px;

  border-radius: 8px;

  background: #214f3e;

  border: 1px solid #416956;

  color: white;

  outline: none;

  font-family: inherit;

  font-size: 10px;

}

.newsletter input::placeholder {

  color: #c4d4ca;

}

.subscribe {

  padding: 0 12px;

  border: 1px solid #416956;

  border-radius: 8px;

  background: #245b43;

  color: white;

  font-size: 10px;

  white-space: nowrap;

  cursor: pointer;

}

.footer-bottom {

  border-top: 1px solid #315b47;

  margin-top: 26px;

  padding-top: 17px;

  color: #9fb9a8;

  font-size: 9px;

}

@media (min-width: 1300px) {

  main {

    min-height: 795px;

  }

}

@media (max-width: 900px) {

  body {

    padding: 10px;

  }

  .page {

    border-radius: 25px;

  }

  .navbar {

    padding: 12px 18px;

    flex-wrap: wrap;

    gap: 14px;

  }

  .nav-links {

    order: 3;

    width: 100%;

    flex-basis: 100%;

    gap: 18px;

    overflow-x: auto;

    padding: 4px 0;

  }

  .nav-actions {

    margin-left: auto;

  }

  main {

    padding: 22px 18px;

  }

  .layout {

    grid-template-columns: 175px minmax(0, 1fr);

    gap: 14px;

  }

  .products {

    grid-template-columns: repeat(2, minmax(0, 1fr));

  }

  .footer-grid {

    grid-template-columns: repeat(2, minmax(0, 1fr));

    gap: 28px;

  }

}

@media (max-width: 560px) {

  body {

    padding: 0;

  }

  .page {

    border-radius: 0;

    border: 0;

  }

  .navbar {

    padding: 12px;

  }

  .nav-links {

    gap: 17px;

  }

  main {

    padding: 18px 12px 28px;

    min-height: auto;

  }

  .layout {

    grid-template-columns: 1fr;

  }

  .sidebar {

    padding: 10px;

  }

  .profile {

    padding-bottom: 10px;

  }

  .sidebar .side-link {

    display: none;

  }

  .sidebar .side-link.active {

    display: flex;

  }

  .heading {

    align-items: flex-start;

  }

  .heading h1 {

    font-size: 19px;

  }

  .discount {

    font-size: 9px;

    padding: 7px;

  }

  .products {

    gap: 10px;

  }

  .product-card {

    padding: 8px;

  }

  .product-image {

    margin-bottom: 9px;

  }

  .product-name {

    min-height: 28px;

  }

  .product-bottom {

    gap: 8px;

  }

  .add-btn {

    padding: 0 9px;

  }

  .status {

    bottom: 47px;

    right: 7px;

    font-size: 8px;

  }

  footer {

    padding: 25px 18px 20px;

  }

  .footer-grid {

    grid-template-columns: 1fr 1fr;

    gap: 28px 18px;

  }

  .newsletter form {

    flex-direction: column;

  }

  .subscribe {

    min-height: 34px;

  }

}

</style>

</head>

<body>

<div class="page">

  <div class="shipping-bar">

    Free shipping on orders over 300 SAR · Easy 14-day returns

  </div>

  <header class="navbar">

    <div class="brand">

      <span class="brand-icon"><i data-lucide="leaf" size="16"></i></span>

      EcoCart <span class="ai">AI</span>

    </div>

    <nav class="nav-links">

      <a href="#home">Home</a>

      <a href="#categories">Categories</a>

      <a href="#recommendations">Recommendations</a>

      <a href="#offers">Offers</a>

    </nav>

    <div class="nav-actions">

      <button class="icon-button" aria-label="Search">

        <i data-lucide="search" size="17"></i>

      </button>

      <button class="icon-button" aria-label="Notifications">

        <i data-lucide="bell" size="17"></i>

        <span class="count">3</span>

      </button>

      <button class="icon-button" aria-label="Shopping cart">

        <i data-lucide="shopping-cart" size="17"></i>

        <span class="count" id="cart-count">3</span>

      </button>

      <button class="icon-button" aria-label="Settings">

        <i data-lucide="sun" size="17"></i>

      </button>

      <div class="avatar">SQ</div>

    </div>

  </header>

  <main>

    <div class="layout">

      <aside class="sidebar">

        <div class="profile">

          <div class="avatar">SQ</div>

          <div class="profile-info">

            <strong><?php echo htmlspecialchars($customer_name); ?></strong>

            <span>Member since Mar 2025</span>

          </div>

        </div>

        <a class="side-link" href="#profile">

          <i data-lucide="user-round"></i> My Profile

        </a>

        <a class="side-link" href="#security">

          <i data-lucide="shield-check"></i> Account &amp; Security

        </a>

        <a class="side-link" href="#orders">

          <i data-lucide="package"></i> My Orders

        </a>

        <a class="side-link active" href="#wishlist">

          <i data-lucide="heart"></i> My Wishlist

        </a>

        <a class="side-link" href="#addresses">

          <i data-lucide="map-pin"></i> My Addresses

        </a>

        <a class="side-link" href="#insights">

          <i data-lucide="chart-column"></i> Shopping Insights

        </a>

        <a class="side-link" href="#notifications">

          <i data-lucide="bell"></i> Notifications

        </a>

        <a class="side-link" href="#logout">

          <i data-lucide="log-out"></i> Log out

        </a>

      </aside>

      <section class="content" id="wishlist">

        <div class="heading">

          <div>

            <h1>My wishlist</h1>

            <p>
    <span id="saved-count">
        <?php echo $savedCount; ?>
    </span>
    saved products
</p>

          </div>

          <div class="discount">

            <i data-lucide="tag"></i>

            GREEN15 applies to 3 items

          </div>

        </div>

        <div class="products" id="products">

    <?php if ($savedCount > 0): ?>

        <?php while ($product = $result->fetch_assoc()): ?>

            <article class="product-card">

                <div class="product-image" style="background: #ffffff;">

    <img
        class="product-photo"
        src="../product/product-<?php echo str_pad($product['product_id'], 3, '0', STR_PAD_LEFT); ?>.jpg"
        alt="<?php echo htmlspecialchars($product['product_name']); ?>"
    >
                    <button class="heart"
                            aria-label="Remove from favorites">

                        <i data-lucide="heart"></i>

                    </button>

                </div>

                <h3 class="product-name">
                    <?php echo htmlspecialchars($product['product_name']); ?>
                </h3>
                <p class="product-brand">
    <?php echo htmlspecialchars($product['brand']); ?>
</p>

<p class="product-color">
    Color: <?php echo htmlspecialchars($product['color']); ?>
</p>

                <div class="price">
                    <?php echo number_format((float)$product['price_sar'], 2); ?>
                    <small>SAR</small>
                </div>

                <div class="product-bottom">

                    <?php if ((int)$product['stock'] > 0): ?>

                        <button class="add-btn">
                            Add to Cart
                        </button>

                    <?php else: ?>

                        <button class="notify-btn">
                            Notify me
                        </button>

                    <?php endif; ?>

                    <form method="POST" style="display: inline;">
    <button
        type="submit"
        name="remove_product"
        value="<?php echo (int)$product['product_id']; ?>"
        class="remove-btn">
        Remove
    </button>
</form>

                </div>

                <?php if ((int)$product['stock'] == 0): ?>

                    <span class="status out-stock">
                        <span class="dot"></span>
                        Out of stock
                    </span>

                <?php elseif ((int)$product['stock'] <= 5): ?>

                    <span class="status low-stock">
                        <span class="dot"></span>
                        Low stock
                    </span>

                <?php endif; ?>

            </article>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No saved products yet.</p>

    <?php endif; ?>

</div>

      
          </article>

          <article class="product-card">

            <div class="product-image glasses">

              <i class="product-art" data-lucide="glasses"></i>

              <button class="heart" aria-label="Remove from favorites">

                <i data-lucide="heart"></i>

              </button>

            </div>

            <h3 class="product-name">Bamboo Sunglasses</h3>

            <div class="price">219 <small>SAR</small></div>

            <div class="product-bottom">

              <button class="add-btn">Add to Cart</button>

              <button class="remove-btn">Remove</button>

            </div>

          </article>

          <article class="product-card">

            <div class="product-image wallet">

              <i class="product-art" data-lucide="credit-card"></i>

              <button class="heart" aria-label="Remove from favorites">

                <i data-lucide="heart"></i>

              </button>

            </div>

            <h3 class="product-name">Leather Card Wallet</h3>

            <div class="price">149 <small>SAR</small></div>

            <div class="product-bottom">

              <button class="add-btn">Add to Cart</button>

              <button class="remove-btn">Remove</button>

            </div>

          </article>

          <article class="product-card">

            <div class="product-image shoes">

              <i class="product-art" data-lucide="footprints"></i>

              <button class="heart" aria-label="Remove from favorites">

                <i data-lucide="heart"></i>

              </button>

            </div>

            <h3 class="product-name">Everyday Runner</h3>

            <div class="price">399 <small>SAR</small></div>

            <div class="product-bottom">

              <button class="add-btn">Add to Cart</button>

              <button class="remove-btn">Remove</button>

            </div>

          </article>

          <article class="product-card">

            <div class="product-image lamp">

              <i class="product-art" data-lucide="lamp-desk"></i>

              <button class="heart" aria-label="Remove from favorites">

                <i data-lucide="heart"></i>

              </button>

            </div>

            <h3 class="product-name">Linen Desk Lamp</h3>

            <div class="price">239 <small>SAR</small></div>

            <span class="status low-stock">

              <span class="dot"></span>Low stock

            </span>

            <div class="product-bottom">

              <button class="add-btn">Add to Cart</button>

              <button class="remove-btn">Remove</button>

            </div>

          </article>

          <article class="product-card">

            <div class="product-image beanie">

              <i class="product-art" data-lucide="bell"></i>

              <button class="heart" aria-label="Remove from favorites">

                <i data-lucide="heart"></i>

              </button>

            </div>

            <h3 class="product-name">Merino Beanie</h3>

            <div class="price">69 <small>SAR</small></div>

            <span class="status out-stock">

              <span class="dot"></span>Out of stock

            </span>

            <div class="product-bottom">

              <button class="notify-btn">Notify me</button>

              <button class="remove-btn">Remove</button>

            </div>

          </article>

        </div>

      </section>

    </div>

  </main>

  <footer>

    <div class="footer-grid">

      <div>

        <div class="brand footer-brand">

          <span class="brand-icon"><i data-lucide="leaf" size="16"></i></span>

          EcoCart <span class="ai">AI</span>

        </div>

        <p class="footer-description">

          A store that explains every recommendation, keeps an eye on your monthly

          budget and shows where sustainability data comes from.

        </p>

      </div>

      <div class="footer-column">

        <h3>Help</h3>

        <a href="#help">Help center</a>

        <a href="#returns">Returns policy</a>

        <a href="#shipping">Shipping</a>

        <a href="#tracking">Track my order</a>

      </div>

      <div class="footer-column">

        <h3>Quick links</h3>

        <a href="#categories">Categories</a>

        <a href="#recommendations">Recommendations</a>

        <a href="#offers">Offers</a>

        <a href="#account">My account</a>

      </div>

      <div class="footer-column newsletter">

        <h3>Newsletter</h3>

        <p>New arrivals and offers that fit your preferences.</p>

        <form id="newsletter-form">

          <input type="email" placeholder="your@email.com" required>

          <button class="subscribe" type="submit">Subscribe</button>

        </form>

      </div>

    </div>

    <div class="footer-bottom">

      © 2026 EcoCart AI · Riyadh, Saudi Arabia · Privacy · Terms

    </div>

  </footer>

</div>

<script>

lucide.createIcons();

let cartCount = 3;

function updateSavedCount() {

  document.getElementById('saved-count').textContent =

    document.querySelectorAll('.product-card').length;

}


// Add products to cart

document.querySelectorAll('.add-btn').forEach(button => {

  button.addEventListener('click', () => {

    cartCount++;

    document.getElementById('cart-count').textContent = cartCount;

    button.textContent = 'Added ✓';

    button.disabled = true;

    button.style.opacity = '0.7';

  });

});

// Wishlist heart interaction

document.querySelectorAll('.heart').forEach(button => {

  button.addEventListener('click', () => {

    const heart = button.querySelector('svg');

    button.classList.toggle('unliked');

    if (button.classList.contains('unliked')) {

      button.style.color = '#999';

      heart.style.fill = 'none';

    } else {

      button.style.color = '#b33131';

      heart.style.fill = '#b33131';

    }

  });

});

// Notify when an item is back in stock

document.querySelectorAll('.notify-btn').forEach(button => {

  button.addEventListener('click', () => {

    button.textContent = 'You will be notified!';

    button.style.background = '#e2eee6';

  });

});

// Newsletter

document.getElementById('newsletter-form').addEventListener('submit', event => {

  event.preventDefault();

  const button = event.currentTarget.querySelector('.subscribe');

  button.textContent = 'Subscribed ✓';

});

</script>

</body>

</html>
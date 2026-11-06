<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please login to checkout");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php?msg=Your cart is empty");
    exit;
}

try {
    $pdo->beginTransaction();
    $total = 0;
    $cart_items = [];

    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $prod) {
        $qty = $_SESSION['cart'][$prod['id']];
        $subtotal = $prod['price'] * $qty;
        $total += $subtotal;

        $cart_items[] = [
            'id' => $prod['id'],
            'name' => $prod['name'],
            'price' => $prod['price'],
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }

    // Insert Order Record
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $order_id = $pdo->lastInsertId();

    // Insert Order Items & Deduct Stock
    $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($cart_items as $ci) {
        $item_stmt->execute([
            $order_id,
            $ci['id'],
            $ci['qty'],
            $ci['price']
        ]);

        $stock_stmt->execute([
            $ci['qty'],
            $ci['id']
        ]);
    }

    $pdo->commit();

    // Clear cart after successful order
    unset($_SESSION['cart']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: cart.php?msg=" . urlencode("Checkout failed: " . $e->getMessage()));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Confirmation - SalesPortal</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        header {
            background: #ffffff;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        header h2 {
            color: #2563eb;
            font-size: 24px;
            font-weight: 800;
        }

        header a {
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .container {
            max-width: 750px;
            margin: 50px auto;
            padding: 20px;
        }

        .success-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .success-icon {
            width: 75px;
            height: 75px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 38px;
            font-weight: bold;
        }

        .success-card h1 {
            color: #15803d;
            font-size: 27px;
            margin-bottom: 12px;
        }

        .success-card p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .order-id {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            font-weight: 700;
        }

        .info-message {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 15px;
            border-radius: 9px;
            margin-top: 20px;
            color: #1e40af;
            font-size: 13px;
            line-height: 1.6;
        }

        .items-card {
            background: #ffffff;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
        }

        .items-card h2 {
            font-size: 20px;
            margin-bottom: 22px;
            color: #1e293b;
        }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 17px 0;
            border-bottom: 1px solid #e2e8f0;
            gap: 15px;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-name {
            font-size: 14px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 7px;
        }

        .item-details {
            font-size: 12px;
            color: #64748b;
        }

        .item-price {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
            margin-top: 10px;
            border-top: 2px solid #e2e8f0;
            font-size: 17px;
            font-weight: 800;
        }

        .total-price {
            color: #2563eb;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 14px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #94a3b8;
            font-size: 12px;
        }

        @media(max-width: 550px) {
            .container {
                margin: 20px auto;
            }

            .success-card {
                padding: 25px 18px;
            }

            .success-card h1 {
                font-size: 22px;
            }

            .items-card {
                padding: 20px;
            }

            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>SalesPortal</h2>
    <a href="index.php">Continue Shopping</a>
</header>

<div class="container">

    <!-- Success Message -->
    <div class="success-card">

        <div class="success-icon">✓</div>

        <h1>Order Placed Successfully!</h1>

        <p>
            Thank you for shopping with SalesPortal.
            Your order has been successfully placed.
        </p>

        <div class="order-id">
            Order ID: #<?= htmlspecialchars((string)$order_id) ?>
        </div>

        <div class="info-message">
            Your order is confirmed! You can check your order
            details and track its current status anytime from
            the <strong>My Orders</strong> page.
        </div>

    </div>

    <!-- Ordered Products -->
    <div class="items-card">

        <h2>📦 Your Ordered Items</h2>

        <?php foreach ($cart_items as $item): ?>

            <div class="item">

                <div>
                    <div class="item-name">
                        <?= htmlspecialchars($item['name']) ?>
                    </div>

                    <div class="item-details">
                        Quantity: <?= htmlspecialchars((string)$item['qty']) ?>
                        ×
                        <?= number_format((float)$item['price'], 2) ?>
                    </div>
                </div>

                <div class="item-price">
                    <?= number_format((float)$item['subtotal'], 2) ?>
                </div>

            </div>

        <?php endforeach; ?>

        <!-- Total -->
        <div class="total-row">
            <span>Total Amount</span>

            <span class="total-price">
                <?= number_format((float)$total, 2) ?>
            </span>
        </div>

        <!-- Buttons -->
        <div class="buttons">

            <a href="my_orders.php" class="btn btn-primary">
                View My Orders →
            </a>

            <a href="customer_dashboard.php" class="btn btn-secondary">
                Continue Shopping
            </a>

        </div>

    </div>

</div>

<footer>
    © 2026 SalesPortal. All rights reserved.
</footer>

</body>
</html>
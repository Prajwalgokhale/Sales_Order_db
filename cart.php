<?php
require_once 'db.php';

// Initialize session safely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = $_GET['msg'] ?? '';

// Handle Item Removal Action
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    unset($_SESSION['cart'][$pid]);
    header("Location: cart.php?msg=" . urlencode("Item removed from cart."));
    exit;
}

// Handle Quantity Updates (Optional POST action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $pid => $quantity) {
            $pid = intval($pid);
            $quantity = intval($quantity);
            if ($quantity > 0) {
                $_SESSION['cart'][$pid] = $quantity;
            } else {
                unset($_SESSION['cart'][$pid]);
            }
        }
    }
    header("Location: cart.php?msg=" . urlencode("Cart updated successfully."));
    exit;
}

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - SalesOut</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="bg-slate-900 text-white sticky top-0 z-50 shadow-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 font-bold text-xl tracking-tight text-white hover:opacity-90 transition">
                <span class="bg-indigo-600 text-white p-2 rounded-lg flex items-center justify-center w-9 h-9">
                    <i class="fa-solid fa-bolt text-sm"></i>
                </span>
                <span>Sales<span class="text-indigo-400">Out</span></span>
            </a>

            <nav class="flex items-center gap-4 text-sm font-medium">
                <a href="customer_dashboard.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-store"></i> Products
                </a>
                <a href="cart.php" class="text-white bg-slate-800 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-cart-shopping text-indigo-400"></i> Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="bg-indigo-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] px-1 flex items-center justify-center ml-1">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="flex items-center gap-2 border-l border-slate-800 pl-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-xs text-slate-300 font-semibold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                        <a href="logout.php" title="Logout" class="text-slate-400 hover:text-red-400 ml-1 transition">
                            <i class="fa-solid fa-right-from-bracket text-base"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-1.5 rounded-lg font-semibold shadow-sm transition">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full">
        
        <!-- Flash Alert Message -->
        <?php if (!empty($msg)): ?>
            <div class="mb-8 bg-indigo-50 border border-indigo-200 text-indigo-900 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-info text-indigo-500 text-lg"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($msg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-indigo-500 hover:text-indigo-700 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Your Shopping Cart</h2>
                    <p class="text-slate-500 text-xs">Review your items, modify quantities, and proceed to checkout.</p>
                </div>
            </div>
            <a href="index.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Continue Shopping
            </a>
        </div>

        <!-- Cart Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="POST">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-3 px-4 font-semibold">Product</th>
                                    <th class="py-3 px-4 font-semibold">Unit Price</th>
                                    <th class="py-3 px-4 font-semibold">Quantity</th>
                                    <th class="py-3 px-4 font-semibold">Subtotal</th>
                                    <th class="py-3 px-4 font-semibold text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <?php
                                $ids = implode(',', array_keys($_SESSION['cart']));
                                $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
                                $cart_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $grand_total = 0;

                                foreach ($cart_products as $p):
                                    $pid = $p['id'];
                                    $qty = intval($_SESSION['cart'][$pid]);
                                    $subtotal = floatval($p['price']) * $qty;
                                    $grand_total += $subtotal;
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-4 px-4 font-bold text-slate-900">
                                            <?= htmlspecialchars($p['name']) ?>
                                            <div class="text-xs text-slate-400 font-normal">Stock available: <?= intval($p['stock']) ?></div>
                                        </td>
                                        <td class="py-4 px-4 font-semibold text-slate-700">$<?= number_format($p['price'], 2) ?></td>
                                        <td class="py-4 px-4">
                                            <input type="number" name="qty[<?= $pid ?>]" value="<?= $qty ?>" min="1" max="<?= intval($p['stock']) ?>" class="w-20 bg-slate-50 border border-slate-300 rounded-lg px-2 py-1.5 text-center text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </td>
                                        <td class="py-4 px-4 font-extrabold text-slate-900">$<?= number_format($subtotal, 2) ?></td>
                                        <td class="py-4 px-4 text-right">
                                            <a href="cart.php?action=remove&id=<?= $pid ?>" onclick="return confirm('Remove this item from your cart?');" class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-xs py-1.5 px-3 rounded-lg border border-red-200 transition">
                                                <i class="fa-solid fa-trash-can"></i> Remove
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cart Summary & Actions Bar -->
                    <div class="mt-6 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <button type="submit" name="update_cart" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-sm transition flex items-center gap-2">
                            <i class="fa-solid fa-rotate"></i> Update Quantities
                        </button>

                        <div class="flex items-center gap-6 w-full sm:w-auto justify-between sm:justify-end">
                            <div class="text-right">
                                <span class="text-xs text-slate-400 uppercase tracking-wider block">Grand Total</span>
                                <span class="text-2xl font-extrabold text-emerald-600">$<?= number_format($grand_total, 2) ?></span>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="checkout.php" class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold py-2.5 px-6 rounded-xl shadow-lg shadow-emerald-600/30 transition text-sm flex items-center gap-2">
                                    <span>Complete Purchase</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </a>
                            <?php else: ?>
                                <a href="login.php?msg=Please login first to complete your purchase." class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-600/30 transition text-sm flex items-center gap-2">
                                    <span>Login to Checkout</span>
                                    <i class="fa-solid fa-lock text-xs"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <!-- Empty Cart State -->
                <div class="py-16 text-center">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl border border-slate-200">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <h3 class="text-slate-800 font-bold text-lg mb-1">Your cart is empty</h3>
                    <p class="text-slate-500 text-xs max-w-sm mx-auto mb-6">Looks like you haven't added any products to your cart yet. Explore our catalog to find great tech gear.</p>
                    <a href="customer_dashboard.php" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2.5 px-5 rounded-xl shadow-sm transition">
                        <span>Browse Store Catalog</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modern Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800 py-6 mt-16 text-center">
        <p>&copy; <?= date('Y') ?> SalesOut Inc. All rights reserved.</p>
    </footer>

</body>
</html>
<?php
require_once 'db.php';

// Initialize session safely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only logged-in users access this dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=" . urlencode("Please login first to view your dashboard."));
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Customer';
$msg = $_GET['msg'] ?? '';

// Handle Product Search
$search_query = trim($_GET['search'] ?? '');
if ($search_query !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search_query%", "%$search_query%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Add to Cart action directly from dashboard search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart_btn'])) {
    $product_id = intval($_POST['product_id']);
    $qty = max(1, intval($_POST['quantity']));

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }

    header("Location: customer_dashboard.php?msg=" . urlencode("Product added to cart successfully!"));
    exit;
}

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - SalesOut</title>
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
                <a href="customer_dashboard.php" class="text-white bg-slate-800 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-gauge-high text-indigo-400"></i> Dashboard
                </a>
              
                <a href="cart.php" class="relative text-slate-300 hover:text-white flex items-center gap-1.5 transition py-1 px-3 rounded-lg hover:bg-slate-800">
                    <i class="fa-solid fa-cart-shopping"></i> Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="bg-indigo-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] px-1 flex items-center justify-center">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="my_orders.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt"></i> My Orders
                </a>

                <div class="flex items-center gap-2 border-l border-slate-800 pl-4">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs uppercase">
                        <?= strtoupper(substr($user_name, 0, 1)) ?>
                    </div>
                    <span class="text-xs text-slate-300 font-semibold hidden sm:inline"><?= htmlspecialchars($user_name) ?></span>
                    <a href="logout.php" title="Logout" class="text-slate-400 hover:text-red-400 ml-1 transition">
                        <i class="fa-solid fa-right-from-bracket text-base"></i>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full space-y-10">
        
        <!-- Flash Alert Message -->
        <?php if (!empty($msg)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($msg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-950 text-white p-8 rounded-2xl shadow-xl flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden border border-slate-800">
            <div class="space-y-2 text-center md:text-left">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    <i class="fa-solid fa-user-check text-indigo-400"></i> Personal Portal
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome back, <?= htmlspecialchars($user_name) ?>!</h1>
                <p class="text-slate-300 text-sm max-w-xl">Manage your active cart, search advanced tech inventory, and keep track of your recent orders effortlessly.</p>
            </div>
            <div>
                <a href="cart.php" class="bg-white hover:bg-slate-100 text-indigo-600 font-semibold px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-cart-shopping"></i> View Cart (<?= $cart_count ?>)
                </a>
            </div>
        </div>

        <!-- Product Search & Catalog Section -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">Search & Explore Products</h3>
                    <p class="text-slate-500 text-xs">Find gear instantly by typing keywords, brand names, or descriptions.</p>
                </div>
            </div>

            <!-- Search Bar Form -->
            <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-8">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" name="search" placeholder="Search products by name or description..." value="<?= htmlspecialchars($search_query) ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/25 transition">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-sm transition flex items-center justify-center gap-2 flex-1 sm:flex-none">
                        <span>Search</span>
                    </button>
                    <?php if ($search_query !== ''): ?>
                        <a href="customer_dashboard.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm px-4 py-2.5 rounded-xl transition flex items-center justify-center">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): 
                        $stock = intval($product['stock']);
                        $in_stock = $stock > 0;
                    ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                                    <!-- FIXED: Changed strpad() to str_pad() -->
                                    <span>SKU-<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] <?= $in_stock ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $in_stock ? "$stock in stock" : "Out of stock" ?>
                                    </span>
                                </div>
                                <h4 class="font-bold text-slate-900 text-base mb-1.5 group-hover:text-indigo-600 transition line-clamp-1"><?= htmlspecialchars($product['name']) ?></h4>
                                <p class="text-slate-500 text-xs line-clamp-2 mb-4 leading-relaxed"><?= htmlspecialchars($product['description']) ?></p>
                            </div>

                            <div class="border-t border-slate-100 pt-3 mt-2">
                                <div class="flex items-baseline justify-between mb-3">
                                    <span class="text-lg font-extrabold text-slate-900">₹<?= number_format($product['price'], 2) ?></span>
                                </div>

                                <?php if ($in_stock): ?>
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="<?= $stock ?>" class="w-16 bg-slate-50 border border-slate-300 rounded-lg py-2 text-center text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <button type="submit" name="add_to_cart_btn" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-3 rounded-lg shadow-sm transition flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-cart-plus text-[10px]"></i> Add to Cart
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button disabled class="w-full bg-slate-100 text-slate-400 text-xs font-semibold py-2 rounded-lg cursor-not-allowed">
                                        Unavailable
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-12 text-center text-slate-400">
                        <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 block"></i>
                        No products found matching your search.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Cart Summary Section -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">Your Active Cart Summary</h3>
                    <p class="text-slate-500 text-xs">Quick breakdown of items currently saved in your active shopping cart.</p>
                </div>
                <a href="cart.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    Manage Full Cart &rarr;
                </a>
            </div>

            <?php if (!empty($_SESSION['cart'])): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3 px-4 font-semibold">Product Name</th>
                                <th class="py-3 px-4 font-semibold">Unit Price</th>
                                <th class="py-3 px-4 font-semibold">Quantity</th>
                                <th class="py-3 px-4 font-semibold">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php
                            $ids = implode(',', array_keys($_SESSION['cart']));
                            $cart_stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
                            $cart_products = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
                            $dashboard_total = 0;

                            foreach ($cart_products as $cp):
                                $qty = intval($_SESSION['cart'][$cp['id']]);
                                $sub = floatval($cp['price']) * $qty;
                                $dashboard_total += $sub;
                            ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 font-semibold text-slate-900"><?= htmlspecialchars($cp['name']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-600">₹<?= number_format($cp['price'], 2) ?></td>
                                    <td class="py-3.5 px-4 font-medium text-slate-800"><?= $qty ?></td>
                                    <td class="py-3.5 px-4 font-extrabold text-slate-900">₹<?= number_format($sub, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-lg font-bold text-slate-900">
                        Total Cart Value: <span class="text-emerald-600 font-extrabold ml-1">₹<?= number_format($dashboard_total, 2) ?></span>
                    </div>
                    <a href="checkout.php" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm py-2.5 px-6 rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                        <span>Proceed to Checkout</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="py-10 text-center text-slate-400">
                    <i class="fa-solid fa-cart-shopping text-3xl mb-2 text-slate-300 block"></i>
                    Your cart is currently empty. Add products from the catalog above!
                </div>
            <?php endif; ?>
        </div>

        <!-- Personal Past Orders Section -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">Your Past Orders & Status</h3>
                    <p class="text-slate-500 text-xs">Review your most recent transaction milestones and fulfillment updates.</p>
                </div>
                <a href="myorders.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    View All Orders &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4 font-semibold">Order ID</th>
                            <th class="py-3 px-4 font-semibold">Total Price</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            <th class="py-3 px-4 font-semibold">Date Placed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php
                        $ord_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5");
                        $ord_stmt->execute([$user_id]);
                        $recent_orders = $ord_stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($recent_orders) > 0):
                            foreach ($recent_orders as $ro):
                                $status = strtolower($ro['status'] ?? 'pending');
                                $status_bg = ($status === 'completed') ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200';
                        ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-semibold text-indigo-600">#<?= $ro['id'] ?></td>
                                <td class="py-3.5 px-4 font-extrabold text-slate-900">₹<?= number_format($ro['total_price'], 2) ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $status_bg ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= ($status === 'completed') ? 'bg-emerald-600' : 'bg-amber-600' ?>"></span>
                                        <?= ucfirst($ro['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 text-xs"><?= htmlspecialchars($ro['created_at']) ?></td>
                            </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="4" class="py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 block"></i>
                                    No past orders found in your history.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modern Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800 py-6 mt-16 text-center">
        <p>&copy; <?= date('Y') ?> SalesOut Inc. All rights reserved.</p>
    </footer>

</body>
</html>
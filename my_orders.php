<?php
require_once 'db.php';

// Initialize session safely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=" . urlencode("Please login first to view your orders."));
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Customer';
$msg = $_GET['msg'] ?? '';

// Fetch orders exclusively for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_orders = count($orders);

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - SalesOut</title>
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
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
                <a href="index.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-store"></i> Catalog
                </a>
                <a href="cart.php" class="relative text-slate-300 hover:text-white flex items-center gap-1.5 transition py-1 px-3 rounded-lg hover:bg-slate-800">
                    <i class="fa-solid fa-cart-shopping"></i> Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="bg-indigo-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] px-1 flex items-center justify-center">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="myorders.php" class="text-white bg-slate-800 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt text-indigo-400"></i> My Orders
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
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full space-y-8">
        
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

        <!-- Page Header -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">My Order History & Status</h2>
                    <p class="text-slate-500 text-xs">Inspect your past purchases, item breakdowns, and fulfillment status updates.</p>
                </div>
            </div>
            <div class="text-xs bg-slate-100 text-slate-700 px-4 py-2 rounded-xl font-medium border border-slate-200">
                Total Orders: <span class="font-bold text-slate-900"><?= $total_orders ?></span>
            </div>
        </div>

        <!-- Orders List -->
        <?php if ($total_orders > 0): ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): 
                    $status = strtolower($order['status'] ?? 'pending');
                    if ($status === 'completed') {
                        $status_badge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                        $dot_color = 'bg-emerald-600';
                    } elseif ($status === 'cancelled') {
                        $status_badge = 'bg-red-100 text-red-800 border-red-200';
                        $dot_color = 'bg-red-600';
                    } else {
                        $status_badge = 'bg-amber-100 text-amber-800 border-amber-200';
                        $dot_color = 'bg-amber-600';
                    }

                    // Fetch items for this specific order
                    $item_stmt = $pdo->prepare("
                        SELECT oi.*, p.name as product_name 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?
                    ");
                    $item_stmt->execute([$order['id']]);
                    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6 border-l-4 border-l-indigo-600">
                        <!-- Order Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-4">
                            <div>
                                <span class="text-lg font-extrabold text-slate-900">Order #<?= $order['id'] ?></span>
                                <span class="text-xs text-slate-400 ml-3"><i class="fa-regular fa-clock mr-1"></i> Placed on: <?= htmlspecialchars($order['created_at']) ?></span>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $status_badge ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $dot_color ?>"></span>
                                    Status: <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Order Items Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">
                                        <th class="py-2.5 px-4 font-semibold">Product Name</th>
                                        <th class="py-2.5 px-4 font-semibold">Price</th>
                                        <th class="py-2.5 px-4 font-semibold">Quantity</th>
                                        <th class="py-2.5 px-4 font-semibold">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <?php foreach ($items as $item): ?>
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="py-3 px-4 font-medium text-slate-900"><?= htmlspecialchars($item['product_name']) ?></td>
                                            <td class="py-3 px-4 text-slate-600">₹<?= number_format($item['price'], 2) ?></td>
                                            <td class="py-3 px-4 text-slate-800 font-semibold"><?= intval($item['quantity']) ?></td>
                                            <td class="py-3 px-4 font-extrabold text-slate-900">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Order Footer Summary -->
                        <div class="flex flex-col sm:flex-row items-center justify-between mt-5 pt-4 border-t border-dashed border-slate-200 gap-3">
                            <span class="text-xs text-slate-500 font-medium">
                                Total Unique Items in Order: <span class="font-bold text-slate-800"><?= count($items) ?></span>
                            </span>
                            <div class="text-right">
                                <span class="text-xs text-slate-400 uppercase tracking-wider mr-2">Total Amount:</span>
                                <span class="text-xl font-extrabold text-emerald-600">₹<?= number_format($order['total_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-16 text-center">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl border border-slate-200">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h3 class="text-slate-800 font-bold text-lg mb-1">No orders placed yet</h3>
                <p class="text-slate-500 text-xs max-w-sm mx-auto mb-6">You haven't completed any purchases yet. Explore our store catalog to place your first order.</p>
                <a href="index.php" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2.5 px-5 rounded-xl shadow-sm transition">
                    <span>Start Shopping</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>

    </main>

    <!-- Modern Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800 py-6 mt-16 text-center">
        <p>&copy; <?= date('Y') ?> SalesOut Inc. All rights reserved.</p>
    </footer>

</body>
</html>
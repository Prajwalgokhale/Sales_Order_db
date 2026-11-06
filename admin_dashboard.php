<?php
require_once 'db.php';

// Initialize session safely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Strict Admin Access Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?msg=" . urlencode("Access denied. Admin privileges required."));
    exit;
}

$msg = $_GET['msg'] ?? '';

// Handle Add Product Submission (Admin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product_btn'])) {
    $pname = trim($_POST['pname'] ?? '');
    $pdesc = trim($_POST['pdesc'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? 'Electronics');

    if (!empty($pname) && $price >= 0 && $stock >= 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$pname, $pdesc, $price, $stock, $category]);
        header("Location: admin_dashboard.php?msg=" . urlencode("Product added successfully!"));
        exit;
    } else {
        header("Location: admin_dashboard.php?msg=" . urlencode("Please fill in all required product fields correctly."));
        exit;
    }
}

// Fetch Dashboard Metrics & Statistics
$product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$order_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn() ?? 0;

// Fetch Recent Customer Orders
$orders_stmt = $pdo->query("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 10");
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SalesOut</title>
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

    <!-- Admin Header Navigation -->
    <header class="bg-slate-900 text-white sticky top-0 z-50 shadow-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="admin_dashboard.php" class="flex items-center gap-2 font-bold text-xl tracking-tight text-white hover:opacity-90 transition">
                <span class="bg-amber-500 text-slate-950 p-2 rounded-lg flex items-center justify-center w-9 h-9 font-extrabold">
                    <i class="fa-solid fa-gauge-high text-sm"></i>
                </span>
                <span>Admin<span class="text-amber-400">Portal</span></span>
            </a>

            <nav class="flex items-center gap-4 text-sm font-medium">
               
                <a href="products.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-box"></i> Products
                </a>
                <a href="orders.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-clipboard-list"></i> Orders
                </a>
                <a href="users.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-users"></i> Users
                </a>
                
                <div class="flex items-center gap-2 border-l border-slate-800 pl-4">
                    <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold px-2.5 py-1 rounded-full uppercase">
                        Admin: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Superuser') ?>
                    </span>
                    <a href="logout.php" title="Logout" class="text-slate-400 hover:text-red-400 ml-2 transition">
                        <i class="fa-solid fa-right-from-bracket text-base"></i>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Dashboard Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full">
        
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

        <!-- Statistics Metrics Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Total Products Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Products</p>
                    <h3 class="text-3xl font-extrabold text-slate-900"><?= number_format($product_count) ?></h3>
                    <a href="products.php" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mt-2 inline-flex items-center gap-1">
                        Manage catalog <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Users</p>
                    <h3 class="text-3xl font-extrabold text-slate-900"><?= number_format($user_count) ?></h3>
                    <a href="users.php" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mt-2 inline-flex items-center gap-1">
                        View accounts <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-100">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Orders</p>
                    <h3 class="text-3xl font-extrabold text-slate-900"><?= number_format($order_count) ?></h3>
                    <a href="orders.php" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mt-2 inline-flex items-center gap-1">
                        Review orders <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl border border-amber-100">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Revenue</p>
                    <h3 class="text-3xl font-extrabold text-slate-900">₹<?= number_format($total_revenue, 2) ?></h3>
                    <span class="text-xs text-emerald-600 font-medium mt-2 inline-flex items-center gap-1">
                        <i class="fa-solid fa-arrow-trend-up"></i> Verified Sales
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl border border-cyan-100">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <!-- Add New Product Card (Admin Exclusive) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8 mb-10">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">Add New Product to Catalog</h3>
                    <p class="text-slate-500 text-xs">Fill in the specification details below to publish items directly to the storefront.</p>
                </div>
            </div>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Product Name</label>
                        <input type="text" name="pname" required placeholder="e.g. Wireless Ergonomic Mouse" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="price" required placeholder="49.99" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Stock Quantity</label>
                        <input type="number" min="0" name="stock" required placeholder="100" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Description</label>
                        <textarea name="pdesc" rows="3" placeholder="Provide technical specifications, dimensions, and product highlights..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Category</label>
                        <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                            <option value="Electronics">Electronics</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Gadgets">Gadgets</option>
                            <option value="Hardware">Hardware</option>
                        </select>
                        <div class="mt-4">
                            <button type="submit" name="add_product_btn" class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                                <span>Publish Product</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Recent Customer Orders Overview Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">Recent Customer Orders</h3>
                    <p class="text-slate-500 text-xs">Overview of the latest transactions placed across the storefront</p>
                </div>
                <a href="orders.php" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    View all orders &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-6 font-semibold">Order ID</th>
                            <th class="py-3 px-6 font-semibold">Customer Name</th>
                            <th class="py-3 px-6 font-semibold">Total Price</th>
                            <th class="py-3 px-6 font-semibold">Status</th>
                            <th class="py-3 px-6 font-semibold">Date Placed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $ord): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6 font-semibold text-indigo-600">#<?= $ord['id'] ?></td>
                                    <td class="py-4 px-6 font-medium text-slate-800">
                                        <?= htmlspecialchars($ord['customer_name']) ?>
                                        <div class="text-xs text-slate-400"><?= htmlspecialchars($ord['customer_email'] ?? '') ?></div>
                                    </td>
                                    <td class="py-4 px-6 font-bold text-slate-900">₹<?= number_format($ord['total_price'], 2) ?></td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            <?= htmlspecialchars($ord['status'] ?? 'Completed') ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 text-xs"><?= htmlspecialchars($ord['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 block"></i>
                                    No customer orders placed yet.
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
        <p>&copy; <?= date('Y') ?> SalesOut Admin Suite. All rights reserved.</p>
    </footer>

</body>
</html>
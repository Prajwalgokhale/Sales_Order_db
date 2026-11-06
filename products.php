<?php
require_once 'db.php';

// Initialize session safely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
$msg = $_GET['msg'] ?? '';

// Handle Product Deletion (Admin Only)
if ($is_admin && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    header("Location: products.php?msg=" . urlencode("Product deleted successfully."));
    exit;
}

// Handle Add Product Submission (Admin Only)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product_btn'])) {
    $pname = trim($_POST['pname'] ?? '');
    $pdesc = trim($_POST['pdesc'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? 'Electronics');

    if (!empty($pname) && $price >= 0 && $stock >= 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$pname, $pdesc, $price, $stock, $category]);
        header("Location: products.php?msg=" . urlencode("Product added successfully."));
        exit;
    } else {
        header("Location: products.php?msg=" . urlencode("Please fill in all required product fields correctly."));
        exit;
    }
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_products = count($products);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory - SalesOut</title>
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
                <a href="index.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-store"></i> Storefront
                </a>
                <a href="products.php" class="text-white bg-slate-800 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-box text-indigo-400"></i> Products
                </a>
                <a href="cart.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-cart-shopping"></i> Cart
                </a>
                
                <?php if ($is_admin): ?>
                    <a href="admin_dashboard.php" class="text-amber-400 hover:text-amber-300 transition flex items-center gap-1.5 font-semibold">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </a>
                    <a href="orders.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                        <i class="fa-solid fa-receipt"></i> Orders
                    </a>
                    <a href="users.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                        <i class="fa-solid fa-users"></i> Users
                    </a>
                <?php endif; ?>

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

        <!-- Page Top Header -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Product Inventory Management</h2>
                    <p class="text-slate-500 text-xs">Browse all catalog entries, stock levels, pricing, and system specifications.</p>
                </div>
            </div>
            <div class="text-xs bg-slate-100 text-slate-700 px-4 py-2 rounded-xl font-medium border border-slate-200">
                Total Products: <span class="font-bold text-slate-900"><?= $total_products ?></span>
            </div>
        </div>

        <!-- Add New Product Form (Admin Only) -->
        <?php if ($is_admin): ?>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8 mb-10">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Add New Product to Catalog</h3>
                        <p class="text-slate-500 text-xs">Publish a new item directly to the active storefront store.</p>
                    </div>
                </div>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Product Name</label>
                            <input type="text" name="pname" required placeholder="e.g. Mechanical Gaming Keyboard" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Price (₹)</label>
                            <input type="number" step="0.01" min="0" name="price" required placeholder="89.99" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Stock Quantity</label>
                            <input type="number" min="0" name="stock" required placeholder="50" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Description</label>
                            <textarea name="pdesc" rows="3" placeholder="Enter product specifications, features, and model info..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Category</label>
                            <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition mb-4">
                                <option value="Electronics">Electronics</option>
                                <option value="Accessories">Accessories</option>
                                <option value="Gadgets">Gadgets</option>
                                <option value="Hardware">Hardware</option>
                            </select>
                            <button type="submit" name="add_product_btn" class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                                <span>Save Product</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Products Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Active Catalog Inventory</h3>
                    <p class="text-slate-500 text-xs">All items currently listed in the store database</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-6 font-semibold">ID</th>
                            <th class="py-3 px-6 font-semibold">Product Name</th>
                            <th class="py-3 px-6 font-semibold">Description</th>
                            <th class="py-3 px-6 font-semibold">Price</th>
                            <th class="py-3 px-6 font-semibold">Stock Status</th>
                            <?php if ($is_admin): ?>
                                <th class="py-3 px-6 font-semibold text-right">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if ($total_products > 0): ?>
                            <?php foreach ($products as $prod): 
                                $stock = intval($prod['stock'] ?? 0);
                                $in_stock = $stock > 0;
                            ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6 font-semibold text-slate-500">#<?= $prod['id'] ?></td>
                                    <td class="py-4 px-6 font-bold text-slate-900">
                                        <?= htmlspecialchars($prod['name']) ?>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 text-xs max-w-xs truncate">
                                        <?= htmlspecialchars($prod['description']) ?>
                                    </td>
                                    <td class="py-4 px-6 font-extrabold text-slate-900">₹<?= number_format($prod['price'], 2) ?></td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $in_stock ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-red-100 text-red-800 border-red-200' ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $in_stock ? 'bg-emerald-600' : 'bg-red-600' ?>"></span>
                                            <?= $stock ?> units
                                        </span>
                                    </td>
                                    <?php if ($is_admin): ?>
                                        <td class="py-4 px-6 text-right">
                                            <a href="products.php?action=delete&id=<?= $prod['id'] ?>" onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>?');" class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-xs py-1.5 px-3 rounded-lg border border-red-200 transition">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $is_admin ? 6 : 5 ?>" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 block"></i>
                                    No products found in the catalog.
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
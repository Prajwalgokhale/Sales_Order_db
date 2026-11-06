<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = $_GET['msg'] ?? '';

// Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart_btn'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?msg=Please login first to add items to your cart!");
        exit;
    }

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

    header("Location: cart.php?msg=Product added to cart");
    exit;
}

// Search and Category
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? 'all';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR description LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($category !== 'all' && !empty($category)) {
    $sql .= " AND category = :category";
    $params[':category'] = $category;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cart_count = isset($_SESSION['cart'])
    ? array_sum($_SESSION['cart'])
    : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>SalesOut | Premium Tech Store</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: #f8fafc;
}

html {
    scroll-behavior: smooth;
}

.product-card {
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12);
}

.product-image {
    transition: all 0.4s ease;
}

.product-card:hover .product-image {
    transform: scale(1.12);
}

.nav-link {
    transition: 0.3s;
}

.nav-link:hover {
    color: #818cf8;
}

.primary-btn {
    transition: all 0.3s ease;
}

.primary-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.feature-box {
    transition: 0.3s ease;
}

.feature-box:hover {
    transform: translateY(-5px);
    background: rgba(51, 65, 85, 0.8);
}

.hero-bg {
    background:
    radial-gradient(circle at 80% 30%, rgba(79,70,229,0.25), transparent 30%),
    linear-gradient(135deg, #312e81, #0f172a 65%, #020617);
}

</style>

</head>

<body class="text-slate-800">

<!-- NAVBAR -->

<header class="bg-slate-900 text-white sticky top-0 z-50 shadow-xl">

<div class="max-w-7xl mx-auto px-5 h-20 flex items-center justify-between">

    <!-- LOGO -->

    <a href="index.php" class="flex items-center gap-3">

        <div class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg">

            <i class="fa-solid fa-bolt text-xl"></i>

        </div>

        <div>

            <h1 class="text-2xl font-extrabold tracking-tight">

                Sales<span class="text-indigo-400">Out</span>

            </h1>

            <p class="text-[10px] text-slate-400 tracking-widest uppercase">

                Premium Tech Store

            </p>

        </div>

    </a>


    <!-- SEARCH -->

    <form action="index.php" method="GET"
    class="hidden md:flex flex-1 max-w-md mx-10">

        <div class="relative w-full">

            <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search products..."
            class="w-full bg-slate-800 border border-slate-700 rounded-full py-3 pl-11 pr-5 text-sm outline-none focus:border-indigo-500">

            <i class="fa-solid fa-search absolute left-4 top-4 text-slate-400"></i>

        </div>

    </form>


    <!-- NAVIGATION -->

    <nav class="flex items-center gap-5">

        <a href="index.php"
        class="nav-link hidden sm:block text-sm text-slate-300">

            Home

        </a>

        <a href="#catalog"
        class="nav-link hidden sm:block text-sm text-slate-300">

            Products

        </a>

        <!-- CART -->

        <a href="cart.php"
        class="relative text-xl text-slate-300 hover:text-white">

            <i class="fa-solid fa-cart-shopping"></i>

            <span class="absolute -top-3 -right-3 bg-indigo-600 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center font-bold">

                <?= $cart_count ?>

            </span>

        </a>


        <?php if (isset($_SESSION['user_id'])): ?>

            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>

                <a href="admin_dashboard.php"
                class="text-amber-400 text-sm font-semibold hidden sm:block">

                    <i class="fa-solid fa-gauge-high"></i> Admin

                </a>

            <?php endif; ?>


            <div class="flex items-center gap-3 border-l border-slate-700 pl-4">

                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-bold">

                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>

                </div>

                <div class="hidden lg:block">

                    <p class="text-sm font-semibold">

                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>

                    </p>

                    <p class="text-xs text-indigo-400">

                        <?= htmlspecialchars($_SESSION['user_role'] ?? 'Member') ?>

                    </p>

                </div>

                <a href="logout.php"
                class="text-slate-400 hover:text-red-400 text-lg">

                    <i class="fa-solid fa-right-from-bracket"></i>

                </a>

            </div>


        <?php else: ?>

            <a href="login.php"
            class="text-sm text-slate-300 hover:text-white">

                Login

            </a>

            <a href="register.php"
            class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 rounded-lg text-sm font-semibold primary-btn">

                Register

            </a>

        <?php endif; ?>

    </nav>

</div>

</header>


<!-- HERO SECTION -->

<section class="hero-bg text-white relative overflow-hidden">

<div class="max-w-7xl mx-auto px-6 py-24">

    <div class="grid md:grid-cols-2 gap-14 items-center">

        <!-- HERO CONTENT -->

        <div>

            <span class="inline-flex items-center gap-2 bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 px-4 py-2 rounded-full text-xs font-semibold mb-7">

                <i class="fa-solid fa-fire text-amber-400"></i>

                THE FUTURE OF TECHNOLOGY

            </span>


            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">

                Upgrade Your

                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">

                    Digital Lifestyle

                </span>

            </h1>


            <p class="text-slate-300 text-base sm:text-lg leading-relaxed mb-9 max-w-lg">

                Discover premium electronics, innovative gadgets and accessories.

                Experience quality technology at the best prices.

            </p>


            <div class="flex flex-wrap gap-4">

                <a href="#catalog"
                class="bg-indigo-600 hover:bg-indigo-500 px-7 py-4 rounded-xl font-semibold primary-btn flex items-center gap-3">

                    Explore Products

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

                <a href="cart.php"
                class="border border-slate-600 hover:bg-slate-800 px-7 py-4 rounded-xl font-semibold transition">

                    View Cart

                </a>

            </div>


            <!-- STATS -->

            <div class="flex gap-10 mt-12">

                <div>

                    <h2 class="text-3xl font-bold text-white">

                        100%

                    </h2>

                    <p class="text-xs text-slate-400 mt-1">

                        Quality Products

                    </p>

                </div>

                <div>

                    <h2 class="text-3xl font-bold text-white">

                        24/7

                    </h2>

                    <p class="text-xs text-slate-400 mt-1">

                        Customer Support

                    </p>

                </div>

                <div>

                    <h2 class="text-3xl font-bold text-white">

                        Fast

                    </h2>

                    <p class="text-xs text-slate-400 mt-1">

                        Delivery

                    </p>

                </div>

            </div>

        </div>


        <!-- HERO FEATURES -->

        <div class="grid grid-cols-2 gap-5">

            <div class="feature-box bg-slate-800/60 backdrop-blur border border-slate-700 p-7 rounded-2xl">

                <i class="fa-solid fa-truck-fast text-3xl text-indigo-400 mb-5"></i>

                <h3 class="font-bold text-lg mb-2">

                    Fast Delivery

                </h3>

                <p class="text-slate-400 text-xs">

                    Quick and reliable delivery.

                </p>

            </div>


            <div class="feature-box bg-slate-800/60 backdrop-blur border border-slate-700 p-7 rounded-2xl mt-8">

                <i class="fa-solid fa-shield-halved text-3xl text-emerald-400 mb-5"></i>

                <h3 class="font-bold text-lg mb-2">

                    Secure Shopping

                </h3>

                <p class="text-slate-400 text-xs">

                    Safe and trusted experience.

                </p>

            </div>


            <div class="feature-box bg-slate-800/60 backdrop-blur border border-slate-700 p-7 rounded-2xl">

                <i class="fa-solid fa-headset text-3xl text-cyan-400 mb-5"></i>

                <h3 class="font-bold text-lg mb-2">

                    24/7 Support

                </h3>

                <p class="text-slate-400 text-xs">

                    Always here to help you.

                </p>

            </div>


            <div class="feature-box bg-slate-800/60 backdrop-blur border border-slate-700 p-7 rounded-2xl mt-8">

                <i class="fa-solid fa-rotate-left text-3xl text-amber-400 mb-5"></i>

                <h3 class="font-bold text-lg mb-2">

                    Easy Returns

                </h3>

                <p class="text-slate-400 text-xs">

                    Simple return experience.

                </p>

            </div>

        </div>

    </div>

</div>

</section>


<!-- MAIN CONTENT -->

<main class="max-w-7xl mx-auto px-5 py-16" id="catalog">


    <!-- MESSAGE -->

    <?php if ($msg): ?>

        <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex justify-between items-center">

            <span>

                <i class="fa-solid fa-circle-check mr-2"></i>

                <?= htmlspecialchars($msg) ?>

            </span>

            <button onclick="this.parentElement.remove()">

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

    <?php endif; ?>


    <!-- SECTION HEADER -->

    <div class="flex flex-col md:flex-row justify-between md:items-end gap-6 mb-10">

        <div>

            <span class="text-indigo-600 font-bold text-xs uppercase tracking-widest">

                Our Collection

            </span>

            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">

                Featured Products

            </h2>

            <p class="text-slate-500 mt-3 text-sm">

                Explore our latest arrivals and discover amazing technology.

            </p>

        </div>


        <!-- FILTER -->

        <form action="index.php#catalog" method="GET"
        class="flex flex-wrap gap-3">

            <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search products..."
            class="bg-white border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:border-indigo-500">


            <select
            name="category"
            onchange="this.form.submit()"
            class="bg-white border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:border-indigo-500">

                <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>

                    All Categories

                </option>

                <option value="Electronics" <?= $category === 'Electronics' ? 'selected' : '' ?>>

                    Electronics

                </option>

                <option value="Accessories" <?= $category === 'Accessories' ? 'selected' : '' ?>>

                    Accessories

                </option>

                <option value="Gadgets" <?= $category === 'Gadgets' ? 'selected' : '' ?>>

                    Gadgets

                </option>

            </select>


            <button type="submit"
            class="bg-slate-900 hover:bg-indigo-600 text-white px-5 py-3 rounded-xl text-sm font-semibold transition">

                <i class="fa-solid fa-filter mr-2"></i>

                Filter

            </button>

        </form>

    </div>


    <!-- PRODUCT GRID -->

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-7">


        <?php if (count($products) > 0): ?>

            <?php foreach ($products as $product): ?>

                <?php

                $in_stock = ($product['stock'] ?? 0) > 0;

                $rating = $product['rating'] ?? 4.8;

                $sku = $product['sku'] ?? ('SKU-' . str_pad($product['id'], 4, '0', STR_PAD_LEFT));

                $category_name = $product['category'] ?? 'Hardware';

                ?>


                <!-- PRODUCT CARD -->

                <div class="product-card bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col">


                    <!-- IMAGE AREA -->

                    <div class="relative bg-slate-100 h-52 flex items-center justify-center overflow-hidden">

                        <div class="product-image">

                            <i class="fa-solid fa-box-open text-7xl text-slate-300"></i>

                        </div>


                        <span class="absolute top-4 left-4 bg-white px-3 py-1.5 rounded-full text-xs font-semibold text-slate-700 shadow-sm">

                            <?= htmlspecialchars($category_name) ?>

                        </span>


                        <span class="absolute top-4 right-4 px-3 py-1.5 rounded-full text-xs font-bold

                        <?= $in_stock
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-red-100 text-red-700' ?>">

                            <?= $in_stock ? 'In Stock' : 'Out of Stock' ?>

                        </span>

                    </div>


                    <!-- PRODUCT DETAILS -->

                    <div class="p-5 flex flex-col flex-1">


                        <div class="flex justify-between items-center mb-3">

                            <span class="text-xs text-slate-400">

                                <?= htmlspecialchars($sku) ?>

                            </span>


                            <span class="text-amber-500 text-xs font-bold">

                                <i class="fa-solid fa-star"></i>

                                <?= number_format($rating, 1) ?>

                            </span>

                        </div>


                        <h3 class="text-lg font-bold text-slate-900 mb-2">

                            <?= htmlspecialchars($product['name']) ?>

                        </h3>


                        <p class="text-sm text-slate-500 leading-relaxed mb-5 line-clamp-2">

                            <?= htmlspecialchars($product['description']) ?>

                        </p>


                        <div class="mt-auto border-t border-slate-100 pt-4">


                            <!-- STOCK -->

                            <div class="flex justify-between text-xs text-slate-500 mb-4">

                                <span>Available Units</span>

                                <span class="font-bold text-slate-800">

                                    <?= intval($product['stock']) ?>

                                </span>

                            </div>


                            <!-- PRICE -->

                            <div class="flex items-center justify-between mb-5">

                                <div>

                                    <p class="text-xs text-slate-400 mb-1">

                                        Price

                                    </p>

                                    <span class="text-2xl font-extrabold text-slate-900">

                                        ₹<?= number_format($product['price'], 2) ?>

                                    </span>

                                </div>


                                <div class="text-xs text-slate-400">

                                    <i class="fa-solid fa-shield-halved text-emerald-500"></i>

                                    Quality Assured

                                </div>

                            </div>


                            <!-- ADD TO CART -->

                            <?php if ($in_stock): ?>

                                <form method="POST" class="flex gap-2">

                                    <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= $product['id'] ?>">


                                    <input
                                    type="number"
                                    name="quantity"
                                    value="1"
                                    min="1"
                                    max="<?= intval($product['stock']) ?>"
                                    class="w-16 bg-slate-50 border border-slate-300 rounded-lg text-center text-sm font-semibold outline-none focus:border-indigo-500">


                                    <button
                                    type="submit"
                                    name="add_to_cart_btn"
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-bold transition">

                                        <i class="fa-solid fa-cart-plus mr-2"></i>

                                        Add To Cart

                                    </button>

                                </form>

                            <?php else: ?>

                                <button disabled
                                class="w-full bg-slate-200 text-slate-400 py-3 rounded-xl text-sm font-semibold cursor-not-allowed">

                                    Currently Unavailable

                                </button>

                            <?php endif; ?>


                        </div>

                    </div>

                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- EMPTY STATE -->

            <div class="col-span-full bg-white border border-dashed border-slate-300 rounded-2xl py-20 text-center">

                <i class="fa-solid fa-box-open text-6xl text-slate-300 mb-6"></i>

                <h3 class="text-2xl font-bold text-slate-800">

                    No Products Found

                </h3>

                <p class="text-slate-500 mt-3 mb-6">

                    Try searching with another product name or category.

                </p>

                <a href="index.php"
                class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl text-sm font-semibold">

                    Reset Filters

                </a>

            </div>


        <?php endif; ?>


    </div>

</main>


<!-- FOOTER -->

<footer class="bg-slate-900 text-slate-400 mt-10">


    <div class="max-w-7xl mx-auto px-6 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">


        <!-- BRAND -->

        <div>

            <div class="flex items-center gap-3 mb-5">

                <div class="bg-indigo-600 w-10 h-10 rounded-xl flex items-center justify-center text-white">

                    <i class="fa-solid fa-bolt"></i>

                </div>

                <h2 class="text-2xl font-extrabold text-white">

                    Sales<span class="text-indigo-400">Out</span>

                </h2>

            </div>

            <p class="text-sm leading-relaxed">

                Your destination for premium electronics, innovative gadgets and modern technology.

            </p>

        </div>


        <!-- QUICK LINKS -->

        <div>

            <h3 class="text-white font-bold mb-5">

                Quick Links

            </h3>

            <ul class="space-y-3 text-sm">

                <li><a href="index.php" class="hover:text-indigo-400 transition">Home</a></li>

                <li><a href="#catalog" class="hover:text-indigo-400 transition">Products</a></li>

                <li><a href="cart.php" class="hover:text-indigo-400 transition">Shopping Cart</a></li>

                <li><a href="login.php" class="hover:text-indigo-400 transition">My Account</a></li>

            </ul>

        </div>


        <!-- CUSTOMER CARE -->

        <div>

            <h3 class="text-white font-bold mb-5">

                Customer Support

            </h3>

            <ul class="space-y-3 text-sm">

                <li><a href="#" class="hover:text-indigo-400 transition">Shipping Policy</a></li>

                <li><a href="#" class="hover:text-indigo-400 transition">Returns & Exchanges</a></li>

                <li><a href="#" class="hover:text-indigo-400 transition">Order Tracking</a></li>

                <li><a href="#" class="hover:text-indigo-400 transition">Help Center</a></li>

            </ul>

        </div>


        <!-- SECURITY -->

        <div>

            <h3 class="text-white font-bold mb-5">

                Secure Shopping

            </h3>

            <p class="text-sm leading-relaxed mb-5">

                Shop confidently with our secure shopping experience.

            </p>

            <div class="flex gap-4 text-2xl">

                <i class="fa-brands fa-cc-visa"></i>

                <i class="fa-brands fa-cc-mastercard"></i>

                <i class="fa-brands fa-cc-paypal"></i>

                <i class="fa-brands fa-cc-apple-pay"></i>

            </div>

        </div>


    </div>


    <!-- COPYRIGHT -->

    <div class="border-t border-slate-800 py-6 text-center text-xs">

        © <?= date('Y') ?> SalesOut Inc. All Rights Reserved.

        <p class="mt-2 text-slate-500">

            Designed with <i class="fa-solid fa-heart text-red-500"></i> for modern shopping.

        </p>

    </div>


</footer>


</body>
</html>
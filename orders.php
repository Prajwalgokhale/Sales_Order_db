
<?php
require_once 'db.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=" . urlencode("Please login to view orders."));
    exit;
}

$user_id = intval($_SESSION['user_id']);
$is_admin = (($_SESSION['user_role'] ?? '') === 'admin');
$msg = $_GET['msg'] ?? '';

// Allowed order statuses
$allowed_statuses = [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

// Initialize CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle Admin Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {

    // Only admin can update status
    if (!$is_admin) {
        http_response_code(403);
        exit("Access denied. Only administrators can update order status.");
    }

    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit("Invalid request. Please try again.");
    }

    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = strtolower(trim($_POST['status'] ?? ''));

    if ($order_id > 0 && array_key_exists($new_status, $allowed_statuses)) {

        $update = $pdo->prepare(
            "UPDATE orders SET status = ? WHERE id = ?"
        );

        $update->execute([$new_status, $order_id]);

        if ($update->rowCount() > 0) {
            $msg = "Order #" . $order_id . " status updated successfully.";
        } else {
            $check = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
            $check->execute([$order_id]);

            $msg = $check->fetch()
                ? "Order status is already set to " . ucfirst($new_status) . "."
                : "Order not found.";
        }

    } else {
        $msg = "Invalid order details or status.";
    }

    header("Location: orders.php?msg=" . urlencode($msg));
    exit;
}

// Fetch orders depending on user role
if ($is_admin) {

    $stmt = $pdo->query("
        SELECT
            o.*,
            u.name AS customer_name,
            u.email AS customer_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC
    ");

} else {

    $stmt = $pdo->prepare("
        SELECT
            o.*,
            u.name AS customer_name,
            u.email AS customer_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.user_id = ?
        ORDER BY o.id DESC
    ");

    $stmt->execute([$user_id]);
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_orders = count($orders);

// Status colors
$status_colors = [
    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
    'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
    'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
    'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'cancelled' => 'bg-red-100 text-red-800 border-red-200'
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Orders Directory - SalesOut</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        select {
            cursor: pointer;
        }
    </style>

</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Header Navigation -->

    <header class="bg-slate-900 text-white sticky top-0 z-50 shadow-md border-b border-slate-800">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

            <a href="index.php"
               class="flex items-center gap-2 font-bold text-xl tracking-tight text-white">

                <span class="bg-indigo-600 text-white p-2 rounded-lg flex items-center justify-center w-9 h-9">
                    <i class="fa-solid fa-bolt text-sm"></i>
                </span>

                <span>
                    Sales<span class="text-indigo-400">Out</span>
                </span>

            </a>

            <nav class="flex items-center gap-4 text-sm font-medium">

                <a href="index.php"
                   class="text-slate-300 hover:text-white transition flex items-center gap-1.5">

                    <i class="fa-solid fa-store"></i>
                    Storefront

                </a>

                <a href="products.php"
                   class="text-slate-300 hover:text-white transition flex items-center gap-1.5">

                    <i class="fa-solid fa-box"></i>
                    Products

                </a>

                <a href="orders.php"
                   class="text-white bg-slate-800 px-3 py-1.5 rounded-lg flex items-center gap-1.5">

                    <i class="fa-solid fa-receipt text-indigo-400"></i>
                    Orders

                </a>

                <?php if ($is_admin): ?>

                    <a href="admin_dashboard.php"
                       class="text-amber-400 hover:text-amber-300 transition flex items-center gap-1.5 font-semibold">

                        <i class="fa-solid fa-gauge-high"></i>
                        Dashboard

                    </a>

                    <a href="users.php"
                       class="text-slate-300 hover:text-white transition flex items-center gap-1.5">

                        <i class="fa-solid fa-users"></i>
                        Users

                    </a>

                <?php endif; ?>

                <div class="flex items-center gap-2 border-l border-slate-800 pl-4">

                    <span class="text-xs text-slate-300 font-semibold">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                    </span>

                    <a href="logout.php"
                       title="Logout"
                       class="text-slate-400 hover:text-red-400 ml-1 transition">

                        <i class="fa-solid fa-right-from-bracket text-base"></i>

                    </a>

                </div>

            </nav>

        </div>

    </header>


    <!-- Main Content -->

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full">


        <!-- Flash Message -->

        <?php if (!empty($msg)): ?>

            <div class="mb-8 bg-indigo-50 border border-indigo-200 text-indigo-900 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">

                <div class="flex items-center gap-3">

                    <i class="fa-solid fa-circle-info text-indigo-500 text-lg"></i>

                    <span class="text-sm font-medium">
                        <?= htmlspecialchars($msg) ?>
                    </span>

                </div>

                <button onclick="this.parentElement.remove()"
                        class="text-indigo-500 hover:text-indigo-700 text-sm">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        <?php endif; ?>


        <!-- Page Header -->

        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

            <div class="flex items-center gap-4">

                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">

                    <i class="fa-solid fa-clipboard-list"></i>

                </div>

                <div>

                    <h2 class="text-xl font-bold text-slate-900">

                        <?= $is_admin ? 'All Customer Orders (Admin View)' : 'My Order History' ?>

                    </h2>

                    <p class="text-slate-500 text-xs">

                        <?= $is_admin
                            ? 'Manage customer orders and update their delivery status.'
                            : 'Review your past purchases and shipment statuses.' ?>

                    </p>

                </div>

            </div>

            <div class="text-xs bg-slate-100 text-slate-700 px-4 py-2 rounded-xl font-medium border border-slate-200">

                Total Records:

                <span class="font-bold text-slate-900">
                    <?= $total_orders ?>
                </span>

            </div>

        </div>


        <!-- Orders Table -->

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full text-left border-collapse">

                    <thead>

                        <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">

                            <th class="py-3 px-6 font-semibold">
                                Order ID
                            </th>

                            <?php if ($is_admin): ?>

                                <th class="py-3 px-6 font-semibold">
                                    Customer Name
                                </th>

                            <?php endif; ?>

                            <th class="py-3 px-6 font-semibold">
                                Total Price
                            </th>

                            <th class="py-3 px-6 font-semibold">
                                Status
                            </th>

                            <?php if ($is_admin): ?>

                                <th class="py-3 px-6 font-semibold">
                                    Update Status
                                </th>

                            <?php endif; ?>

                            <th class="py-3 px-6 font-semibold">
                                Order Date
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100 text-sm">

                        <?php if ($total_orders > 0): ?>

                            <?php foreach ($orders as $ord): ?>

                                <?php

                                $current_status = strtolower($ord['status'] ?? 'pending');

                                $status_class = $status_colors[$current_status]
                                    ?? 'bg-slate-100 text-slate-700 border-slate-200';

                                ?>

                                <tr class="hover:bg-slate-50/80 transition">

                                    <!-- Order ID -->

                                    <td class="py-4 px-6 font-semibold text-indigo-600">

                                        #<?= (int)$ord['id'] ?>

                                    </td>


                                    <!-- Customer Information (Admin Only) -->

                                    <?php if ($is_admin): ?>

                                        <td class="py-4 px-6 font-medium text-slate-800">

                                            <?= htmlspecialchars($ord['customer_name']) ?>

                                            <div class="text-xs text-slate-400">

                                                <?= htmlspecialchars($ord['customer_email'] ?? '') ?>

                                            </div>

                                        </td>

                                    <?php endif; ?>


                                    <!-- Total Price -->

                                    <td class="py-4 px-6 font-extrabold text-slate-900">

                                        ₹<?= number_format((float)$ord['total_price'], 2) ?>

                                    </td>


                                    <!-- Current Status -->

                                    <td class="py-4 px-6">

                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $status_class ?>">

                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>

                                            <?= htmlspecialchars(ucfirst($current_status)) ?>

                                        </span>

                                    </td>


                                    <!-- Admin Status Update -->

                                    <?php if ($is_admin): ?>

                                        <td class="py-4 px-6">

                                            <form method="POST"
                                                  class="flex items-center gap-2">

                                                <input type="hidden"
                                                       name="order_id"
                                                       value="<?= (int)$ord['id'] ?>">

                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                                <select name="status"
                                                        class="border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none bg-white">

                                                    <?php foreach ($allowed_statuses as $value => $label): ?>

                                                        <option value="<?= htmlspecialchars($value) ?>"
                                                            <?= ($current_status === $value) ? 'selected' : '' ?>>

                                                            <?= htmlspecialchars($label) ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                                <button type="submit"
                                                        name="update_status"
                                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap">

                                                    <i class="fa-solid fa-rotate mr-1"></i>
                                                    Update

                                                </button>

                                            </form>

                                        </td>

                                    <?php endif; ?>


                                    <!-- Order Date -->

                                    <td class="py-4 px-6 text-slate-500 text-xs whitespace-nowrap">

                                        <?= htmlspecialchars($ord['created_at']) ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="<?= $is_admin ? 6 : 4 ?>"
                                    class="py-12 text-center text-slate-400">

                                    <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 block"></i>

                                    No orders found in your history.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- Status Information -->

        <?php if ($is_admin): ?>

            <div class="mt-6 bg-white rounded-xl border border-slate-200 p-5">

                <h3 class="text-sm font-bold text-slate-800 mb-4">

                    <i class="fa-solid fa-circle-info text-indigo-500 mr-2"></i>
                    Order Status Guide

                </h3>

                <div class="flex flex-wrap gap-3 text-xs font-semibold">

                    <span class="px-3 py-2 rounded-lg bg-amber-100 text-amber-800">
                        Pending
                    </span>

                    <span class="px-3 py-2 rounded-lg bg-blue-100 text-blue-800">
                        Processing
                    </span>

                    <span class="px-3 py-2 rounded-lg bg-purple-100 text-purple-800">
                        Shipped
                    </span>

                    <span class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-800">
                        Completed
                    </span>

                    <span class="px-3 py-2 rounded-lg bg-red-100 text-red-800">
                        Cancelled
                    </span>

                </div>

            </div>

        <?php endif; ?>

    </main>


    <!-- Footer -->

    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800 py-6 mt-16 text-center">

        <p>
            &copy; <?= date('Y') ?> SalesOut Inc. All rights reserved.
        </p>

    </footer>

</body>

</html>

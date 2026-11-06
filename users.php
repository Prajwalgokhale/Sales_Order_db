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

// Handle User Deletion Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $uid = intval($_GET['id']);
    
    // Prevent deleting your own active session account
    if ($uid === intval($_SESSION['user_id'])) {
        header("Location: users.php?msg=" . urlencode("You cannot delete your own active admin account!"));
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    header("Location: users.php?msg=" . urlencode("User account deleted successfully."));
    exit;
}

// Fetch all registered users
$users_stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
$total_users = count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SalesOut Admin</title>
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
                <a href="admin_dashboard.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
                <a href="products.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-box"></i> Products
                </a>
                <a href="orders.php" class="text-slate-300 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-clipboard-list"></i> Orders
                </a>
                <a href="users.php" class="text-white bg-slate-800 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-users text-indigo-400"></i> Users
                </a>
                
                <div class="flex items-center gap-2 border-l border-slate-800 pl-4">
                    <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold px-2.5 py-1 rounded-full uppercase">
                        Admin
                    </span>
                    <a href="logout.php" title="Logout" class="text-slate-400 hover:text-red-400 ml-2 transition">
                        <i class="fa-solid fa-right-from-bracket text-base"></i>
                    </a>
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

        <!-- Page Top Action Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">User Accounts Directory</h2>
                    <p class="text-slate-500 text-xs">Manage registered users, inspect role privileges, and register new accounts.</p>
                </div>
            </div>
            <a href="register.php" class="bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg shadow-indigo-600/30 transition text-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-plus text-xs"></i>
                <span>Create New User / Admin</span>
            </a>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Registered Profiles</h3>
                    <p class="text-slate-500 text-xs">Total registered accounts: <span class="font-semibold text-slate-800"><?= $total_users ?></span></p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-6 font-semibold">ID</th>
                            <th class="py-3 px-6 font-semibold">Full Name</th>
                            <th class="py-3 px-6 font-semibold">Email Address</th>
                            <th class="py-3 px-6 font-semibold">Role Privilege</th>
                            <th class="py-3 px-6 font-semibold">Account Created</th>
                            <th class="py-3 px-6 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if ($total_users > 0): ?>
                            <?php foreach ($users as $usr): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6 font-semibold text-slate-500">#<?= $usr['id'] ?></td>
                                    <td class="py-4 px-6 font-bold text-slate-900 flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs uppercase border border-slate-200">
                                            <?= strtoupper(substr($usr['name'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($usr['name']) ?>
                                    </td>
                                    <td class="py-4 px-6 text-slate-600"><?= htmlspecialchars($usr['email']) ?></td>
                                    <td class="py-4 px-6">
                                        <?php if ($usr['role'] === 'admin'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <i class="fa-solid fa-shield-halved text-[10px]"></i> ADMIN
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                <i class="fa-solid fa-user text-[10px]"></i> USER
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 text-xs"><?= htmlspecialchars($usr['created_at']) ?></td>
                                    <td class="py-4 px-6 text-right">
                                        <?php if (intval($usr['id']) !== intval($_SESSION['user_id'])): ?>
                                            <a href="users.php?action=delete&id=<?= $usr['id'] ?>" onclick="return confirm('Are you sure you want to delete user <?= htmlspecialchars($usr['name'], ENT_QUOTES) ?>? This action cannot be undone.');" class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-xs py-1.5 px-3 rounded-lg border border-red-200 transition">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="inline-block text-xs font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">
                                                Current Account
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-users-slash text-3xl mb-2 text-slate-300 block"></i>
                                    No user accounts registered yet.
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
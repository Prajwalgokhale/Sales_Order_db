<?php
require_once 'db.php';

// Initialize session safely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = $_GET['msg'] ?? '';

// Handle POST Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php?msg=Welcome back Admin, " . urlencode($user['name']));
            } else {
                header("Location: customer_dashboard.php?msg=" . urlencode("Welcome back, " . $user['name']));
exit();
            }
            exit;
        } else {
            header("Location: login.php?msg=" . urlencode("Invalid email or password!"));
            exit;
        }
    } else {
        header("Location: login.php?msg=" . urlencode("Please fill in all fields."));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SalesOut Portal</title>
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
                <a href="index.php" class="text-slate-300 hover:text-white transition">Catalog</a>
                <a href="register.php" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-1.5 rounded-lg font-semibold shadow-sm transition">Register</a>
            </nav>
        </div>
    </header>

    <!-- Main Login Container -->
    <main class="max-w-md mx-auto px-4 py-12 flex-1 w-full flex flex-col justify-center">
        
        <!-- Flash Alert Message -->
        <?php if (!empty($msg)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($msg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xl overflow-hidden p-8">
            <div class="text-center mb-8">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-indigo-100 shadow-sm">
                    <i class="fa-solid fa-lock text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Welcome Back</h2>
                <p class="text-slate-500 text-xs mt-1">Please enter your credentials to access your account</p>
            </div>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </span>
                        <input type="email" name="email" required placeholder="name@example.com" class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                        <a href="#" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-key text-sm"></i>
                        </span>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                </div>

                <button type="submit" class="w-full mt-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-6">
                <p class="text-xs text-slate-500">
                    Don't have an account yet? 
                    <a href="register.php" class="text-indigo-600 hover:text-indigo-800 font-semibold transition ml-1">Create account</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Modern Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800 py-6 mt-12 text-center">
        <p>&copy; <?= date('Y') ?> SalesOut Inc. All rights reserved.</p>
    </footer>

</body>
</html>
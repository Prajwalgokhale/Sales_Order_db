<?php
require_once 'db.php';
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? 'user';

    // Only allow role creation override if logged in as admin
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        $role = 'user';
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header("Location: register.php?msg=Email already registered!");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);
    header("Location: login.php?msg=Account created successfully. Please log in.");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SalesOut</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef2ff, #f8fafc, #e0e7ff);
            color: #1e293b;
        }

        /* Header */
        header {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.04);
            border-bottom: 1px solid #e5e7eb;
        }

        header h2 a {
            color: #2563eb;
            text-decoration: none;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        nav {
            display: flex;
            gap: 28px;
        }

        header nav a {
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }

        header nav a:hover {
            color: #2563eb;
        }

        /* Main Container */
        .container {
            max-width: 480px;
            margin: 65px auto;
            padding: 0 20px;
        }

        /* Registration Card */
        .card {
            background: #ffffff;
            padding: 38px 35px;
            border-radius: 18px;
            box-shadow: 0 15px 45px rgba(37,99,235,0.09);
            border: 1px solid rgba(226,232,240,0.8);
        }

        .card-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .icon-box {
            width: 65px;
            height: 65px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 18px;
            font-size: 28px;
        }

        .card-header h3 {
            font-size: 25px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 9px;
            letter-spacing: -0.7px;
        }

        .card-header p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        /* Alert */
        .alert {
            background: #fef2f2;
            color: #b91c1c;
            padding: 13px 15px;
            border-radius: 9px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
            font-size: 13px;
            font-weight: 500;
        }

        /* Form */
        form label {
            display: block;
            margin-top: 19px;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        form input,
        form select {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            outline: none;
            background: #f8fafc;
            color: #1e293b;
            font-size: 13px;
            transition: 0.3s;
        }

        form input:focus,
        form select:focus {
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }

        form input::placeholder {
            color: #94a3b8;
        }

        /* Button */
        .btn {
            display: block;
            width: 100%;
            margin-top: 27px;
            padding: 14px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(37,99,235,0.2);
        }

        .btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.25);
        }

        /* Login Link */
        .login-text {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        .login-text a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 700;
        }

        .login-text a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 25px;
            color: #94a3b8;
            font-size: 11px;
        }

        /* Responsive */
        @media (max-width: 500px) {
            header {
                padding: 16px 5%;
            }

            header h2 a {
                font-size: 21px;
            }

            nav {
                gap: 15px;
            }

            .container {
                margin: 35px auto;
            }

            .card {
                padding: 30px 23px;
            }

            .card-header h3 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<header>
    <h2><a href="index.php">SalesOut</a></h2>

    <nav>
        <a href="index.php">Products</a>
        <a href="login.php">Login</a>
    </nav>
</header>

<div class="container">

    <?php if ($msg): ?>
        <div class="alert"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card">

        <div class="card-header">
            <div class="icon-box">👤</div>
            <h3>Create Account</h3>
            <p>Join SalesPortal and get started with your account today.</p>
        </div>

        <form method="POST">

            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter your full name" required>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email address" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" required>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <label>Account Role Privilege</label>
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            <?php endif; ?>

            <button type="submit" class="btn">Create Account →</button>

        </form>

        <p class="login-text">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>

    </div>

    <div class="footer">
        © 2026 SalesOut. All rights reserved.
    </div>

</div>

</body>
</html>
<?php
session_start();
require_once __DIR__ . '/includes/auth_helper.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    $login = $auth->login($email, $password, $remember);
    if ($login['error']) {
        $error = $login['message'];
    } else {
        setcookie($auth_config->cookie_name, $login['hash'], $login['expire'], $auth_config->cookie_path, $auth_config->cookie_domain, $auth_config->cookie_secure, $auth_config->cookie_http);
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Prayag Computer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #28a745;
            --accent-yellow: #ffc107;
        }
        body.login-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            width: 400px;
            perspective: 1000px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: var(--primary-green) !important;
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: none;
        }
        .card-header img {
            max-height: 60px;
            margin-bottom: 15px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        .card-body {
            padding: 40px;
            background: white;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1);
        }
        .input-group-text {
            border-radius: 8px;
            background-color: #f8f9fa;
            border-color: #e0e0e0;
            color: #6c757d;
        }
        .btn-primary {
            background-color: var(--primary-green) !important;
            border-color: var(--primary-green) !important;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #218838 !important;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .login-box-msg {
            font-size: 1.1rem;
            color: #495057;
            margin-bottom: 25px;
        }
        .alert {
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .icheck-primary label {
            font-weight: 500 !important;
            color: #6c757d;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-box">
        <div class="card">
            <div class="card-header">
                <img src="./src/images/prayag-computer-logo.png" alt="Logo">
                <h4 class="mb-0">Admin Portal</h4>
            </div>
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

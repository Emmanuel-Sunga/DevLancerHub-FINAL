<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

SessionMiddleware::start();
AuthMiddleware::redirectIfAuthenticated();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->login();
    
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $errors = $result['errors'];
        $email = $_POST['email'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
				<a href="index.php">
					<h1>DevLancerHub</h1>
					<p class="tagline">IT & CS Talent Platform</p>
                </a>
            </div>
            <div class="nav-links">
				<a href="register.php" class="btn btn-outline">Register</a>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card">
            <h2>Welcome Back</h2>
			<p class="auth-subtitle">Login to your account</p>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

			<form method="POST" action="login.php" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>

            <p class="auth-footer">
				Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>

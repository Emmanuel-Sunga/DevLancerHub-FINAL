<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;

SessionMiddleware::start();
AuthMiddleware::redirectIfAuthenticated();

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->register();
    
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $errors = $result['errors'];
        $formData = $_POST;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register</title>
	<link rel="stylesheet" href="css/style.css">
	<script src="js/register.js" defer></script>
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
				<a href="login.php" class="btn btn-outline">Login</a>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card register-card">
			<h2>Create your account</h2>
            <p class="auth-subtitle">Create your professional account</p>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

			<form method="POST" action="register.php" class="auth-form" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['first_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
                        <?php if (isset($errors['last_name'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['last_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                        <small>Min 8 characters, include uppercase, lowercase, and number</small>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['password']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['confirm_password']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">I am a *</label>
                    <select id="role" name="role" required>
                        <option value="">Select your role</option>
                        <option value="employee" <?= ($formData['role'] ?? '') === 'employee' ? 'selected' : '' ?>>Freelancer (Employee)</option>
                        <option value="employer" <?= ($formData['role'] ?? '') === 'employer' ? 'selected' : '' ?>>Client (Employer)</option>
                    </select>
                    <?php if (isset($errors['role'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['role']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required>
                        <?php if (isset($errors['phone'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['phone']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" value="<?= htmlspecialchars($formData['location'] ?? '') ?>" required>
                        <?php if (isset($errors['location'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['location']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="employeeFields" style="display: none;">
                    <div class="form-group">
                        <label for="skills">Technical Skills * (e.g., PHP, JavaScript, Python)</label>
                        <textarea id="skills" name="skills" rows="3"><?= htmlspecialchars($formData['skills'] ?? '') ?></textarea>
                        <?php if (isset($errors['skills'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['skills']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="experience_years">Years of Experience *</label>
                        <input type="number" id="experience_years" name="experience_years" min="0" value="<?= htmlspecialchars($formData['experience_years'] ?? '') ?>">
                        <?php if (isset($errors['experience_years'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['experience_years']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Bio (Optional)</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."><?= htmlspecialchars($formData['bio'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>

            <p class="auth-footer">
				Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>

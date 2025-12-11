<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;

SessionMiddleware::start();
AuthMiddleware::redirectIfAuthenticated();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Home</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
				<h1>DevLancerHub</h1>
				<p class="tagline">IT & CS Talent Platform</p>
            </div>
            <div class="nav-links">
				<a href="login.php" class="btn btn-outline">Login</a>
				<a href="register.php" class="btn btn-primary">Get Started</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Connect with Top IT & Computer Science Talent</h2>
                <p>The premier platform for technology professionals to find opportunities and showcase their expertise</p>
                <div class="hero-buttons">
					<a href="register.php" class="btn btn-primary btn-large">Join as Freelancer</a>
					<a href="register.php" class="btn btn-secondary btn-large">Hire Talent</a>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
			<h3>Why Choose Us?</h3>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">💼</div>
                    <h4>For Freelancers</h4>
                    <p>Showcase your skills, build your portfolio, and find exciting projects in IT and Computer Science</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h4>For Employers</h4>
                    <p>Post jobs, browse qualified candidates, and connect with expert developers and tech professionals</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h4>Secure Platform</h4>
                    <p>Built with security in mind, ensuring your data and transactions are protected</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
			<p>&copy; 2025. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

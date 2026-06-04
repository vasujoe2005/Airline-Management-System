<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: passenger_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Airline Ticketing Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --text-main: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --white: #ffffff;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-main);
            overflow-x: hidden;
        }

        .hero {
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: var(--white);
            text-align: center;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 40px;
            max-width: 600px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .cta-button {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.4);
        }

        .cta-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(59, 130, 246, 0.5);
        }

        .features {
            padding: 80px 20px;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: var(--white);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: inline-block;
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-main);
            font-weight: 600;
        }

        .feature-desc {
            color: var(--text-light);
            line-height: 1.6;
        }

        footer {
            background-color: var(--text-main);
            color: var(--white);
            padding: 40px 20px;
            text-align: center;
        }

        .login-link {
            position: absolute;
            top: 20px;
            right: 20px;
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 50px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .login-link:hover {
            background-color: var(--white);
            color: var(--text-main);
        }
    </style>
</head>
<body>
    <a href="login.php" class="login-link">Sign In</a>
    
    <div class="hero">
        <h1>Real-Time Airline Ticketing Platform</h1>
        <p>Premium air travel booking with real-time seat selection and instant confirmation.</p>
        <a href="login.php" class="cta-button">Book Your Flight Now</a>
    </div>

    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">✈️</div>
            <div class="feature-title">Global Destinations</div>
            <div class="feature-desc">Connect with hundreds of cities worldwide with our extensive partner network.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💺</div>
            <div class="feature-title">Comfort First</div>
            <div class="feature-desc">Choose from First, Business, or Economy class with our interactive seat map.</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <div class="feature-title">Instant Booking</div>
            <div class="feature-desc">Secure your seat in seconds with our streamlined, real-time reservation system.</div>
        </div>
    </div>
</body>
</html>

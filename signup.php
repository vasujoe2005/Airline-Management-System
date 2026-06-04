<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Real-Time Airline Ticketing Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --bg-light: #f8fafc;
            --text-main: #0f172a;
            --text-light: #64748b;
            --white: #ffffff;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-container {
            background: white;
            padding: 3rem 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 400px;
            border: 1px solid #e2e8f0;
        }

        h2 {
            text-align: center;
            color: var(--text-main);
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            font-size: 1.75rem;
        }
        
        .subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.875rem 1rem;
            margin: 0.5rem 0 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        button {
            width: 100%;
            padding: 0.875rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .error {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border: 1px solid #fecaca;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: var(--text-light);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Create Account</h2>
        <p class="subtitle">Join Real-Time Airline Ticketing Platform for premium air travel</p>
        
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="post" action="">
            <div style="margin-bottom: 0.5rem; color: var(--text-main); font-weight: 500; font-size: 0.9rem;">Username</div>
            <input type="text" name="username"  required>
            
            <div style="margin-bottom: 0.5rem; color: var(--text-main); font-weight: 500; font-size: 0.9rem;">Email Address</div>
            <input type="email" name="email"  required>
            
            <div style="margin-bottom: 0.5rem; color: var(--text-main); font-weight: 500; font-size: 0.9rem;">Password</div>
            <input type="password" name="password" required>
            
            <button type="submit">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>

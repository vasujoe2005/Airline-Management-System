<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: passenger_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Real-Time Airline Ticketing Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --text-main: #0f172a;
            --text-light: #64748b;
            --bg-light: #f1f5f9;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
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

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.875rem 1rem;
            margin: 0.5rem 0 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s;
            box-sizing: border-box; /* Ensure padding doesn't affect width */
        }

        input[type="email"]:focus, input[type="password"]:focus {
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

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: var(--text-light);
        }

        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Welcome Back</h2>
        <p class="subtitle">Please enter your details to sign in</p>
        
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="post" action="">
            <div style="margin-bottom: 0.5rem; color: var(--text-main); font-weight: 500; font-size: 0.9rem;">Email Address</div>
            <input type="email" name="email"  required>
            
            <div style="margin-bottom: 0.5rem; color: var(--text-main); font-weight: 500; font-size: 0.9rem;">Password</div>
            <input type="password" name="password"  required>
            
            <button type="submit">Sign In</button>
        </form>
        
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Create account</a>
        </div>
    </div>
</body>
</html>

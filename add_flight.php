<?php
session_start();
include 'db.php';
include 'functions.php';
check_admin();

$edit = false;
if (isset($_GET['edit'])) {
    $edit = true;
    $flight_id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM flights WHERE id = $flight_id");
    $flight = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $first_price = $_POST['first_price'];
    $business_price = $_POST['business_price'];
    $economy_price = $_POST['economy_price'];

    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        echo "<script>alert('Error: Cannot schedule flights in the past.'); window.history.back();</script>";
        exit();
    }
    
    if ($edit) {
        $stmt = $conn->prepare("UPDATE flights SET source=?, destination=?, date=?, time=?, first_class_price=?, business_price=?, economy_price=? WHERE id=?");
        $stmt->bind_param("ssssdddi", $source, $destination, $date, $time, $first_price, $business_price, $economy_price, $flight_id);
        $stmt->execute();
        // Update seat prices
        $conn->query("UPDATE seats SET price=$first_price WHERE flight_id=$flight_id AND class='first'");
        $conn->query("UPDATE seats SET price=$business_price WHERE flight_id=$flight_id AND class='business'");
        $conn->query("UPDATE seats SET price=$economy_price WHERE flight_id=$flight_id AND class='economy'");
        header("Location: admin_dashboard.php");
    } else {
        $stmt = $conn->prepare("INSERT INTO flights (source, destination, date, time, first_class_price, business_price, economy_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddd", $source, $destination, $date, $time, $first_price, $business_price, $economy_price);
        $stmt->execute();
        $flight_id = $conn->insert_id;

        // Insert seats with continuous row numbering
        $global_row = 1;
        
        // Configuration: [Class, NumRows, Letters]
        // Updated to ensure symmetric/even distribution (e.g. 2-2 or 3-3)
        $seat_configs = [
            ['first', 2, ['A','B','E','F']], // 2-2 configuration (Symmetric)
            ['business', 5, ['A','B','C','D','E','F']], // 3-3 configuration (Symmetric)
            ['economy', 15, ['A','B','C','D','E','F']] // 3-3 configuration (Symmetric)
        ];

        foreach ($seat_configs as $config) {
            $class = $config[0];
            $num_rows = $config[1];
            $letters = $config[2];
            $price = ($class == 'first') ? $first_price : (($class == 'business') ? $business_price : $economy_price);
            
            for ($i = 0; $i < $num_rows; $i++) {
                foreach ($letters as $letter) {
                    $seat_number = $global_row . $letter;
                    $conn->query("INSERT INTO seats (flight_id, seat_number, class, price) VALUES ($flight_id, '$seat_number', '$class', $price)");
                }
                $global_row++;
            }
        }
        header("Location: admin_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit ? 'Edit Flight' : 'Add New Flight'; ?> | Real-Time Airline Ticketing Platform</title>
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
            --success: #10b981;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background-color: var(--white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            text-decoration: none;
        }
        
        .navbar-brand span {
            color: var(--primary);
        }

        .container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        h2 {
            margin: 0 0 2rem 0;
            font-size: 1.75rem;
            font-weight: 700;
            text-align: center;
            color: var(--text-main);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 0.875rem;
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }
        
        .btn-update {
            background-color: var(--primary);
        }
        
        .btn-update:hover {
             background-color: var(--primary-dark);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="navbar-brand">Real-Time Airline Ticketing Platform</a>
    </nav>

    <div class="container">
        <div class="card">
            <h2><?php echo $edit ? 'Update Flight Details' : 'Schedule New Flight'; ?></h2>
            <form method="post" action="">
                <div class="form-group">
                    <label class="form-label">Source Airport</label>
                    <input type="text" name="source" class="form-control" value="<?php echo $flight['source'] ?? ''; ?>" required placeholder="e.g. Mumbai">
                </div>
                <div class="form-group">
                    <label class="form-label">Destination Airport</label>
                    <input type="text" name="destination" class="form-control" value="<?php echo $flight['destination'] ?? ''; ?>" required placeholder="e.g. Chennai">
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $flight['date'] ?? ''; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Time</label>
                    <input type="time" name="time" class="form-control" value="<?php echo $flight['time'] ?? ''; ?>" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">First Class ($)</label>
                        <input type="number" step="0.01" name="first_price" class="form-control" value="<?php echo $flight['first_class_price'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Business ($)</label>
                        <input type="number" step="0.01" name="business_price" class="form-control" value="<?php echo $flight['business_price'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Economy ($)</label>
                        <input type="number" step="0.01" name="economy_price" class="form-control" value="<?php echo $flight['economy_price'] ?? ''; ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn <?php echo $edit ? 'btn-update' : ''; ?>"><?php echo $edit ? 'Save Changes' : 'Create Flight'; ?></button>
            </form>
            <a href="admin_dashboard.php" class="back-link">Cancel and Return</a>
        </div>
    </div>
</body>
</html>

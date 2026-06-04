<?php
session_start();
include 'db.php';
include 'functions.php';
check_passenger();

$flights = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $date = $_POST['date'];

    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        echo "<script>alert('Error: Cannot search for flights in the past.'); window.location.href='passenger_dashboard.php';</script>";
        exit();
    }

    $time = isset($_POST['time']) && !empty($_POST['time']) ? $_POST['time'] : null;

    $query = "SELECT * FROM flights WHERE source LIKE ? AND destination LIKE ? AND date = ?";
    $params = ["%$source%", "%$destination%", $date];
    $types = "sss";

    if ($time) {
        $query .= " AND time >= ?";
        $params[] = $time;
        $types .= "s";
    }

    $query .= " ORDER BY id DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $flights = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Dashboard | Real-Time Airline Ticketing Platform</title>
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
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-btn {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .nav-btn:hover {
            background-color: var(--primary-dark);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Search Section */
        .search-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid #e2e8f0;
        }

        .search-card h2 {
            margin: 0 0 1.5rem 0;
            color: var(--text-main);
            font-weight: 600;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-light);
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-btn {
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            height: 48px; /* Match input height roughly */
        }

        .search-btn:hover {
            background-color: var(--primary-dark);
        }

        /* Results Table */
        .results-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .table-container {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f1f5f9;
            text-align: left;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
        }

        td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            color: var(--text-main);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .price {
            font-weight: 600;
            color: var(--text-main);
        }

        .btn-view {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--white);
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-view:hover {
            background-color: var(--primary);
            color: white;
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">Real-Time Airline Ticketing Platform</a>
        <div class="navbar-nav">
            <a href="history.php" class="nav-link">My Bookings</a>
            <span style="color: #cbd5e1">|</span>
            <a href="logout.php" class="nav-link" style="color: #ef4444;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="search-card">
            <h2>Find Your Flight</h2>
            <form method="post" action="" class="search-form">
                <div class="form-group">
                    <label class="form-label">From</label>
                    <input type="text" name="source" class="form-control" placeholder="Origin City" required>
                </div>
                <div class="form-group">
                    <label class="form-label">To</label>
                    <input type="text" name="destination" class="form-control" placeholder="Destination City" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Time (Optional)</label>
                    <input type="time" name="time" class="form-control">
                </div>
                <button type="submit" class="search-btn">Search Flights</button>
            </form>
        </div>

        <?php if (!empty($flights)): ?>
            <div class="results-section">
                <h2>Available Flights</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th>Schedule</th>
                                <th>First Class</th>
                                <th>Business</th>
                                <th>Economy</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flights as $flight): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($flight['source']); ?></div>
                                        <div style="color: var(--text-light); font-size: 0.9rem;">to <?php echo htmlspecialchars($flight['destination']); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($flight['date'])); ?></div>
                                        <div style="color: var(--text-light); font-size: 0.9rem;"><?php echo date('H:i', strtotime($flight['time'])); ?></div>
                                    </td>
                                    <td class="price">$<?php echo number_format($flight['first_class_price'], 2); ?></td>
                                    <td class="price">$<?php echo number_format($flight['business_price'], 2); ?></td>
                                    <td class="price">$<?php echo number_format($flight['economy_price'], 2); ?></td>
                                    <td>
                                        <a href="flight_view.php?flight_id=<?php echo $flight['id']; ?>" class="btn-view">Select Seats</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

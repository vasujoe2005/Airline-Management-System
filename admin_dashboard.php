<?php
session_start();
include 'db.php';
include 'functions.php';
check_admin();

if (isset($_GET['delete'])) {
    $flight_id = $_GET['delete'];
    $conn->query("DELETE FROM flights WHERE id = $flight_id");
    $conn->query("DELETE FROM seats WHERE flight_id = $flight_id");
    $conn->query("DELETE FROM bookings WHERE flight_id = $flight_id");
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Real-Time Airline Ticketing Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
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
            color: var(--text-main);
        }
        
        .navbar-brand span {
            color: var(--primary);
        }

        .logout-btn {
            color: var(--danger);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Header Actions */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .header-btns {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: white;
            color: var(--text-main);
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            border-color: var(--secondary);
            background-color: #f1f5f9;
        }

        /* Table Card */
        .card {
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
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            color: var(--text-main);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        /* Action Buttons in Grid */
        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-icon {
            padding: 0.4rem;
            border-radius: 0.375rem;
            color: var(--text-light);
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
        }

        .action-icon:hover {
            background: #e2e8f0;
            color: var(--primary);
        }
        
        .action-icon.delete:hover {
            background: #fef2f2;
            color: var(--danger);
        }

        .price-badge {
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.85rem;
            font-family: monospace;
            color: var(--text-main);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">Real-Time Airline Ticketing Platform</div>
        <a href="logout.php" class="logout-btn">Sign Out</a>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Flight Management</h1>
            <div class="header-btns">
                <a href="view_bookings.php" class="btn btn-secondary">View Bookings</a>
                <a href="add_flight.php" class="btn btn-primary">+ New Flight</a>
            </div>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Route</th>
                        <th>Schedule</th>
                        <th>Pricing (F/B/E)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM flights ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>
                                <div style='font-weight:600'>{$row['source']}</div>
                                <div style='font-size:0.85rem; color:var(--secondary)'>to {$row['destination']}</div>
                              </td>";
                        echo "<td>
                                <div>" . date('M d, Y', strtotime($row['date'])) . "</div>
                                <div style='font-size:0.85rem; color:var(--secondary)'>" . date('H:i', strtotime($row['time'])) . "</div>
                              </td>";
                        echo "<td>
                                <span class='price-badge'>\${$row['first_class_price']}</span>
                                <span class='price-badge'>\${$row['business_price']}</span>
                                <span class='price-badge'>\${$row['economy_price']}</span>
                              </td>";
                        echo "<td class='actions'>";
                        echo "<a href='add_flight.php?edit={$row['id']}' class='action-icon' title='Edit'>✏️</a>";
                        echo "<a href='admin_seat_view.php?flight_id={$row['id']}' class='action-icon' title='View Seats'>💺</a>";
                        echo "<a href='admin_dashboard.php?delete={$row['id']}' class='action-icon delete' onclick='return confirm(\"Are you sure?\")' title='Delete'>🗑️</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
session_start();
include 'db.php';
include 'functions.php';
check_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Bookings | Real-Time Airline Ticketing Platform</title>
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
            color: var(--text-main);
            text-decoration: none;
        }
        
        .navbar-brand span {
            color: var(--primary);
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

        /* Container */
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h2 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-main);
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
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .ticket-badge {
            background-color: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--secondary);
            border: 1px solid #e2e8f0;
        }

        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-confirmed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="admin_dashboard.php" class="navbar-brand">Real-Time Airline Ticketing Platform</a>
        <a href="admin_dashboard.php" class="nav-link">Back to Dashboard</a>
    </nav>
    <div class="container">
        <div class="page-header">
            <h2>All Bookings</h2>
        </div>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Flight Details</th>
                        <th>Seats</th>
                         <th>Ticket No.</th>
                        <th>Status</th>
                        <th>Date Booked</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT b.id, u.username, u.email, f.source, f.destination, f.date, f.time, b.seat_ids, b.ticket_number, b.booking_date, b.status FROM bookings b JOIN users u ON b.user_id = u.id JOIN flights f ON b.flight_id = f.id ORDER BY b.booking_date DESC");
                    while ($row = $result->fetch_assoc()) {
                        $seat_ids = explode(',', $row['seat_ids']);
                        $seats = [];
                        foreach ($seat_ids as $id) {
                            $seat = $conn->query("SELECT seat_number FROM seats WHERE id = $id")->fetch_assoc();
                            $seats[] = $seat['seat_number'];
                        }
                        echo "<tr>";
                        echo "<td>#{$row['id']}</td>";
                        echo "<td style='font-weight:500'>{$row['username']}</td>";
                        echo "<td style='color:var(--text-light)'>{$row['email']}</td>";
                        echo "<td>
                                <div style='font-weight:600'>{$row['source']} → {$row['destination']}</div>
                                <div style='font-size:0.85rem; color:var(--secondary)'>" . date('M d, Y', strtotime($row['date'])) . " at " . date('H:i', strtotime($row['time'])) . "</div>
                              </td>";
                        echo "<td>" . implode(', ', $seats) . "</td>";
                         echo "<td><span class='ticket-badge'>{$row['ticket_number']}</span></td>";
                        $statusClass = ($row['status'] == 'cancelled') ? 'status-cancelled' : 'status-confirmed';
                        echo "<td><span class='status-badge $statusClass'>{$row['status']}</span></td>";
                        echo "<td style='color:var(--text-light); font-size:0.9rem'>" . date('M d, Y H:i', strtotime($row['booking_date'])) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

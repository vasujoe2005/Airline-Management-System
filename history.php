<?php
session_start();
include 'db.php';
include 'functions.php';
check_passenger();

$user_id = $_SESSION['user_id'];
$bookings = $conn->query("SELECT b.*, f.source, f.destination, f.date, f.time FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE b.user_id = $user_id ORDER BY b.booking_date DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Real-Time Airline Ticketing Platform</title>
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
        
        .nav-link {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-header {
            margin-bottom: 2rem;
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
            padding: 1.25rem 1.5rem;
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
        
        .ticket-badge {
            background-color: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--secondary);
            border: 1px solid #e2e8f0;
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

        .btn-cancel {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--white);
            color: #ef4444;
            border: 1px solid #ef4444;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
            margin-left: 0.5rem;
        }

        .btn-cancel:hover {
            background-color: #ef4444;
            color: white;
        }

        .btn-disabled {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #f1f5f9;
            color: #94a3b8;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: not-allowed;
            margin-left: 0.5rem;
        }

        .status-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 1rem;
            font-size: 0.75rem;
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

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">Real-Time Airline Ticketing Platform</a>
        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <a href="passenger_dashboard.php" class="nav-link">Search Flights</a>
            <span style="color: #cbd5e1">|</span>
            <a href="logout.php" class="nav-link" style="color: #ef4444;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
             <h2>My Travel History</h2>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="card" style="text-align: center; padding: 4rem;">
                <p style="color: var(--text-light); margin-bottom: 1rem;">You haven't booked any flights yet.</p>
                <a href="passenger_dashboard.php" class="btn-view" style="background: var(--primary); color: white; border: none;">Book a Flight</a>
            </div>
        <?php else: ?>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket No.</th>
                            <th>Flight Details</th>
                            <th>Date & Time</th>
                            <th>Seats</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><span class="ticket-badge"><?php echo $booking['ticket_number']; ?></span></td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($booking['source']); ?> <span style="color:var(--text-light); font-weight:400;">to</span> <?php echo htmlspecialchars($booking['destination']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo date('M d, Y', strtotime($booking['date'])); ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-light);"><?php echo date('H:i', strtotime($booking['time'])); ?></div>
                                </td>
                                <td>
                                    <?php 
                                    $seat_ids = explode(',', $booking['seat_ids']);
                                    $seat_numbers = [];
                                    foreach ($seat_ids as $id) {
                                        $seat = $conn->query("SELECT seat_number FROM seats WHERE id = $id")->fetch_assoc();
                                        $seat_numbers[] = $seat['seat_number'];
                                    }
                                    echo implode(', ', $seat_numbers);
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo ($booking['status'] == 'cancelled') ? 'status-cancelled' : 'status-confirmed'; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="ticket.php?ticket_number=<?php echo $booking['ticket_number']; ?>" class="btn-view">View Ticket</a>
                                        <?php 
                                        if ($booking['status'] != 'cancelled') {
                                            $can_cancel = is_cancellation_allowed($booking['date'], $booking['time']);
                                            
                                            if ($can_cancel['allowed']) {
                                                echo '<a href="cancel_booking.php?ticket_number=' . $booking['ticket_number'] . '" class="btn-cancel" onclick="return confirm(\'Are you sure you want to cancel this booking?\')">Cancel</a>';
                                            } else {
                                                echo '<span class="btn-disabled" title="' . $can_cancel['reason'] . '">Cancel</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

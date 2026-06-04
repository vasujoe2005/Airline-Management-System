<?php
session_start();
include 'db.php';
include 'functions.php';
check_admin();

$flight_id = $_GET['flight_id'];
$flight = $conn->query("SELECT * FROM flights WHERE id = $flight_id")->fetch_assoc();
$seats = $conn->query("SELECT * FROM seats WHERE flight_id = $flight_id ORDER BY LENGTH(seat_number), seat_number")->fetch_all(MYSQLI_ASSOC);

// Group seats by row
$rows = [];
foreach ($seats as $seat) {
    $row_num = (int)substr($seat['seat_number'], 0, -1);
    $rows[$row_num][] = $seat;
}
ksort($rows, SORT_NUMERIC);

// Sort seats within each row by seat letter
foreach ($rows as $row_num => $row_seats) {
    usort($row_seats, function($a, $b) {
        return strcmp($a['seat_number'], $b['seat_number']);
    });
    $rows[$row_num] = $row_seats;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Seats | Real-Time Airline Ticketing Platform</title>
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
            --seat-first: #8b5cf6;
            --seat-business: #0ea5e9;
            --seat-economy: #10b981;
            --seat-booked: #94a3b8;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background-color: var(--white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            width: 100%;
            box-sizing: border-box;
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

        .plane-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 2rem 0;
        }

        .plane {
            background: var(--white);
            border: 2px solid #e2e8f0;
            border-radius: 4rem 4rem 2rem 2rem;
            padding: 4rem 3rem 3rem;
            width: 320px;
            position: relative;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .row {
            display: grid;
            /* 2-2 is default for first, but we need handling. Using flex for generic row centering */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 0.75rem;
            gap: 2rem; /* Aisle gap */
        }
        
        .row-group {
             display: flex;
             gap: 0.5rem;
        }

        .seat {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            color: white;
        }

        .seat.available.first { background-color: var(--seat-first); }
        .seat.available.business { background-color: var(--seat-business); }
        .seat.available.economy { background-color: var(--seat-economy); }
        .seat.booked { background-color: var(--seat-booked); cursor: not-allowed; opacity: 0.6; }

        .aisle {
            /* Handled by gap in flex */
            display: none; 
        }

        .section-label {
            text-align: center;
            font-weight: 600;
            margin: 1.5rem 0 1rem 0;
            color: var(--text-light);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .legend {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin: 1rem 0 2rem;
            gap: 1.5rem;
            background: var(--white);
            padding: 1rem 2rem;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .legend-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-main);
            font-weight: 500;
        }

        .legend-color {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            border-radius: 0.25rem;
        }

        .summary {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            margin: 2rem auto;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .summary h3 {
            margin: 0 0 1.5rem 0;
            color: var(--text-main);
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-block;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">Real-Time Airline Ticketing Platform</div>
        <div class="nav-links">
             <span style="margin-right: 1rem; color: var(--text-light);">Flight #<?php echo $flight['id']; ?></span>
             <a href="admin_dashboard.php" class="nav-link">Back to Dashboard</a>
        </div>
    </nav>
    
    <div style="text-align: center; margin-top: 2rem;">
        <h2 style="margin: 0; font-size: 1.5rem;"><?php echo $flight['source']; ?> <span style="color:var(--text-light)">to</span> <?php echo $flight['destination']; ?></h2>
        <p style="color:var(--text-light); margin-top: 0.5rem;"><?php echo date('M d, Y', strtotime($flight['date'])); ?> • <?php echo date('H:i', strtotime($flight['time'])); ?></p>
    </div>

    <div class="legend">
        <div class="legend-item"><div class="legend-color" style="background-color: var(--seat-first);"></div>First</div>
        <div class="legend-item"><div class="legend-color" style="background-color: var(--seat-business);"></div>Business</div>
        <div class="legend-item"><div class="legend-color" style="background-color: var(--seat-economy);"></div>Economy</div>
        <div class="legend-item"><div class="legend-color" style="background-color: var(--seat-booked);"></div>Booked</div>
    </div>
    
    <div class="plane-container">
        <div class="plane">
            <div class="section-label">First Class</div>
            <?php
            // First Class logic (2-2)
            $first_rows = array_slice($rows, 0, 2, true);
            foreach ($first_rows as $row_num => $row_seats) {
                echo "<div class='row'>";
                $left = array_slice($row_seats, 0, 2);
                $right = array_slice($row_seats, 2, 2);
                echo "<div class='row-group'>";
                foreach ($left as $seat) {
                    $class = $seat['status'] == 'booked' ? 'booked' : 'available';
                    echo "<div class='seat $class first'>{$seat['seat_number']}</div>";
                }
                echo "</div>";
                 // Gap
                echo "<div class='row-group'>";
                foreach ($right as $seat) {
                    $class = $seat['status'] == 'booked' ? 'booked' : 'available';
                    echo "<div class='seat $class first'>{$seat['seat_number']}</div>";
                }
                echo "</div>";
                echo "</div>";
            }
            ?>
            <div class="section-label">Business Class</div>
            <?php
            // Business Class logic (3-3 for 6 seats, or adjusted if 5)
            // Previously was 3-2. New add_flight.php sets 6 seats so 3-3.
            $business_rows = array_slice($rows, 2, 5, true);
            foreach ($business_rows as $row_num => $row_seats) {
                echo "<div class='row'>";
                // Check if we have 6 seats
                if(count($row_seats) >= 6) {
                    $left = array_slice($row_seats, 0, 3);
                    $right = array_slice($row_seats, 3, 3);
                } else {
                    // Fallback for old data or 5 seats (3-2)
                    $left = array_slice($row_seats, 0, 3);
                    $right = array_slice($row_seats, 3);
                }
                
                echo "<div class='row-group'>";
                foreach ($left as $seat) {
                    $class = $seat['status'] == 'booked' ? 'booked' : 'available';
                    echo "<div class='seat $class business'>{$seat['seat_number']}</div>";
                }
                echo "</div>";
                
                echo "<div class='row-group'>";
                foreach ($right as $seat) {
                    $class = $seat['status'] == 'booked' ? 'booked' : 'available';
                    echo "<div class='seat $class business'>{$seat['seat_number']}</div>";
                }
                echo "</div>";
                echo "</div>";
            }
            ?>
            <div class="section-label">Economy Class</div>
            <?php
            $economy_rows = array_slice($rows, 7, null, true);
            foreach ($economy_rows as $row_num => $row_seats) {
                echo "<div class='row'>";
                $left = array_slice($row_seats, 0, 3);
                $right = array_slice($row_seats, 3, 3);
                
                echo "<div class='row-group'>";
                foreach ($left as $seat) {
                    $class = $seat['status'] == 'booked' ? 'booked' : 'available';
                    echo "<div class='seat $class economy'>{$seat['seat_number']}</div>";
                }
                echo "</div>";
                
                echo "<div class='row-group'>";
                foreach ($right as $seat) {
                    $class = $seat['status'] == 'booked' ? 'booked' : 'available';
                    echo "<div class='seat $class economy'>{$seat['seat_number']}</div>";
                }
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    
    <div class="summary">
        <h3>Occupancy Stats</h3>
        <?php
        $total_seats = count($seats);
        $booked_seats = 0;
        foreach ($seats as $seat) {
            if ($seat['status'] == 'booked') $booked_seats++;
        }
        $available_seats = $total_seats - $booked_seats;
        ?>
        <div class="stat-grid">
            <div class="stat-item">
                <div class="stat-value"><?php echo $total_seats; ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-item">
                 <div class="stat-value" style="color:var(--success)"><?php echo $available_seats; ?></div>
                 <div class="stat-label">Available</div>
            </div>
            <div class="stat-item">
                 <div class="stat-value" style="color:var(--seat-booked)"><?php echo $booked_seats; ?></div>
                 <div class="stat-label">Booked</div>
            </div>
        </div>
        <a href="admin_dashboard.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>

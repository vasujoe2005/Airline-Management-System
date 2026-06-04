<?php
session_start();
include 'db.php';
include 'functions.php';
check_passenger();

$flight_id = isset($_GET['flight_id']) ? intval($_GET['flight_id']) : 0;
if ($flight_id === 0) {
    header("Location: passenger_dashboard.php");
    exit();
}

$flight = $conn->query("SELECT * FROM flights WHERE id = $flight_id")->fetch_assoc();
if (!$flight) {
    echo "Flight not found.";
    exit();
}

$seats_result = $conn->query("SELECT * FROM seats WHERE flight_id = $flight_id ORDER BY LENGTH(seat_number), seat_number");
$seats_by_class = ['first' => [], 'business' => [], 'economy' => []];

while ($seat = $seats_result->fetch_assoc()) {
    // Determine row number from seat numbering (e.g. "1A" -> 1)
    preg_match('/^(\d+)([A-Z]+)$/', $seat['seat_number'], $matches);
    if ($matches) {
        $r = $matches[1];
        $seats_by_class[$seat['class']][$r][] = $seat;
    } else {
        $seats_by_class[$seat['class']]['unknown'][] = $seat;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Selection | Real-Time Airline Ticketing Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #64748b;
            --success: #10b981;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --seat-first: #8b5cf6;
            --seat-business: #0ea5e9;
            --seat-economy: #10b981;
            --seat-booked: #94a3b8;
            --seat-selected: #3b82f6;
            --text-main: #0f172a;
            --text-light: #64748b;
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
        header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .flight-info h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .flight-info p {
            margin: 0.25rem 0 0;
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .header-actions .btn {
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-outline {
            border: 1px solid var(--secondary);
            color: var(--secondary);
        }
        
        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }

        /* Main Container */
        .main-container {
            display: flex;
            flex: 1;
            padding: 2rem;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        /* Plane Styling (Matched with Admin) */
        .plane-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            overflow-x: auto;
            padding-bottom: 2rem;
        }

        .plane {
            background: var(--white);
            border: 2px solid #e2e8f0;
            border-radius: 4rem 4rem 2rem 2rem;
            padding: 4rem 3rem 3rem;
            width: 320px; /* Matched Admin Width */
            position: relative;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            height: fit-content;
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

        .row {
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
            cursor: pointer;
            position: relative;
        }

        .seat.first { background-color: var(--seat-first); }
        .seat.business { background-color: var(--seat-business); }
        .seat.economy { background-color: var(--seat-economy); }
        
        .seat.booked { 
            background-color: var(--seat-booked); 
            cursor: not-allowed; 
            opacity: 0.6; 
        }

        .seat.selected {
            background-color: var(--seat-selected) !important;
            transform: scale(1.1);
            box-shadow: 0 0 0 2px var(--primary-dark);
            z-index: 2;
        }

        .seat:hover:not(.booked):not(.selected) {
            transform: translateY(-2px);
            filter: brightness(0.9);
        }

        /* Summary Panel */
        .summary-panel {
            width: 350px;
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            height: fit-content;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 100px;
            border: 1px solid #e2e8f0;
        }

        .summary-header {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .selected-list {
            min-height: 100px;
            margin-bottom: 1.5rem;
        }

        .selected-seat-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .total-section {
            border-top: 1px solid #e2e8f0;
            padding-top: 1.5rem;
            margin-top: auto;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .total-label {
            font-size: 1.1rem;
            color: var(--text-light);
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkout-btn:hover:not(:disabled) {
            background: var(--primary-dark);
        }

        .checkout-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
                align-items: center;
            }
            .summary-panel {
                width: 100%;
                max-width: 500px;
                position: static;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="flight-info">
        <h1><?php echo htmlspecialchars($flight['source']); ?> <span style="color:var(--text-light)">&#9992;</span> <?php echo htmlspecialchars($flight['destination']); ?></h1>
        <p><?php echo date('D, d M Y', strtotime($flight['date'])); ?> • <?php echo date('H:i', strtotime($flight['time'])); ?></p>
    </div>
    <div class="header-actions">
        <a href="passenger_dashboard.php" class="btn btn-outline">Cancel</a>
    </div>
</header>

<div class="main-container">
    <div class="plane-wrapper">
        <div class="plane">
            
            <?php
            // Helper function to render a row with left/right split
            function render_row($seats, $split_idx, $class_name) {
                // Determine left and right groups based on split index
                // Assuming seats are already sorted by letter A, B, C...
                // Only render if we have seats
                if (empty($seats)) return;
                
                // Sort seats by seat number logic (last char)
                usort($seats, function($a, $b) {
                   return strcmp(substr($a['seat_number'], -1), substr($b['seat_number'], -1)); 
                });

                $left_seats = array_slice($seats, 0, $split_idx);
                $right_seats = array_slice($seats, $split_idx);
                
                echo "<div class='row'>";
                
                // Left Group
                echo "<div class='row-group'>";
                foreach ($left_seats as $seat) {
                    $status_class = ($seat['status'] == 'booked') ? 'booked' : '';
                    echo "<div class='seat $class_name $status_class' 
                          data-id='{$seat['id']}' 
                          data-number='{$seat['seat_number']}' 
                          data-price='{$seat['price']}' 
                          data-class='$class_name'>
                          {$seat['seat_number']}
                          </div>";
                }
                echo "</div>"; // End Left
                
                // Right Group
                echo "<div class='row-group'>";
                foreach ($right_seats as $seat) {
                     $status_class = ($seat['status'] == 'booked') ? 'booked' : '';
                     echo "<div class='seat $class_name $status_class' 
                          data-id='{$seat['id']}' 
                          data-number='{$seat['seat_number']}' 
                          data-price='{$seat['price']}' 
                          data-class='$class_name'>
                          {$seat['seat_number']}
                          </div>";
                }
                echo "</div>"; // End Right
                
                echo "</div>"; // End Row
            }
            ?>

            <!-- First Class -->
            <?php if (!empty($seats_by_class['first'])): ?>
                <div class="section-label">First Class</div>
                <?php 
                foreach ($seats_by_class['first'] as $row_num => $row_seats) {
                    render_row($row_seats, 2, 'first'); // 2-2 configuration
                }
                ?>
            <?php endif; ?>

            <!-- Business Class -->
            <?php if (!empty($seats_by_class['business'])): ?>
                <div class="section-label">Business Class</div>
                <?php 
                foreach ($seats_by_class['business'] as $row_num => $row_seats) {
                    // Logic for 2-3 or 3-3? Defaulting to 3-3 split like Admin
                    // If less than 6 seats, it adjusts automatically
                     render_row($row_seats, 3, 'business'); 
                }
                ?>
            <?php endif; ?>

            <!-- Economy Class -->
            <?php if (!empty($seats_by_class['economy'])): ?>
                <div class="section-label">Economy Class</div>
                <?php 
                foreach ($seats_by_class['economy'] as $row_num => $row_seats) {
                    render_row($row_seats, 3, 'economy'); // 3-3 configuration
                }
                ?>
            <?php endif; ?>
            
        </div>
    </div>

    <!-- Right Summary Panel -->
    <div class="summary-panel">
        <div class="summary-header">Your Selection</div>
        
        <div class="legend">
            <div class="legend-item"><div class="legend-dot" style="background:var(--seat-selected)"></div>Selected</div>
            <div class="legend-item"><div class="legend-dot" style="background:var(--seat-booked)"></div>Booked</div>
            <div class="legend-item"><div class="legend-dot" style="background:var(--seat-first)"></div>First</div>
            <div class="legend-item"><div class="legend-dot" style="background:var(--seat-business)"></div>Business</div>
            <div class="legend-item"><div class="legend-dot" style="background:var(--seat-economy)"></div>Economy</div>
        </div>

        <div class="selected-list" id="selected-seats-container">
            <p style="color:var(--text-light); text-align:center; margin-top:2rem;">No seats selected</p>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span class="total-label">Total</span>
                <span class="total-amount">$<span id="total-price-display">0.00</span></span>
            </div>
            
            <form method="post" action="booking.php" id="booking-form">
                <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
                <input type="hidden" name="selected_seats" id="selected_seats_input">
                <button type="submit" class="checkout-btn" id="checkout-btn" disabled>Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<script>
    const seats = document.querySelectorAll('.seat:not(.booked)');
    const selectedContainer = document.getElementById('selected-seats-container');
    const totalPriceDisplay = document.getElementById('total-price-display');
    const checkoutBtn = document.getElementById('checkout-btn');
    const selectedInput = document.getElementById('selected_seats_input');
    
    let selectedSeats = [];

    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            const id = seat.dataset.id;
            const number = seat.dataset.number;
            const price = parseFloat(seat.dataset.price);
            const seatClass = seat.dataset.class;

            if (seat.classList.contains('selected')) {
                // Deselect
                seat.classList.remove('selected');
                selectedSeats = selectedSeats.filter(s => s.id !== id);
            } else {
                // Select
                seat.classList.add('selected');
                selectedSeats.push({ id, number, price, seatClass });
            }
            updateSummary();
        });
    });

    function updateSummary() {
        // Clear list
        selectedContainer.innerHTML = '';
        
        if (selectedSeats.length === 0) {
            selectedContainer.innerHTML = '<p style="color:var(--text-light); text-align:center; margin-top:2rem;">No seats selected</p>';
            checkoutBtn.disabled = true;
            totalPriceDisplay.textContent = '0.00';
            selectedInput.value = '';
            return;
        }

        let total = 0;
        selectedSeats.forEach(s => {
            total += s.price;
            const div = document.createElement('div');
            div.className = 'selected-seat-item';
            div.innerHTML = `
                <span>Seat ${s.number} <span style="font-size:0.8rem; color:var(--text-light)">(${capitalize(s.seatClass)})</span></span>
                <span class="price-tag">$${s.price.toFixed(2)}</span>
            `;
            selectedContainer.appendChild(div);
        });

        totalPriceDisplay.textContent = total.toFixed(2);
        checkoutBtn.disabled = false;
        
        // Update hidden input
        const ids = selectedSeats.map(s => s.id).join(',');
        selectedInput.value = ids;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>

</body>
</html>

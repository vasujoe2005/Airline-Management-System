<?php
session_start();
include 'db.php';
include 'functions.php';
check_passenger();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    $flight_id = $_POST['flight_id'];
    $selected_seats = explode(',', $_POST['selected_seats']);
    $passport = $_POST['passport'];
    $user_id = $_SESSION['user_id'];

    // Generate ticket number
    $ticket_number = generate_ticket_number();

    // Insert booking
    $seat_ids = implode(',', $selected_seats);
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, flight_id, seat_ids, passport_number, ticket_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $flight_id, $seat_ids, $passport, $ticket_number);
    $stmt->execute();

    // Update seats to booked
    foreach ($selected_seats as $seat_id) {
        $conn->query("UPDATE seats SET status='booked' WHERE id=$seat_id");
    }

    header("Location: ticket.php?ticket_number=$ticket_number");
    exit();
}

$flight_id = $_POST['flight_id'];
$selected_seats = explode(',', $_POST['selected_seats']);
$flight = $conn->query("SELECT * FROM flights WHERE id = $flight_id")->fetch_assoc();

// Calculate total price
$total_price = 0;
$seat_details = [];
foreach ($selected_seats as $seat_id) {
    $seat = $conn->query("SELECT * FROM seats WHERE id = $seat_id")->fetch_assoc();
    $total_price += $seat['price'];
    $seat_details[] = $seat;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Booking | Real-Time Airline Ticketing Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --secondary-color: #64748b;
            --bg-color: #f1f5f9;
            --text-main: #1e293b;
            --text-light: #64748b;
            --card-bg: #ffffff;
        }
        
        * {
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-main);
        }
        
        .booking-container {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 500px;
            border: 1px solid #e2e8f0;
        }
        
        h2 {
            text-align: center;
            color: var(--text-main);
            margin-top: 0;
            margin-bottom: 2rem;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .section:last-of-type {
            border-bottom: none;
            margin-bottom: 1rem;
            padding-bottom: 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .label {
            color: var(--text-light);
            font-weight: 500;
        }
        
        .value {
            font-weight: 600;
            color: var(--text-main);
        }
        
        .seat-list {
            list-style: none;
            padding: 0;
            margin: 0.5rem 0;
        }
        
        .seat-item {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .total-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: right;
            margin-top: 1rem;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            margin: 1rem 0;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s;
        }
        
        input[type="text"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.6);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--text-light);
            border: 1px solid #e2e8f0;
            margin-top: 0.5rem;
        }
        
        .btn-secondary:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <h2>Complete Your Booking</h2>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 2rem;">Real-Time Airline Ticketing Platform</p>
        
        <div class="section">
            <div class="info-row">
                <span class="label">Flight</span>
                <span class="value"><?php echo htmlspecialchars($flight['source']); ?> &#8594; <?php echo htmlspecialchars($flight['destination']); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Date</span>
                <span class="value"><?php echo date('D, d M Y', strtotime($flight['date'])); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Time</span>
                <span class="value"><?php echo date('H:i', strtotime($flight['time'])); ?></span>
            </div>
        </div>
        
        <div class="section">
            <div class="label" style="margin-bottom: 0.5rem;">Selected Seats</div>
            <ul class="seat-list">
                <?php foreach ($seat_details as $seat): ?>
                    <li class="seat-item">
                        <span>Seat <?php echo $seat['seat_number']; ?> <span style="color:var(--text-light); font-size:0.85rem">(<?php echo ucfirst($seat['class']); ?>)</span></span>
                        <span>$<?php echo number_format($seat['price'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total-price">
                Total: $<?php echo number_format($total_price, 2); ?>
            </div>
        </div>
        
        <form method="post" action="">
            <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
            <input type="hidden" name="selected_seats" value="<?php echo implode(',', $selected_seats); ?>">
            
            <div class="section">
                <label class="label" for="passport">Passport Number</label>
                <input type="text" id="passport" name="passport" placeholder="Enter passport number" required>
            </div>
            
            <button type="submit" name="confirm" class="btn btn-primary">Pay & Confirm</button>
            <button type="button" class="btn btn-secondary" onclick="history.back()">Go Back</button>
        </form>
    </div>
</body>
</html>

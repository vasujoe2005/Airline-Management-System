<?php
session_start();
include 'db.php';
include 'functions.php';
check_passenger();

$ticket_number = $_GET['ticket_number'];
$booking = $conn->query("SELECT b.*, f.source, f.destination, f.date, f.time, u.username, u.email FROM bookings b JOIN flights f ON b.flight_id = f.id JOIN users u ON b.user_id = u.id WHERE b.ticket_number = '$ticket_number' AND b.user_id = {$_SESSION['user_id']}")->fetch_assoc();

if (!$booking) {
    die("Ticket not found.");
}

$seat_ids = explode(',', $booking['seat_ids']);
$seats = [];
foreach ($seat_ids as $id) {
    $seat = $conn->query("SELECT seat_number, class FROM seats WHERE id = $id")->fetch_assoc();
    $seats[] = $seat['seat_number'] . ' (' . ucfirst($seat['class']) . ')';
}

if (isset($_GET['download'])) {
    require('fpdf/fpdf.php');

    class PDF extends FPDF {
        function Header() {
            // Airline header
            $this->SetFont('Arial', 'B', 20);
            $this->SetTextColor(102, 126, 234);
            $this->Cell(0, 15, 'AIRLINE TICKETING SYSTEM', 0, 1, 'C');

            // Decorative line
            $this->SetDrawColor(102, 126, 234);
            $this->SetLineWidth(1);
            $this->Line(10, 25, 200, 25);

            $this->Ln(10);
        }

        function Footer() {
            $this->SetY(-30);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(128, 128, 128);
            $this->Cell(0, 10, 'This is an electronic ticket. Please present valid ID at check-in.', 0, 0, 'C');
            $this->Ln(5);
            $this->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 0, 'C');
        }

        function RotatedText($x, $y, $txt, $angle) {
            // Text rotated around its origin
            $this->Rotate($angle, $x, $y);
            $this->Text($x, $y, $txt);
            $this->Rotate(0);
        }

        var $angle = 0;

        function Rotate($angle, $x = -1, $y = -1) {
            if ($x == -1)
                $x = $this->x;
            if ($y == -1)
                $y = $this->y;
            if ($this->angle != 0)
                $this->_out('Q');
            $this->angle = $angle;
            if ($angle != 0) {
                $angle *= M_PI / 180;
                $c = cos($angle);
                $s = sin($angle);
                $cx = $x * $this->k;
                $cy = ($this->h - $y) * $this->k;
                $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
            }
        }

        function _endpage() {
            if ($this->angle != 0) {
                $this->angle = 0;
                $this->_out('Q');
            }
            parent::_endpage();
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 35);

    // Ticket header
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 12, 'PASSENGER TICKET', 0, 1, 'C');
    $pdf->Ln(5);

    // Ticket number
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 10, 'Ticket Number: ' . $booking['ticket_number'], 1, 1, 'C', true);
    $pdf->Ln(5);

    if ($booking['status'] == 'cancelled') {
        $pdf->SetFont('Arial', 'B', 50);
        $pdf->SetTextColor(255, 192, 203);
        $pdf->RotatedText(35, 190, 'CANCELLED', 45);
        $pdf->SetTextColor(0, 0, 0);
    }

    // Passenger information section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->Cell(0, 10, 'PASSENGER INFORMATION', 0, 1, 'L');
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(3);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    // Create a table for passenger info
    $pdf->SetFillColor(248, 249, 250);
    $pdf->Cell(40, 8, 'Name:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $booking['username'], 1, 1, 'L');

    $pdf->Cell(40, 8, 'Email:', 1, 0, 'L', false);
    $pdf->Cell(0, 8, $booking['email'], 1, 1, 'L');

    $pdf->Cell(40, 8, 'Passport:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $booking['passport_number'], 1, 1, 'L');

    $pdf->Ln(5);

    // Flight information section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->Cell(0, 10, 'FLIGHT INFORMATION', 0, 1, 'L');
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(3);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetFillColor(248, 249, 250);
    $pdf->Cell(40, 8, 'From:', 1, 0, 'L', true);
    $pdf->Cell(50, 8, $booking['source'], 1, 0, 'L');
    $pdf->Cell(30, 8, 'To:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $booking['destination'], 1, 1, 'L');

    $pdf->Cell(40, 8, 'Date:', 1, 0, 'L', false);
    $pdf->Cell(50, 8, $booking['date'], 1, 0, 'L');
    $pdf->Cell(30, 8, 'Time:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $booking['time'], 1, 1, 'L');

    $pdf->Ln(5);

    // Seat information section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->Cell(0, 10, 'SEAT INFORMATION', 0, 1, 'L');
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(3);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetFillColor(248, 249, 250);
    $pdf->Cell(40, 8, 'Seats:', 1, 0, 'L', true);
    $pdf->MultiCell(0, 8, implode(', ', $seats), 1, 'L');

    $pdf->Ln(5);

    // Important notices
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(220, 53, 69);
    $pdf->Cell(0, 10, 'IMPORTANT TRAVEL INFORMATION', 0, 1, 'L');
    $pdf->SetDrawColor(220, 53, 69);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(3);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 5, "• Please arrive at the airport at least 2 hours before domestic flights and 3 hours before international flights.\n• Valid government-issued photo ID is required for check-in.\n• Baggage allowance: 20kg checked baggage + 7kg carry-on.\n• This ticket is non-transferable and non-refundable.", 0, 'L');

    $pdf->Ln(10);

    // Signature line
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 10, 'Passenger Signature: _______________________________', 0, 1, 'L');

    $pdf->Output('D', 'ticket_' . $ticket_number . '.pdf');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket | Real-Time Airline Ticketing Platform</title>
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
            --seat-color: #3b82f6;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .ticket-container {
            background: var(--white);
            padding: 0;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 700px;
            overflow: hidden;
            position: relative;
            margin: 2rem;
            border: 1px solid #e2e8f0;
        }

        .ticket-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .ticket-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
            letter-spacing: -0.025em;
        }

        .ticket-header .airline {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .ticket-body {
            padding: 2.5rem;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-group h3 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .info-group p {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-main);
        }
        
        .info-group .highlight {
            color: var(--primary);
            font-weight: 700;
        }
        
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 1.5rem 0;
            border: none;
        }
        
        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .airport-code {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-main);
        }
        
        .plane-icon {
            color: var(--text-light);
            font-size: 1.5rem;
        }

        .seats-section {
            background-color: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
        }
        
        .seats-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .seat-badge {
            background-color: white;
            border: 1px solid #e2e8f0;
            color: var(--text-main);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .ticket-footer {
            background-color: #f8fafc;
            padding: 1.5rem 2.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        button {
            padding: 0.75rem 1.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .back-link a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link a:hover {
            color: var(--primary);
        }

        .cancelled-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 8rem;
            font-weight: 800;
            color: rgba(239, 68, 68, 0.15);
            pointer-events: none;
            z-index: 100;
            text-transform: uppercase;
            width: 100%;
            text-align: center;
        }

        .status-header-cancelled {
            background: #ef4444 !important;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <?php if ($booking['status'] == 'cancelled'): ?>
            <div class="cancelled-watermark">CANCELLED</div>
        <?php endif; ?>
        <div class="ticket-header <?php echo ($booking['status'] == 'cancelled') ? 'status-header-cancelled' : ''; ?>">
            <h2>Boarding Pass</h2>
            <div class="airline">Real-Time Airline Ticketing Platform</div>
        </div>
        
        <div class="ticket-body">
            <div class="flight-route">
                <div class="airport-code"><?php echo strtoupper(substr($booking['source'], 0, 3)); ?></div>
                <div class="plane-icon">✈</div>
                <div class="airport-code"><?php echo strtoupper(substr($booking['destination'], 0, 3)); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="info-group">
                    <h3>Passenger</h3>
                    <p><?php echo $booking['username']; ?></p>
                </div>
                <div class="info-group">
                    <h3>Ticket Number</h3>
                    <p class="highlight"><?php echo $booking['ticket_number']; ?></p>
                </div>
            </div>
            
            <div class="detail-row">
                 <div class="info-group">
                    <h3>From</h3>
                    <p><?php echo $booking['source']; ?></p>
                </div>
                <div class="info-group">
                    <h3>To</h3>
                    <p><?php echo $booking['destination']; ?></p>
                </div>
            </div>
            
            <div class="detail-row">
                 <div class="info-group">
                    <h3>Date</h3>
                    <p><?php echo date('M d, Y', strtotime($booking['date'])); ?></p>
                </div>
                <div class="info-group">
                    <h3>Time</h3>
                    <p><?php echo date('H:i', strtotime($booking['time'])); ?></p>
                </div>
            </div>
            
            <hr class="divider">
            
            <div class="seats-section">
                <div class="info-group">
                    <h3>Assigned Seats</h3>
                    <div class="seats-list">
                        <?php foreach ($seats as $seat): ?>
                            <span class="seat-badge"><?php echo $seat; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ticket-footer">
            <div class="back-link">
                <a href="history.php">← Back to History</a>
            </div>
            <?php if ($booking['status'] != 'cancelled'): ?>
                <button onclick="window.open('ticket.php?ticket_number=<?php echo $ticket_number; ?>&download=1', '_blank')">Download PDF</button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

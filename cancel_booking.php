<?php
session_start();
include 'db.php';
include 'functions.php';
check_passenger();

if (isset($_GET['ticket_number'])) {
    $ticket_number = $conn->real_escape_string($_GET['ticket_number']);
    $user_id = $_SESSION['user_id'];

    // Fetch booking and flight details
    $query = "SELECT b.*, f.date, f.time 
              FROM bookings b 
              JOIN flights f ON b.flight_id = f.id 
              WHERE b.ticket_number = '$ticket_number' AND b.user_id = $user_id";
    
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        
        // Check if status is already cancelled
        if ($booking['status'] == 'cancelled') {
            $_SESSION['error'] = "Ticket is already cancelled.";
            header("Location: history.php");
            exit;
        }

        // Check if it's 24 hours before flight (Dynamic check based on latest flight timing)
        $can_cancel = is_cancellation_allowed($booking['date'], $booking['time']);

        if ($can_cancel['allowed']) {
            // Can cancel
            $conn->begin_transaction();

            try {
                // Update booking status
                $conn->query("UPDATE bookings SET status = 'cancelled' WHERE id = " . $booking['id']);

                // Mark seats as available
                $seat_ids = explode(',', $booking['seat_ids']);
                foreach ($seat_ids as $id) {
                    $conn->query("UPDATE seats SET status = 'available' WHERE id = $id");
                }

                $conn->commit();
                $_SESSION['success'] = "Ticket cancelled successfully.";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = "Failed to cancel ticket. Please try again.";
            }
        } else {
            $_SESSION['error'] = $can_cancel['reason'];
        }
    } else {
        $_SESSION['error'] = "Invalid ticket or access denied.";
    }
} else {
    $_SESSION['error'] = "No ticket number provided.";
}

header("Location: history.php");
exit;
?>

<?php
date_default_timezone_set('Asia/Kolkata');

function generate_ticket_number() {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 7));
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function check_admin() {
    check_login();
    if ($_SESSION['role'] != 'admin') {
        header("Location: passenger_dashboard.php");
        exit();
    }
}

function check_passenger() {
    check_login();
    if ($_SESSION['role'] != 'passenger') {
        header("Location: admin_dashboard.php");
        exit();
    }
}

/**
 * Returns an array with 'allowed' (bool) and 'reason' (string)
 */
function is_cancellation_allowed($date, $time) {
    $flight_datetime = strtotime($date . ' ' . $time);
    $current_datetime = time();
    $diff = $flight_datetime - $current_datetime;

    if ($diff < 0) {
        return ['allowed' => false, 'reason' => 'Flight has already departed'];
    } elseif ($diff < 24 * 3600) {
        return ['allowed' => false, 'reason' => 'Cancellation window closed (less than 24h before flight)'];
    }
    return ['allowed' => true, 'reason' => ''];
}
?>

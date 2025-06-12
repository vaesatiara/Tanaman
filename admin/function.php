<?php
/**
 * Helper functions for the dashboard
 */

/**
 * Format currency in Indonesian Rupiah format
 * 
 * @param float $amount The amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return "Rp " . number_format($amount, 0, ',', '.');
}

/**
 * Format number with thousand separators
 * 
 * @param float $number The number to format
 * @return string Formatted number string
 */
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

/**
 * Get status class for order status
 * 
 * @param string $status The order status
 * @return string CSS class name
 */
function getStatusClass($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'selesai':
            return 'completed';
        case 'proses':
            return 'processing';
        case 'dikirim':
            return 'shipped';
        case 'pending':
            return 'pending';
        default:
            return '';
    }
}

/**
 * Get month name in Indonesian
 * 
 * @param int $month Month number (1-12)
 * @return string Month name in Indonesian
 */
function getIndonesianMonth($month) {
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    
    return $months[$month] ?? '';
}

/**
 * Format date to Indonesian format
 * 
 * @param string $date Date string in any format
 * @return string Formatted date
 */
function formatIndonesianDate($date) {
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = getIndonesianMonth(date('n', $timestamp));
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Sanitize input data
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

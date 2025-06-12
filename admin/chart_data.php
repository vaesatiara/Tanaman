<?php
// This file provides chart data in JSON format
// It can be used to fetch real-time data for charts

// Set headers for JSON response
header('Content-Type: application/json');

// In a real application, you would connect to a database here
// $conn = mysqli_connect("localhost", "username", "password", "database");

// Sample data (in a real app, this would come from database)
$data = [
    'sales' => [
        'labels' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        'data' => [12, 19, 15, 25, 22, 30]
    ],
    'categories' => [
        'labels' => ["Tanaman Hias Daun", "Tanaman Hias Bunga", "Pot & Aksesoris"],
        'data' => [60, 30, 10]
    ]
];

// Return data as JSON
echo json_encode($data);
?>

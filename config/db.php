<?php
$host = 'localhost';
$user = 'risunper_travel_abraham';
$password = 'T;ZTF2s7jp2H';
$database = 'risunper_travel_peru';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}
$conn->set_charset('utf8');
?>
<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'blackwhite_detailing';

$connection = mysqli_connect($host, $user, $password, $database);

if (!$connection) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($connection, 'utf8');

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
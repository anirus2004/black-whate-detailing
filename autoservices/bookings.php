<?php
require_once 'config.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Для записи на услугу необходимо войти в систему';
    header('Location: login.php');
    exit();
}

// Проверяем CSRF токен
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Ошибка безопасности. Попробуйте снова.';
    header('Location: index.php');
    exit();
}

// Обработка формы записи
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_service'])) {
    $user_id = $_SESSION['user_id'];
    $service_id = intval($_POST['service_id']);
    $booking_date = mysqli_real_escape_string($connection, $_POST['booking_date']);
    $booking_time = mysqli_real_escape_string($connection, $_POST['booking_time']);
    $car_model = mysqli_real_escape_string($connection, $_POST['car_model']);
    $car_year = isset($_POST['car_year']) ? intval($_POST['car_year']) : null;
    $phone = mysqli_real_escape_string($connection, $_POST['phone']);
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($connection, $_POST['notes']) : '';
    
    // Проверка даты (нельзя записываться на прошедшие даты)
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $_SESSION['error'] = 'Нельзя записываться на прошедшую дату';
        header('Location: index.php');
        exit();
    }
    
    // Проверка времени работы (9:00 - 21:00)
    $time = strtotime($booking_time);
    $hour = date('H', $time);
    if ($hour < 9 || $hour >= 21) {
        $_SESSION['error'] = 'Время работы: с 9:00 до 21:00';
        header('Location: index.php');
        exit();
    }
    
    // Проверяем, не занято ли это время
    $check_query = "SELECT id FROM bookings 
                    WHERE booking_date = '$booking_date' 
                    AND booking_time = '$booking_time'
                    AND status IN ('pending', 'confirmed')";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = 'Выбранное время уже занято. Пожалуйста, выберите другое время.';
        header('Location: index.php');
        exit();
    }
    
    // Вставляем запись
    if ($car_year) {
        $query = "INSERT INTO bookings (user_id, service_id, booking_date, booking_time, 
                  car_model, car_year, phone, notes, status) 
                  VALUES ('$user_id', '$service_id', '$booking_date', '$booking_time', 
                  '$car_model', '$car_year', '$phone', '$notes', 'pending')";
    } else {
        $query = "INSERT INTO bookings (user_id, service_id, booking_date, booking_time, 
                  car_model, phone, notes, status) 
                  VALUES ('$user_id', '$service_id', '$booking_date', '$booking_time', 
                  '$car_model', '$phone', '$notes', 'pending')";
    }
    
    if (mysqli_query($connection, $query)) {
        $_SESSION['success'] = 'Вы успешно записались на услугу! Мы свяжемся с вами для подтверждения.';
    } else {
        $_SESSION['error'] = 'Ошибка при записи. Пожалуйста, попробуйте позже.';
    }
    
    header('Location: index.php');
    exit();
}

// Если не POST запрос, перенаправляем на главную
header('Location: index.php');
exit();
?>
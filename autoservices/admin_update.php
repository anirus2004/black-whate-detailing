<?php
require_once 'config.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: index.php');
    exit();
}

// Обработка изменения статуса админа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    // Запрещаем изменять свой собственный статус
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'Вы не можете изменить свой собственный статус!';
        header('Location: admin.php');
        exit();
    }
    
    $new_status = ($action == 'make_admin') ? 1 : 0;
    $query = "UPDATE users SET is_admin = $new_status WHERE id = $user_id";
    
    if (mysqli_query($connection, $query)) {
        $status_text = $new_status ? 'администратором' : 'обычным пользователем';
        $_SESSION['message'] = "Пользователь успешно сделан $status_text";
    } else {
        $_SESSION['error'] = 'Ошибка при изменении статуса пользователя';
    }
}

header('Location: admin.php');
exit();
?>
<?php
require_once 'config.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Для доступа к личному кабинету необходимо войти в систему';
    header('Location: login.php');
    exit();
}

// Получаем данные пользователя
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($connection, $query);
$user = mysqli_fetch_assoc($result);

// Получаем записи пользователя
$bookings_query = "SELECT b.*, s.title as service_name 
                  FROM bookings b 
                  LEFT JOIN services s ON b.service_id = s.id 
                  WHERE b.user_id = $user_id 
                  ORDER BY b.booking_date DESC, b.booking_time DESC";
$bookings_result = mysqli_query($connection, $bookings_query);

// Обработка отмены записи
if (isset($_GET['cancel_booking'])) {
    $booking_id = intval($_GET['cancel_booking']);
    
    // Проверяем, что запись принадлежит пользователю
    $check_query = "SELECT * FROM bookings WHERE id = $booking_id AND user_id = $user_id";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id";
        if (mysqli_query($connection, $update_query)) {
            $_SESSION['success'] = 'Запись успешно отменена';
        } else {
            $_SESSION['error'] = 'Ошибка при отмене записи';
        }
    } else {
        $_SESSION['error'] = 'Запись не найдена';
    }
    
    header('Location: profile.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Blackwhite&Detailing</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-section {
            padding: 100px 0;
            background-color: #111;
            min-height: 100vh;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .profile-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .profile-card {
            background-color: #222;
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-info p {
            margin-bottom: 10px;
            color: #ccc;
        }
        
        .user-info strong {
            color: #fff;
        }
        
        .booking-card {
            margin-bottom: 20px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        .booking-card.pending {
            border-left-color: #ffaa44;
        }
        
        .booking-card.confirmed {
            border-left-color: #44ff44;
        }
        
        .booking-card.completed {
            border-left-color: #44aaff;
        }
        
        .booking-card.cancelled {
            border-left-color: #ff4444;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .booking-header h3 {
            margin: 0;
            color: #fff;
        }
        
        .booking-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: rgba(255, 170, 68, 0.1);
            color: #ffaa44;
        }
        
        .status-confirmed {
            background-color: rgba(68, 255, 68, 0.1);
            color: #44ff44;
        }
        
        .status-completed {
            background-color: rgba(68, 170, 255, 0.1);
            color: #44aaff;
        }
        
        .status-cancelled {
            background-color: rgba(255, 68, 68, 0.1);
            color: #ff4444;
        }
        
        .booking-details p {
            margin-bottom: 8px;
            color: #ccc;
            font-size: 0.95rem;
        }
        
        .booking-details i {
            width: 20px;
            color: #fff;
        }
        
        .btn-cancel-booking {
            padding: 8px 15px;
            background-color: transparent;
            border: 1px solid #ff4444;
            color: #ff4444;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-cancel-booking:hover {
            background-color: #ff4444;
            color: #fff;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #444;
        }
        
        .no-data p {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .btn-add-service {
            padding: 10px 25px;
            background-color: transparent;
            border: 2px solid #fff;
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-add-service:hover {
            background-color: #fff;
            color: #000;
        }
        
        .alert {
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .alert-success {
            background-color: rgba(68, 255, 68, 0.1);
            color: #44ff44;
            border: 1px solid rgba(68, 255, 68, 0.3);
        }
        
        .alert-error {
            background-color: rgba(255, 68, 68, 0.1);
            color: #ff4444;
            border: 1px solid rgba(255, 68, 68, 0.3);
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <span class="logo-black">Black</span><span class="logo-white">White</span>
                <span class="logo-amp">&</span>
                <span class="logo-detailing">Detailing</span>
            </a>
            
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Главная</a>
                <a href="index.php#services" class="nav-link">Услуги</a>
                <a href="profile.php" class="nav-link active">Личный кабинет</a>
                
                <div class="auth-buttons">
                    <span class="welcome-text">Привет, <?php echo htmlspecialchars($user['username']); ?>!</span>
                    <?php if ($user['is_admin'] == 1): ?>
                        <a href="admin.php" class="btn-admin">
                            <i class="fas fa-cog"></i> Админка
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </a>
                </div>
            </div>
            
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Личный кабинет -->
    <section class="profile-section">
        <div class="container">
            <div class="profile-header">
                <h1 class="section-title">Личный кабинет</h1>
                <p class="section-subtitle">Управление вашими записями</p>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="profile-cards">
                <!-- Информация о пользователе -->
                <div class="profile-card">
                    <h2 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">
                        <i class="fas fa-user"></i> Мои данные
                    </h2>
                    <div class="user-info">
                        <p><strong>Имя пользователя:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Дата регистрации:</strong> <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></p>
                        <p><strong>Статус:</strong> 
                            <?php if ($user['is_admin'] == 1): ?>
                                <span style="color: #44ff44;">Администратор</span>
                            <?php else: ?>
                                <span style="color: #ccc;">Пользователь</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Мои записи -->
                <div class="profile-card">
                    <h2 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">
                        <i class="fas fa-calendar-alt"></i> Мои записи
                    </h2>
                    
                    <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                        <div class="bookings-list">
                            <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                                <div class="booking-card <?php echo $booking['status']; ?>">
                                    <div class="booking-header">
                                        <h3><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                                        <span class="booking-status status-<?php echo $booking['status']; ?>">
                                            <?php 
                                            $statuses = [
                                                'pending' => 'Ожидает',
                                                'confirmed' => 'Подтверждена',
                                                'completed' => 'Выполнена',
                                                'cancelled' => 'Отменена'
                                            ];
                                            echo $statuses[$booking['status']];
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="booking-details">
                                        <p><i class="fas fa-calendar-day"></i> 
                                            <strong>Дата:</strong> <?php echo date('d.m.Y', strtotime($booking['booking_date'])); ?>
                                        </p>
                                        <p><i class="fas fa-clock"></i> 
                                            <strong>Время:</strong> <?php echo $booking['booking_time']; ?>
                                        </p>
                                        <p><i class="fas fa-car"></i> 
                                            <strong>Автомобиль:</strong> <?php echo htmlspecialchars($booking['car_model']); ?>
                                            <?php if ($booking['car_year']): ?>
                                                (<?php echo $booking['car_year']; ?> г.)
                                            <?php endif; ?>
                                        </p>
                                        <p><i class="fas fa-phone"></i> 
                                            <strong>Телефон:</strong> <?php echo htmlspecialchars($booking['phone']); ?>
                                        </p>
                                        <?php if ($booking['notes']): ?>
                                            <p><i class="fas fa-sticky-note"></i> 
                                                <strong>Примечания:</strong> <?php echo htmlspecialchars($booking['notes']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p><i class="fas fa-calendar-plus"></i> 
                                            <strong>Запись создана:</strong> <?php echo date('d.m.Y H:i', strtotime($booking['created_at'])); ?>
                                        </p>
                                    </div>
                                    
                                    <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                        <a href="?cancel_booking=<?php echo $booking['id']; ?>" 
                                           class="btn-cancel-booking"
                                           onclick="return confirm('Вы уверены, что хотите отменить запись?')">
                                            <i class="fas fa-times"></i> Отменить запись
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-times"></i>
                            <p>У вас нет активных записей</p>
                            <a href="index.php#services" class="btn-add-service">
                                <i class="fas fa-calendar-plus"></i> Записаться на услугу
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Футер -->
<footer id="contact" class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-info">
                <div class="logo footer-logo">
                    <span class="logo-black">Black</span><span class="logo-white">White</span>
                    <span class="logo-amp">&</span>
                    <span class="logo-detailing">Detailing</span>
                </div>
                <p class="footer-text">
                    Премиальный детейлинг в Москве. <br>
                    Вернем вашему автомобилю первозданный вид.
                </p>
                <div class="social-links">
                    <a href="https://vk.com/blackwhite_detailing" 
                       target="_blank" 
                       class="social-link" 
                       title="Мы ВКонтакте">
                        <i class="fab fa-vk"></i>
                    </a>
                    <a href="https://t.me/blackwhite_detailing" 
                       target="_blank" 
                       class="social-link" 
                       title="Наш Telegram">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <a href="https://wa.me/79991234567" 
                       target="_blank" 
                       class="social-link" 
                       title="Написать в WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.instagram.com/blackwhite_detailing" 
                       target="_blank" 
                       class="social-link" 
                       title="Мы в Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/@blackwhite_detailing" 
                       target="_blank" 
                       class="social-link" 
                       title="Наш YouTube канал">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-contact">
                <h3 class="footer-title">Контакты</h3>
                <p><i class="fas fa-map-marker-alt"></i> Москва, ул. Детейлинговая, 1</p>
                <p><i class="fas fa-phone"></i> <a href="tel:+79991234567" class="footer-link">+7 (999) 123-45-67</a></p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:info@blackwhite-detailing.ru" class="footer-link">info@blackwhite-detailing.ru</a></p>
                <p><i class="fas fa-clock"></i> Ежедневно 9:00 - 21:00</p>
                <p><i class="fas fa-car"></i> Предварительная запись обязательна</p>
            </div>
            
            <div class="footer-links">
                <h3 class="footer-title">Быстрые ссылки</h3>
                <a href="index.php#home" class="footer-link">Главная</a>
                <a href="index.php#services" class="footer-link">Услуги</a>
                <a href="index.php#about" class="footer-link">О нас</a>
                <a href="index.php#contact" class="footer-link">Контакты</a>
                <a href="login.php" class="footer-link">Вход</a>
                <a href="register.php" class="footer-link">Регистрация</a>
                <?php if ($is_logged_in): ?>
                    <a href="profile.php" class="footer-link">Личный кабинет</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-left">
                <p>&copy; 2024 Blackwhite&Detailing. Все права защищены.</p>
                <p>Сделано с <i class="fas fa-heart"></i> для автомобилей</p>
            </div>
            <div class="footer-bottom-right">
                <a href="privacy.php" class="footer-link">Политика конфиденциальности</a>
                <a href="terms.php" class="footer-link">Условия использования</a>
            </div>
        </div>
    </div>
</footer>
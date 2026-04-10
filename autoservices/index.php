<?php
require_once 'config.php';

// Проверка авторизации
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Получение услуг из базы
$query = "SELECT * FROM services ORDER BY id ASC";
$result = mysqli_query($connection, $query);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blackwhite&Detailing</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Стили для модальных окон */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background-color: #222;
            border-radius: 15px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 25px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #fff;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: #ccc;
            font-size: 1.8rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: #fff;
        }

        .modal-body {
            padding: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #fff;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #fff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-submit {
            padding: 12px 30px;
            background-color: transparent;
            border: 2px solid #44ff44;
            color: #44ff44;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-submit:hover {
            background-color: #44ff44;
            color: #000;
        }

        .btn-cancel {
            padding: 12px 30px;
            background-color: transparent;
            border: 2px solid #ff4444;
            color: #ff4444;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background-color: #ff4444;
            color: #fff;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .selected-service {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            color: #fff;
            font-weight: 500;
            margin-bottom: 20px;
            border-left: 3px solid #fff;
        }

        .alert {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            z-index: 3000;
            max-width: 400px;
            animation: slideIn 0.3s ease, fadeOut 0.3s ease 4.7s forwards;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .modal-content {
                max-width: 95%;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .alert {
                left: 20px;
                right: 20px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <!-- Лоадер -->
    <div class="loader">
        <div class="loader-circle"></div>
    </div>

    <!-- Навигация -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <span class="logo-black">Black</span><span class="logo-white">White</span>
                <span class="logo-amp">&</span>
                <span class="logo-detailing">Detailing</span>
            </a>
            
            <div class="nav-menu">
                <a href="#home" class="nav-link">Главная</a>
                <a href="#services" class="nav-link">Услуги</a>
                <a href="#about" class="nav-link">О нас</a>
                <a href="#contact" class="nav-link">Контакты</a>
                
                <div class="auth-buttons">
                    <?php if ($is_logged_in): ?>
                        <span class="welcome-text">Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        <a href="profile.php" class="btn-admin">
                            <i class="fas fa-user"></i> Личный кабинет
                        </a>
                        <?php if ($is_admin): ?>
                            <a href="admin.php" class="btn-admin">
                                <i class="fas fa-cog"></i> Админка
                            </a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Выйти
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i> Вход
                        </a>
                        <a href="register.php" class="btn-register">
                            <i class="fas fa-user-plus"></i> Регистрация
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Герой-секция -->
<section id="home" class="hero" style="background-image: url('images/porsh.jpg');">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-title animate-fade-up">
            Искусство <span class="highlight">детейлинга</span>
        </h1>
        <p class="hero-subtitle animate-fade-up-delay">
            Премиальный уход за автомобилем в чёрно-белом стиле
        </p>
        <a href="#services" class="btn-hero animate-fade-up-delay-2">
            Наши услуги <i class="fas fa-arrow-down"></i>
        </a>
    </div>
    <div class="scroll-indicator">
        <div class="mouse">
            <div class="wheel"></div>
        </div>
    </div>
</section>

  <!-- Секция услуг -->
<section id="services" class="services">
    <div class="container">
        <h2 class="section-title animate-on-scroll">Наши услуги</h2>
        <p class="section-subtitle animate-on-scroll">Премиальные решения для вашего автомобиля</p>
        
        <div class="services-grid">
            <?php
            $query = "SELECT * FROM services ORDER BY id ASC";
            $result = mysqli_query($connection, $query);
            
            // Маппинг ID услуг к названиям файлов изображений
            $service_images = [
                1 => 'polishing.jpg',    // Полировка кузова
                2 => 'cleaning.jpg',     // Химчистка салона
                3 => 'ceramic.jpg',      // Покрытие керамикой
                4 => 'film.jpg',         // Оклейка плёнкой
                5 => 'detailing.jpg',    // Детейлинг химчистка
                6 => 'protection.jpg'    // Защитное покрытие
            ];
            
            while ($service = mysqli_fetch_assoc($result)):
                $service_id = $service['id'];
                $image_file = isset($service_images[$service_id]) ? $service_images[$service_id] : 'default.jpg';
                $image_path = 'images/' . $image_file;
            ?>
            <div class="service-card animate-on-scroll">
                <div class="service-image">
                    <?php if (file_exists($image_path)): ?>
                        <img src="<?php echo $image_path; ?>" 
                             alt="<?php echo htmlspecialchars($service['title']); ?>"
                             class="service-img"
                             onerror="this.src='https: //images.unsplash.com/photo-1563720223485-8d6d5c5bf60c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'; this.onerror=null;">
                    <?php else: ?>
                        <!-- Если фото нет, показываем заглушку с названием услуги -->
                        <div class="image-placeholder" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%;">
                            <i class="fas fa-car" style="font-size: 3rem; margin-bottom: 10px;"></i>
                            <span style="font-size: 0.9rem; text-align: center; padding: 0 10px;">
                                <?php echo htmlspecialchars($service['title']); ?>
                            </span>
                            <small style="color: #888; font-size: 0.8rem; margin-top: 5px;">
                                (добавьте фото в папку images/)
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="service-content">
                    <h3 class="service-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                    <div class="service-footer">
                        <span class="service-price">от 5 000 ₽</span>
                        <button class="btn-service" 
                                data-id="<?php echo $service['id']; ?>"
                                data-title="<?php echo htmlspecialchars($service['title']); ?>">
                            <i class="fas fa-calendar-check"></i> Записаться
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

    <!-- Секция о нас -->
<section id="about" class="about">
    <div class="container">
        <div class="about-content">
            <!-- Картинка слева -->
            <div class="about-image animate-on-scroll">
                <div class="image-frame">
                    <?php if (file_exists('images/about.jpg')): ?>
                        <img src="images/about.jpg" 
                             alt="Наш детейлинг центр" 
                             class="about-img">
                    <?php else: ?>
                        <img src="https://images.pexels.com/photos/3802508/pexels-photo-3802508.jpeg?auto=compress&cs=tinysrgb&w=1200" 
                             alt="Наш детейлинг центр" 
                             class="about-img">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Текст справа -->
            <div class="about-text animate-on-scroll">
                <h2 class="section-title">О компании Blackwhite&Detailing</h2>
                
                <div class="about-description">
                    <p class="about-paragraph">
                        <strong>Blackwhite&Detailing</strong> — это премиальная студия детейлинга, 
                        где каждая деталь вашего автомобиля получает безупречный уход. 
                        Мы сочетаем искусство, передовые технологии и настоящую страсть 
                        к автомобилям, чтобы вернуть им первозданный блеск и защитить 
                        от воздействия окружающей среды.
                    </p>
                    
                    <p class="about-paragraph">
                        Наша команда состоит из сертифицированных специалистов с многолетним 
                        опытом работы в премиальном сегменте детейлинга. Мы используем только 
                        профессиональные материалы и оборудование ведущих мировых брендов.
                    </p>
                </div>
                
                <div class="about-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="feature-text">
                            <h4>5+ лет опыта</h4>
                            <p>Более 500 довольных клиентов</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Работаем 7 дней в неделю</h4>
                            <p>С 9:00 до 21:00 без выходных</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Премиальные материалы</h4>
                            <p>Koch Chemie, Gyeon, CarPro</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Гарантия качества</h4>
                            <p>На все виды работ до 3 лет</p>
                        </div>
                    </div>
                </div>
                
                <div class="about-quote">
                    <i class="fas fa-quote-left"></i>
                    <p>Мы не просто моем машины — мы создаем искусство, 
                       в котором каждая деталь имеет значение. Ваш автомобиль 
                       заслуживает лучшего ухода!</p>
                    <div class="quote-author">
                        <strong>Александр Петров</strong>
                        <span>Основатель студии Blackwhite&Detailing</span>
                    </div>
                </div>
                
                <div class="stats">
                    <div class="stat">
                        <span class="stat-number" data-count="500">0</span>
                        <span class="stat-label">Автомобилей</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" data-count="6">0</span>
                        <span class="stat-label">Услуг</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" data-count="100">0</span>
                        <span class="stat-label">Довольных клиентов</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" data-count="5">0</span>
                        <span class="stat-label">Лет опыта</span>
                    </div>
                </div>
                
                <div class="about-buttons">
                    <a href="#services" class="btn-about">
                        <i class="fas fa-car"></i> Наши услуги
                    </a>
                    <a href="#contact" class="btn-about-outline">
                        <i class="fas fa-phone"></i> Связаться с нами
                    </a>
                </div>
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

    <!-- Модальное окно записи -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Запись на услугу</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="bookingForm" method="POST" action="bookings.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="book_service" value="1">
                    <input type="hidden" id="modalServiceId" name="service_id" value="">
                    
                    <div class="form-group">
                        <label class="form-label">Выбранная услуга</label>
                        <div id="modalServiceName" class="selected-service"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Дата записи *</label>
                            <input type="date" name="booking_date" id="bookingDate" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Время *</label>
                            <select name="booking_time" id="bookingTime" class="form-input" required>
                                <option value="">Выберите время</option>
                                <option value="09:00">09:00</option>
                                <option value="09:30">09:30</option>
                                <option value="10:00">10:00</option>
                                <option value="10:30">10:30</option>
                                <option value="11:00">11:00</option>
                                <option value="11:30">11:30</option>
                                <option value="12:00">12:00</option>
                                <option value="12:30">12:30</option>
                                <option value="13:00">13:00</option>
                                <option value="13:30">13:30</option>
                                <option value="14:00">14:00</option>
                                <option value="14:30">14:30</option>
                                <option value="15:00">15:00</option>
                                <option value="15:30">15:30</option>
                                <option value="16:00">16:00</option>
                                <option value="16:30">16:30</option>
                                <option value="17:00">17:00</option>
                                <option value="17:30">17:30</option>
                                <option value="18:00">18:00</option>
                                <option value="18:30">18:30</option>
                                <option value="19:00">19:00</option>
                                <option value="19:30">19:30</option>
                                <option value="20:00">20:00</option>
                                <option value="20:30">20:30</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Модель автомобиля *</label>
                            <input type="text" name="car_model" class="form-input" 
                                   placeholder="Например: BMW X5" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Год выпуска</label>
                            <input type="number" name="car_year" class="form-input" 
                                   min="1990" max="<?php echo date('Y'); ?>"
                                   placeholder="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Телефон для связи *</label>
                        <input type="tel" name="phone" class="form-input" 
                               placeholder="+7 (999) 123-45-67" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Дополнительные пожелания</label>
                        <textarea name="notes" class="form-input form-textarea" rows="3" 
                                  placeholder="Особые требования, цвет автомобиля и т.д."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-calendar-check"></i> Подтвердить запись
                        </button>
                        <button type="button" class="btn-cancel modal-close">
                            <i class="fas fa-times"></i> Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно авторизации -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Требуется авторизация</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Для записи на услугу необходимо войти в систему.</p>
                <div class="modal-actions">
                    <a href="login.php" class="btn-submit">
                        <i class="fas fa-sign-in-alt"></i> Войти
                    </a>
                    <a href="register.php" class="btn-cancel">
                        <i class="fas fa-user-plus"></i> Регистрация
                    </a>
                    <button type="button" class="btn-cancel modal-close">
                        <i class="fas fa-times"></i> Отмена
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Простые переменные
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        
        // Лоадер
        window.addEventListener('load', () => {
            const loader = document.querySelector('.loader');
            setTimeout(() => {
                loader.classList.add('loader-hidden');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }, 1000);
        });

        // Навигация при скролле
        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Мобильное меню
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                menuToggle.innerHTML = navMenu.classList.contains('active') 
                    ? '<i class="fas fa-times"></i>' 
                    : '<i class="fas fa-bars"></i>';
            });
        }

        // Анимация появления при скролле
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Модальные окна
        const bookingModal = document.getElementById('bookingModal');
        const loginModal = document.getElementById('loginModal');
        const modalCloses = document.querySelectorAll('.modal-close');
        const bookButtons = document.querySelectorAll('.btn-service');
        const bookingForm = document.getElementById('bookingForm');

        // Обработчики для кнопок "Записаться"
        bookButtons.forEach(button => {
            button.addEventListener('click', function() {
                const serviceId = this.getAttribute('data-id');
                const serviceName = this.getAttribute('data-title');
                
                if (isLoggedIn) {
                    // Показываем форму записи
                    document.getElementById('modalServiceId').value = serviceId;
                    document.getElementById('modalServiceName').textContent = serviceName;
                    
                    // Устанавливаем минимальную дату (сегодня)
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('bookingDate').min = today;
                    document.getElementById('bookingDate').value = today;
                    
                    bookingModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    // Показываем окно авторизации
                    loginModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Закрытие модальных окон
        modalCloses.forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        });

        // Закрытие по клику на фон
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Закрытие по ESC
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            }
        });

        // Валидация формы записи
        if (bookingForm) {
            bookingForm.addEventListener('submit', function(event) {
                const date = document.getElementById('bookingDate').value;
                const time = document.getElementById('bookingTime').value;
                
                if (!date || !time) {
                    event.preventDefault();
                    alert('Пожалуйста, выберите дату и время');
                    return;
                }
                
                // Проверка на прошедшую дату
                const selectedDate = new Date(date + 'T' + time);
                const now = new Date();
                
                if (selectedDate < now) {
                    event.preventDefault();
                    alert('Нельзя записываться на прошедшее время');
                    return;
                }
                
                // Если все ок, форма отправится
            });
        }

        // Функция показа уведомлений
        function showAlert(message, type = 'info') {
            // Удаляем старые уведомления
            const oldAlerts = document.querySelectorAll('.alert');
            oldAlerts.forEach(alert => alert.remove());
            
            // Создаем новое уведомление
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            
            // Иконка в зависимости от типа
            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'error') icon = 'exclamation-circle';
            
            alertDiv.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Автоматическое удаление через 5 секунд
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Показываем сообщения из сессии
        <?php if (isset($_SESSION['success'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('<?php echo addslashes($_SESSION['success']); ?>', 'success');
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('<?php echo addslashes($_SESSION['error']); ?>', 'error');
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // Анимация чисел в статистике
        const statNumbers = document.querySelectorAll('.stat-number');
        const aboutSection = document.querySelector('.about');
        
        const aboutObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                statNumbers.forEach(stat => {
                    const target = parseInt(stat.getAttribute('data-count'));
                    const duration = 2000;
                    const step = target / (duration / 16);
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            stat.textContent = target + '+';
                            clearInterval(timer);
                        } else {
                            stat.textContent = Math.floor(current);
                        }
                    }, 16);
                });
                aboutObserver.unobserve(aboutSection);
            }
        }, { threshold: 0.5 });

        if (aboutSection) {
            aboutObserver.observe(aboutSection);
        }

        // Плавная прокрутка
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Закрываем мобильное меню
                    if (navMenu) {
                        navMenu.classList.remove('active');
                        if (menuToggle) {
                            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($connection); ?>
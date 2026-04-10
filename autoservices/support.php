<?php
require_once 'config.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Для доступа в поддержку необходимо войти в систему';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Создание нового обращения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_ticket'])) {
    $subject = mysqli_real_escape_string($connection, $_POST['subject']);
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    $priority = mysqli_real_escape_string($connection, $_POST['priority']);
    
    // Создаем тикет
    $ticket_query = "INSERT INTO support_tickets (user_id, subject, priority) 
                     VALUES ('$user_id', '$subject', '$priority')";
    mysqli_query($connection, $ticket_query);
    $ticket_id = mysqli_insert_id($connection);
    
    // Добавляем первое сообщение
    $message_query = "INSERT INTO chat_messages (ticket_id, user_id, message) 
                      VALUES ('$ticket_id', '$user_id', '$message')";
    mysqli_query($connection, $message_query);
    
    $_SESSION['success'] = 'Обращение создано! Мы ответим в ближайшее время.';
    header('Location: support.php?ticket_id=' . $ticket_id);
    exit();
}

// Отправка нового сообщения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    
    // Проверяем, принадлежит ли тикет пользователю
    if (!$is_admin) {
        $check_query = "SELECT id FROM support_tickets WHERE id = $ticket_id AND user_id = $user_id";
        $check_result = mysqli_query($connection, $check_query);
        if (mysqli_num_rows($check_result) == 0) {
            $_SESSION['error'] = 'Доступ запрещен';
            header('Location: support.php');
            exit();
        }
    }
    
    $is_admin_value = $is_admin ? 1 : 0;
    $message_query = "INSERT INTO chat_messages (ticket_id, user_id, message, is_admin) 
                      VALUES ('$ticket_id', '$user_id', '$message', '$is_admin_value')";
    mysqli_query($connection, $message_query);
    
    // Обновляем статус тикета если нужно
    if ($is_admin) {
        mysqli_query($connection, "UPDATE support_tickets SET status = 'in_progress' WHERE id = $ticket_id");
    }
    
    header('Location: support.php?ticket_id=' . $ticket_id);
    exit();
}

// Закрытие тикета
if (isset($_GET['close_ticket'])) {
    $ticket_id = intval($_GET['close_ticket']);
    
    if ($is_admin) {
        mysqli_query($connection, "UPDATE support_tickets SET status = 'closed' WHERE id = $ticket_id");
        $_SESSION['success'] = 'Обращение закрыто';
    } else {
        $check_query = "SELECT id FROM support_tickets WHERE id = $ticket_id AND user_id = $user_id";
        $check_result = mysqli_query($connection, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            mysqli_query($connection, "UPDATE support_tickets SET status = 'closed' WHERE id = $ticket_id");
            $_SESSION['success'] = 'Обращение закрыто';
        }
    }
    header('Location: support.php');
    exit();
}

// Получаем список обращений пользователя
if ($is_admin) {
    // Админ видит все обращения
    $tickets_query = "SELECT t.*, u.username 
                      FROM support_tickets t 
                      LEFT JOIN users u ON t.user_id = u.id 
                      ORDER BY 
                        CASE t.status 
                            WHEN 'open' THEN 1 
                            WHEN 'in_progress' THEN 2 
                            ELSE 3 
                        END, 
                        t.created_at DESC";
    $tickets_result = mysqli_query($connection, $tickets_query);
} else {
    // Пользователь видит только свои обращения
    $tickets_query = "SELECT * FROM support_tickets WHERE user_id = $user_id ORDER BY created_at DESC";
    $tickets_result = mysqli_query($connection, $tickets_query);
}

// Получаем сообщения текущего чата
$current_ticket = null;
$messages_result = null;
if (isset($_GET['ticket_id'])) {
    $ticket_id = intval($_GET['ticket_id']);
    
    // Проверяем доступ
    if ($is_admin) {
        $current_ticket_query = "SELECT t.*, u.username FROM support_tickets t 
                                 LEFT JOIN users u ON t.user_id = u.id 
                                 WHERE t.id = $ticket_id";
    } else {
        $current_ticket_query = "SELECT * FROM support_tickets WHERE id = $ticket_id AND user_id = $user_id";
    }
    
    $current_ticket_result = mysqli_query($connection, $current_ticket_query);
    if (mysqli_num_rows($current_ticket_result) > 0) {
        $current_ticket = mysqli_fetch_assoc($current_ticket_result);
        
        $messages_query = "SELECT m.*, u.username 
                          FROM chat_messages m 
                          LEFT JOIN users u ON m.user_id = u.id 
                          WHERE m.ticket_id = $ticket_id 
                          ORDER BY m.created_at ASC";
        $messages_result = mysqli_query($connection, $messages_query);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поддержка - Blackwhite&Detailing</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .support-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #000000 0%, #111111 100%);
            min-height: 100vh;
        }
        
        .support-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .support-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        /* Список обращений */
        .tickets-sidebar {
            background: #222;
            border-radius: 15px;
            padding: 20px;
            height: calc(100vh - 200px);
            overflow-y: auto;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .tickets-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .tickets-title {
            color: #fff;
            font-size: 1.3rem;
            margin: 0;
        }
        
        .btn-new-ticket {
            padding: 10px 20px;
            background: transparent;
            border: 2px solid #44ff44;
            color: #44ff44;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-new-ticket:hover {
            background: #44ff44;
            color: #000;
        }
        
        .ticket-item {
            padding: 15px;
            background: rgba(255,255,255,0.03);
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        
        .ticket-item:hover {
            background: rgba(255,255,255,0.05);
            transform: translateY(-2px);
        }
        
        .ticket-item.active {
            background: rgba(68, 255, 68, 0.1);
            border-left-color: #44ff44;
        }
        
        .ticket-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-open {
            background: rgba(255, 170, 68, 0.1);
            color: #ffaa44;
        }
        
        .status-in_progress {
            background: rgba(68, 170, 255, 0.1);
            color: #44aaff;
        }
        
        .status-closed {
            background: rgba(68, 68, 68, 0.1);
            color: #888;
        }
        
        .ticket-subject {
            color: #fff;
            font-weight: 600;
            margin: 8px 0;
        }
        
        .ticket-meta {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.8rem;
        }
        
        /* Чат */
        .chat-container {
            background: #222;
            border-radius: 15px;
            height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-title {
            color: #fff;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message {
            display: flex;
            flex-direction: column;
            max-width: 70%;
        }
        
        .message-user {
            align-self: flex-end;
        }
        
        .message-admin {
            align-self: flex-start;
        }
        
        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }
        
        .message-user .message-bubble {
            background: #44ff44;
            color: #000;
            border-bottom-right-radius: 4px;
        }
        
        .message-admin .message-bubble {
            background: #333;
            color: #fff;
            border-bottom-left-radius: 4px;
        }
        
        .message-info {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            font-size: 0.75rem;
            color: #888;
        }
        
        .message-author {
            font-weight: 600;
            color: #44ff44;
        }
        
        .chat-input-container {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            gap: 10px;
        }
        
        .chat-input {
            flex: 1;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 25px;
            color: #fff;
            font-size: 0.95rem;
        }
        
        .chat-input:focus {
            outline: none;
            border-color: #44ff44;
        }
        
        .btn-send {
            width: 45px;
            height: 45px;
            background: transparent;
            border: 2px solid #44ff44;
            color: #44ff44;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-send:hover {
            background: #44ff44;
            color: #000;
        }
        
        .btn-close {
            padding: 8px 16px;
            background: transparent;
            border: 2px solid #ff4444;
            color: #ff4444;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-close:hover {
            background: #ff4444;
            color: #fff;
        }
        
        /* Форма создания обращения */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #222;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 2px solid #44ff44;
            color: #44ff44;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #44ff44;
            color: #000;
        }
        
        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #888;
        }
        
        @media (max-width: 992px) {
            .support-grid {
                grid-template-columns: 1fr;
            }
            
            .tickets-sidebar, .chat-container {
                height: auto;
                max-height: 500px;
            }
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
                <a href="index.php#about" class="nav-link">О нас</a>
                <a href="index.php#contact" class="nav-link">Контакты</a>
                <a href="support.php" class="nav-link active">Поддержка</a>
                
                <div class="auth-buttons">
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
                </div>
            </div>
            
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Поддержка -->
    <section class="support-section">
        <div class="support-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="color: #fff; font-size: 2.5rem;">Поддержка</h1>
                <button onclick="openNewTicketModal()" class="btn-new-ticket">
                    <i class="fas fa-plus"></i> Создать обращение
                </button>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: rgba(68,255,68,0.1); color: #44ff44; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(68,255,68,0.3);">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: rgba(255,68,68,0.1); color: #ff4444; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(255,68,68,0.3);">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="support-grid">
                <!-- Список обращений -->
                <div class="tickets-sidebar">
                    <div class="tickets-header">
                        <h3 class="tickets-title">Мои обращения</h3>
                        <span style="color: #888;"><?php echo mysqli_num_rows($tickets_result); ?></span>
                    </div>
                    
                    <?php if (mysqli_num_rows($tickets_result) > 0): ?>
                        <?php while ($ticket = mysqli_fetch_assoc($tickets_result)): ?>
                            <a href="support.php?ticket_id=<?php echo $ticket['id']; ?>" 
                               class="ticket-item <?php echo ($current_ticket && $current_ticket['id'] == $ticket['id']) ? 'active' : ''; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="ticket-status status-<?php echo $ticket['status']; ?>">
                                        <?php 
                                            $status_names = [
                                                'open' => 'Открыто',
                                                'in_progress' => 'В работе',
                                                'closed' => 'Закрыто'
                                            ];
                                            echo $status_names[$ticket['status']];
                                        ?>
                                    </span>
                                    <span style="color: <?php 
                                        echo $ticket['priority'] == 'high' ? '#ff4444' : 
                                            ($ticket['priority'] == 'medium' ? '#ffaa44' : '#44ff44'); 
                                    ?>; font-size: 0.8rem;">
                                        <?php echo ucfirst($ticket['priority']); ?> приоритет
                                    </span>
                                </div>
                                <div class="ticket-subject"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                <div class="ticket-meta">
                                    <span><i class="far fa-clock"></i> <?php echo date('d.m.Y', strtotime($ticket['created_at'])); ?></span>
                                    <?php if ($is_admin): ?>
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($ticket['username']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 20px; color: #888;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                            <p>У вас нет обращений в поддержку</p>
                            <button onclick="openNewTicketModal()" style="margin-top: 15px; padding: 10px 20px; background: transparent; border: 2px solid #44ff44; color: #44ff44; border-radius: 30px; cursor: pointer;">
                                Создать первое обращение
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Чат -->
                <div class="chat-container">
                    <?php if ($current_ticket): ?>
                        <div class="chat-header">
                            <div>
                                <h3 class="chat-title"><?php echo htmlspecialchars($current_ticket['subject']); ?></h3>
                                <span style="color: #888; font-size: 0.9rem;">
                                    ID: #<?php echo $current_ticket['id']; ?> • 
                                    Создано: <?php echo date('d.m.Y H:i', strtotime($current_ticket['created_at'])); ?>
                                    <?php if ($is_admin): ?>
                                        • Пользователь: <?php echo htmlspecialchars($current_ticket['username']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($current_ticket['status'] != 'closed'): ?>
                                <a href="?close_ticket=<?php echo $current_ticket['id']; ?>" 
                                   class="btn-close"
                                   onclick="return confirm('Закрыть обращение?')">
                                    <i class="fas fa-times"></i> Закрыть
                                </a>
                            <?php else: ?>
                                <span style="color: #888;">Обращение закрыто</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="chat-messages" id="chatMessages">
                            <?php if ($messages_result && mysqli_num_rows($messages_result) > 0): ?>
                                <?php while ($msg = mysqli_fetch_assoc($messages_result)): ?>
                                    <div class="message <?php echo $msg['is_admin'] ? 'message-admin' : 'message-user'; ?>">
                                        <div class="message-bubble">
                                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                        </div>
                                        <div class="message-info">
                                            <span class="message-author">
                                                <?php if ($msg['is_admin']): ?>
                                                    <i class="fas fa-shield-alt"></i> Поддержка
                                                <?php else: ?>
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($msg['username']); ?>
                                                <?php endif; ?>
                                            </span>
                                            <span><?php echo date('H:i, d.m.Y', strtotime($msg['created_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($current_ticket['status'] != 'closed'): ?>
                            <form method="POST" action="" class="chat-input-container">
                                <input type="hidden" name="ticket_id" value="<?php echo $current_ticket['id']; ?>">
                                <input type="hidden" name="send_message" value="1">
                                <input type="text" 
                                       name="message" 
                                       class="chat-input" 
                                       placeholder="Введите сообщение..." 
                                       required
                                       autocomplete="off">
                                <button type="submit" class="btn-send">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-chat-selected">
                            <i class="fas fa-comments" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;"></i>
                            <h3 style="color: #fff; margin-bottom: 10px;">Выберите обращение</h3>
                            <p style="color: #888;">Выберите обращение из списка слева или создайте новое</p>
                            <button onclick="openNewTicketModal()" class="btn-new-ticket" style="margin-top: 20px;">
                                <i class="fas fa-plus"></i> Создать обращение
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Модальное окно создания обращения -->
    <div id="newTicketModal" class="modal">
        <div class="modal-content">
            <h2 style="color: #fff; margin-bottom: 20px;">Новое обращение</h2>
            <form method="POST" action="">
                <input type="hidden" name="create_ticket" value="1">
                
                <div class="form-group">
                    <label class="form-label">Тема обращения *</label>
                    <input type="text" name="subject" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Приоритет</label>
                    <select name="priority" class="form-select">
                        <option value="low">Низкий</option>
                        <option value="medium" selected>Средний</option>
                        <option value="high">Высокий</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Сообщение *</label>
                    <textarea name="message" class="form-textarea" required></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">Отправить</button>
                    <button type="button" onclick="closeNewTicketModal()" style="flex: 1; padding: 12px; background: transparent; border: 2px solid #ff4444; color: #ff4444; border-radius: 30px; cursor: pointer;">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Функции для модального окна
        function openNewTicketModal() {
            document.getElementById('newTicketModal').classList.add('active');
        }
        
        function closeNewTicketModal() {
            document.getElementById('newTicketModal').classList.remove('active');
        }
        
        // Авто-скролл вниз чата
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
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
        
        // Закрытие модального окна по ESC
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeNewTicketModal();
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($connection); ?>
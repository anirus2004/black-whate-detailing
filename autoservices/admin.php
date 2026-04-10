<?php
require_once 'config.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: index.php');
    exit();
}

// Инициализируем переменную запроса
$services_result = null;

// Обработка удаления услуги
if (isset($_GET['delete_service'])) {
    $id = intval($_GET['delete_service']);
    $delete_query = "DELETE FROM services WHERE id = $id";
    mysqli_query($connection, $delete_query);
    $_SESSION['message'] = 'Услуга успешно удалена';
    header('Location: admin.php');
    exit();
}

// Обработка удаления пользователя
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    
    // Запрещаем удалять самого себя
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'Вы не можете удалить свой собственный аккаунт!';
    } else {
        $delete_query = "DELETE FROM users WHERE id = $id";
        if (mysqli_query($connection, $delete_query)) {
            $_SESSION['message'] = 'Пользователь успешно удален';
        } else {
            $_SESSION['error'] = 'Ошибка при удалении пользователя';
        }
    }
    header('Location: admin.php');
    exit();
}

// Обработка назначения/снятия администратора
if (isset($_GET['toggle_admin'])) {
    $id = intval($_GET['toggle_admin']);
    
    // Запрещаем менять свой собственный статус
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'Вы не можете изменить свой собственный статус!';
    } else {
        // Получаем текущий статус
        $check_query = "SELECT is_admin FROM users WHERE id = $id";
        $check_result = mysqli_query($connection, $check_query);
        $user = mysqli_fetch_assoc($check_result);
        
        if ($user) {
            $new_status = $user['is_admin'] == 1 ? 0 : 1;
            $update_query = "UPDATE users SET is_admin = $new_status WHERE id = $id";
            
            if (mysqli_query($connection, $update_query)) {
                $status_text = $new_status ? 'администратором' : 'обычным пользователем';
                $_SESSION['message'] = "Пользователь успешно сделан $status_text";
            } else {
                $_SESSION['error'] = 'Ошибка при изменении статуса пользователя';
            }
        } else {
            $_SESSION['error'] = 'Пользователь не найден';
        }
    }
    header('Location: admin.php');
    exit();
}

// Обработка добавления/редактирования услуги
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_service'])) {
        $title = mysqli_real_escape_string($connection, $_POST['title']);
        $description = mysqli_real_escape_string($connection, $_POST['description']);
        $image = mysqli_real_escape_string($connection, $_POST['image']);
        
        $query = "INSERT INTO services (title, description, image) 
                 VALUES ('$title', '$description', '$image')";
        mysqli_query($connection, $query);
        $_SESSION['message'] = 'Услуга успешно добавлена';
        
    } elseif (isset($_POST['edit_service'])) {
        $id = intval($_POST['id']);
        $title = mysqli_real_escape_string($connection, $_POST['title']);
        $description = mysqli_real_escape_string($connection, $_POST['description']);
        $image = mysqli_real_escape_string($connection, $_POST['image']);
        
        $query = "UPDATE services SET 
                 title = '$title',
                 description = '$description',
                 image = '$image'
                 WHERE id = $id";
        mysqli_query($connection, $query);
        $_SESSION['message'] = 'Услуга успешно обновлена';
    }
    
    header('Location: admin.php');
    exit();
}

// Получение данных для редактирования
$edit_service = null;
if (isset($_GET['edit_service'])) {
    $id = intval($_GET['edit_service']);
    $query = "SELECT * FROM services WHERE id = $id";
    $result = mysqli_query($connection, $query);
    $edit_service = mysqli_fetch_assoc($result);
}

// Получение списка пользователей
$users_query = "SELECT id, username, email, is_admin, created_at FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($connection, $users_query);

// Получение списка услуг
$services_query = "SELECT * FROM services ORDER BY id ASC";
$services_result = mysqli_query($connection, $services_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - Blackwhite&Detailing</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #000000 0%, #111111 100%);
            padding: 100px 20px 50px;
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .admin-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .admin-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        .admin-nav-link {
            padding: 10px 25px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .admin-nav-link.active {
            background-color: var(--white);
            color: var(--black);
        }
        
        .admin-nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.1);
        }
        
        .admin-section {
            background-color: rgba(34, 34, 34, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: none;
        }
        
        .admin-section.active {
            display: block;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 30px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-table th {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--accent);
            font-weight: 600;
        }
        
        .admin-table tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
        
        .badge-admin {
            display: inline-block;
            padding: 5px 10px;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--accent);
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .badge-user {
            display: inline-block;
            padding: 5px 10px;
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--accent-dark);
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .btn-action {
            padding: 8px 12px;
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--white);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 0 3px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        
        .btn-action:hover {
            background-color: var(--white);
            color: var(--black);
        }
        
        .btn-add {
            padding: 10px 25px;
            background-color: transparent;
            border: 2px solid var(--white);
            color: var(--white);
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            text-decoration: none;
        }
        
        .btn-add:hover {
            background-color: var(--white);
            color: var(--black);
        }
        
        .btn-delete {
            border-color: #ff4444;
            color: #ff4444;
        }
        
        .btn-delete:hover {
            background-color: #ff4444;
            color: var(--white);
        }
        
        .btn-edit {
            border-color: #44aaff;
            color: #44aaff;
        }
        
        .btn-edit:hover {
            background-color: #44aaff;
            color: var(--white);
        }
        
        .btn-admin-toggle {
            border-color: #ffaa44;
            color: #ffaa44;
        }
        
        .btn-admin-toggle:hover {
            background-color: #ffaa44;
            color: var(--white);
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background-color: rgba(34, 34, 34, 0.95);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--white);
            display: block;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--accent-dark);
            font-size: 0.9rem;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--accent);
            text-decoration: none;
            margin-bottom: 30px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--accent);
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--white);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-save {
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
        }
        
        .btn-save:hover {
            background-color: #44ff44;
            color: var(--black);
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
            margin-left: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-cancel:hover {
            background-color: #ff4444;
            color: var(--white);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background-color: rgba(68, 255, 68, 0.1);
            color: #44ff44;
            border: 1px solid rgba(68, 255, 68, 0.3);
        }
        
        .message.error {
            background-color: rgba(255, 68, 68, 0.1);
            color: #ff4444;
            border: 1px solid rgba(255, 68, 68, 0.3);
        }
        
        .current-user {
            background-color: rgba(68, 255, 68, 0.1) !important;
        }
        
        .current-user:hover {
            background-color: rgba(68, 255, 68, 0.2) !important;
        }
        
        .user-you {
            color: #44ff44;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--accent-dark);
            font-style: italic;
        }
        
        .user-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Вернуться на сайт
            </a>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="admin-header">
                <h1 class="admin-title">
                    <span class="logo-black">Black</span><span class="logo-white">White</span>
                    <span class="logo-amp">&</span>
                    <span class="logo-detailing">Detailing</span> Админ
                </h1>
                <p>Панель управления сайтом</p>
            </div>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number">
                        <?php 
                        $count_query = "SELECT COUNT(*) as total FROM users";
                        $count_result = mysqli_query($connection, $count_query);
                        $count = mysqli_fetch_assoc($count_result);
                        echo $count['total'];
                        ?>
                    </span>
                    <span class="stat-label">Пользователей</span>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <span class="stat-number">
                        <?php 
                        $count_query = "SELECT COUNT(*) as total FROM services";
                        $count_result = mysqli_query($connection, $count_query);
                        $count = mysqli_fetch_assoc($count_result);
                        echo $count['total'];
                        ?>
                    </span>
                    <span class="stat-label">Услуг</span>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span class="stat-number">
                        <?php 
                        $count_query = "SELECT COUNT(*) as total FROM users WHERE is_admin = 1";
                        $count_result = mysqli_query($connection, $count_query);
                        $count = mysqli_fetch_assoc($count_result);
                        echo $count['total'];
                        ?>
                    </span>
                    <span class="stat-label">Администраторов</span>
                </div>
            </div>
            
            <div class="admin-nav">
                <!-- Добавьте в admin-nav -->
<a href="#support" class="admin-nav-link">
    <i class="fas fa-headset"></i> Поддержка 
    <?php
    $new_tickets_query = "SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'";
    $new_tickets_result = mysqli_query($connection, $new_tickets_query);
    $new_tickets = mysqli_fetch_assoc($new_tickets_result);
    if ($new_tickets['count'] > 0) {
        echo '<span style="background: #ff4444; color: white; padding: 2px 8px; border-radius: 10px; margin-left: 5px; font-size: 0.8rem;">' . $new_tickets['count'] . '</span>';
    }
    ?>
</a>
                <a href="#users" class="admin-nav-link active">Пользователи</a>
                <a href="#services" class="admin-nav-link">Услуги</a>
                <a href="#add-service" class="admin-nav-link">Добавить услугу</a>
            </div>
            
            
            <!-- Секция пользователей -->
            <section id="users" class="admin-section active">
                <h2 class="section-title">
                    <i class="fas fa-users"></i> Пользователи
                </h2>
                
                <div class="table-responsive">
                    <?php if (mysqli_num_rows($users_result) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя пользователя</th>
                                <th>Email</th>
                                <th>Статус</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while ($user = mysqli_fetch_assoc($users_result)): 
                            $is_current_user = ($user['id'] == $_SESSION['user_id']);
                            ?>
                            <tr class="<?php echo $is_current_user ? 'current-user' : ''; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                    <?php if ($is_current_user): ?>
                                        <span class="user-you">(Вы)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_admin'] == 1): ?>
                                        <span class="badge-admin">
                                            <i class="fas fa-user-shield"></i> Админ
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-user">
                                            <i class="fas fa-user"></i> Пользователь
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="user-actions">
                                        <?php if ($is_current_user): ?>
                                            <span style="color: var(--accent-dark); font-size: 0.9rem;">
                                                <i class="fas fa-info-circle"></i> Это вы
                                            </span>
                                        <?php else: ?>
                                            <a href="?toggle_admin=<?php echo $user['id']; ?>" 
                                               class="btn-action btn-admin-toggle"
                                               onclick="return confirm('<?php echo $user['is_admin'] == 1 ? 'Снять права администратора у ' : 'Назначить администратором '; ?><?php echo htmlspecialchars(addslashes($user['username'])); ?>?')">
                                                <i class="fas fa-user-shield"></i>
                                                <?php echo $user['is_admin'] == 1 ? 'Снять админа' : 'Сделать админом'; ?>
                                            </a>
                                            <a href="?delete_user=<?php echo $user['id']; ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('Удалить пользователя <?php echo htmlspecialchars(addslashes($user['username'])); ?>?')">
                                                <i class="fas fa-trash"></i> Удалить
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-users-slash fa-2x"></i>
                        <p>Пользователи не найдены</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Секция услуг -->
            <section id="services" class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-car"></i> Услуги
                </h2>
                
                <div class="table-responsive">
                    <?php if ($services_result && mysqli_num_rows($services_result) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Описание</th>
                                <th>Изображение</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($service = mysqli_fetch_assoc($services_result)): ?>
                            <tr>
                                <td><?php echo $service['id']; ?></td>
                                <td><?php echo htmlspecialchars($service['title']); ?></td>
                                <td><?php echo substr(htmlspecialchars($service['description']), 0, 50); ?>...</td>
                                <td>
                                    <?php if ($service['image']): ?>
                                        <span style="color: var(--accent);"><?php echo htmlspecialchars($service['image']); ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--accent-dark);">Нет изображения</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?edit_service=<?php echo $service['id']; ?>#add-service" 
                                       class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i> Редактировать
                                    </a>
                                    <a href="?delete_service=<?php echo $service['id']; ?>" 
                                       class="btn-action btn-delete"
                                       onclick="return confirm('Удалить услугу &quot;<?php echo htmlspecialchars(addslashes($service['title'])); ?>&quot;?')">
                                        <i class="fas fa-trash"></i> Удалить
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-car fa-2x"></i>
                        <p>Услуги не найдены</p>
                        <a href="#add-service" class="btn-add">
                            <i class="fas fa-plus"></i> Добавить первую услугу
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Секция добавления/редактирования услуги -->
            <section id="add-service" class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-<?php echo $edit_service ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $edit_service ? 'Редактирование услуги' : 'Добавление новой услуги'; ?>
                </h2>
                
                <form method="POST" action="">
                    <?php if ($edit_service): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_service['id']; ?>">
                        <input type="hidden" name="edit_service" value="1">
                    <?php else: ?>
                        <input type="hidden" name="add_service" value="1">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-heading"></i> Название услуги
                        </label>
                        <input type="text" 
                               name="title" 
                               class="form-input" 
                               value="<?php echo $edit_service ? htmlspecialchars($edit_service['title']) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i> Описание услуги
                        </label>
                        <textarea name="description" 
                                  class="form-input form-textarea" 
                                  required><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-image"></i> Имя файла изображения
                        </label>
                        <input type="text" 
                               name="image" 
                               class="form-input" 
                               value="<?php echo $edit_service ? htmlspecialchars($edit_service['image']) : 'service-default.jpg'; ?>"
                               placeholder="Например: polishing.jpg">
                        <small style="color: var(--accent-dark); margin-top: 5px; display: block;">
                            Загрузите изображение в папку images/
                        </small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i>
                            <?php echo $edit_service ? 'Сохранить изменения' : 'Добавить услугу'; ?>
                        </button>
                        <a href="admin.php" class="btn-cancel">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </div>
    
    <script>
        // Переключение вкладок в админке
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href');
                
                // Убираем активный класс у всех ссылок
                document.querySelectorAll('.admin-nav-link').forEach(l => {
                    l.classList.remove('active');
                });
                
                // Добавляем активный класс текущей ссылке
                link.classList.add('active');
                
                // Скрываем все секции
                document.querySelectorAll('.admin-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Показываем нужную секцию
                const targetSection = document.querySelector(targetId);
                if (targetSection) {
                    targetSection.classList.add('active');
                }
            });
        });
        
        // Проверка URL hash при загрузке
        window.addEventListener('load', () => {
            const hash = window.location.hash;
            if (hash) {
                const targetLink = document.querySelector(`.admin-nav-link[href="${hash}"]`);
                if (targetLink) {
                    targetLink.click();
                }
            }
        });
        
        // Автоматическое закрытие сообщений через 5 секунд
        setTimeout(() => {
            document.querySelectorAll('.message').forEach(msg => {
                msg.style.display = 'none';
            });
        }, 5000);
        
        // Переход к форме редактирования при наличии edit_service
        <?php if (isset($_GET['edit_service'])): ?>
            setTimeout(() => {
                document.querySelector('a[href="#add-service"]').click();
            }, 100);
        <?php endif; ?>
    </script>
</body>
</html>
<?php mysqli_close($connection); ?>
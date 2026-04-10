<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Проверка пароля
    if ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else if (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } else {
        // Проверка существования пользователя
        $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Пользователь с таким именем или email уже существует';
        } else {
            // Хеширование пароля
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Создание пользователя
            $insert_query = "INSERT INTO users (username, email, password) 
                            VALUES ('$username', '$email', '$hashed_password')";
            
            if (mysqli_query($connection, $insert_query)) {
                $success = 'Регистрация успешна! Теперь вы можете войти.';
            } else {
                $error = 'Ошибка при регистрации: ' . mysqli_error($connection);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Blackwhite&Detailing</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #000000 0%, #111111 100%);
        }
        
        .auth-box {
            background-color: rgba(34, 34, 34, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .auth-title {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--white);
        }
        
        .auth-subtitle {
            text-align: center;
            color: var(--accent-dark);
            margin-bottom: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--accent);
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: var(--white);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }
        
        .btn-auth {
            width: 100%;
            padding: 15px;
            background-color: transparent;
            border: 2px solid var(--white);
            color: var(--white);
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-auth:hover {
            background-color: var(--white);
            color: var(--black);
        }
        
        .auth-links {
            margin-top: 30px;
            text-align: center;
        }
        
        .auth-link {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .auth-link:hover {
            color: var(--white);
            text-decoration: underline;
        }
        
        .error-message {
            background-color: rgba(255, 68, 68, 0.1);
            color: #ff4444;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 68, 68, 0.3);
        }
        
        .success-message {
            background-color: rgba(68, 255, 68, 0.1);
            color: #44ff44;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(68, 255, 68, 0.3);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box animate-fade-up">
            <h1 class="auth-title">
                <span class="logo-black">Black</span><span class="logo-white">White</span>
                <span class="logo-amp">&</span>
                <span class="logo-detailing">Detailing</span>
            </h1>
            <p class="auth-subtitle">Создайте новый аккаунт</p>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Имя пользователя
                    </label>
                    <input type="text" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Пароль
                    </label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Подтвердите пароль
                    </label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn-auth">
                    <i class="fas fa-user-plus"></i> Зарегистрироваться
                </button>
            </form>
            <?php endif; ?>
            
            <div class="auth-links">
                <p>Уже есть аккаунт? <a href="login.php" class="auth-link">Войти</a></p>
                <p><a href="index.php" class="auth-link">Вернуться на главную</a></p>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
<?php mysqli_close($connection); ?>
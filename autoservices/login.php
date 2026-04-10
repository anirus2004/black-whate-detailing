<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
    $result = mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Неверный пароль';
        }
    } else {
        $error = 'Пользователь не найден';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Blackwhite&Detailing</title>
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
            <p class="auth-subtitle">Войдите в свой аккаунт</p>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Имя пользователя или Email
                    </label>
                    <input type="text" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Пароль
                    </label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn-auth">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
            </form>
            
            <div class="auth-links">
                <p>Нет аккаунта? <a href="register.php" class="auth-link">Зарегистрироваться</a></p>
                <p><a href="index.php" class="auth-link">Вернуться на главную</a></p>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
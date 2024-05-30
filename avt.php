<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="imgs/icon.png"/>
    <title>Авторизация</title>
    <link rel="stylesheet" type="text/css" href="avt_styles_mob.css" media="only screen and (max-width: 768px)">
</head>
<body>
    <a href="main.html" class="backbut"><img src="imgs/back.png"></a>
    <div class="text">Введите данные</div>
    <form action="" method="post" class="form">
        <label for="login">Логин</label>
        <input class="textarea" type="text" id="login" name="login" required>

        <label for="password">Пароль</label>
        <input class="textarea" type="password" id="password" name="password" required>
      
        <input type="submit" class="form-button" value="Войти в кабинет">

        <a href="reg.php" class="textmini">У меня нет аккаунта</a>
    </form>

    <?php
    session_start();

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "lapka";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM accounts WHERE login='$login'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_city'] = $user['city_id'];
                $_SESSION['user_area'] = $user['area_id'];
                header("Location: ads.php");
                exit();
            } else {
                echo "<p>Неверный пароль</p>";
            }
        } else {
            echo "<p>Такой пользователь не найден</p>";
        }
    }

    $conn->close();
    ?>
</body>
</html>

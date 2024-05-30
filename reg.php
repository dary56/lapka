<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="imgs/icon.png"/>
    <title>Создание профиля</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Flamenco:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="reg_styles_mob.css" media="only screen and (max-width: 768px)">
</head>
<body>
    <a href="main.html" class="backbut"><img src="imgs/back.png"></a>
    <div class="text">Заполните форму</div>
    <form action="" method="post" class="form">

        <label for="name">Имя</label>
        <input class="textarea" type="text" id="name" name="name" placeholder="ник" required>

        <label for="date">Возраст</label>
        <input class="textarea" type="number" id="date" name="age" placeholder="полных лет" required>

        <label for="sex">Пол</label>
        <select class="textarea" id="sex" name="sex" required>
            <option value="м">Мужской</option>
            <option value="ж">Женский</option>
            <option value="другой">Другой</option>
        </select>

        <label for="town">Город</label>
        <select class="textarea" id="town" name="city" required>
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "lapka";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT id, name FROM cities";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
            ?>
        </select>

        <label for="area">Район</label>
        <select class="textarea" id="area" name="area" required>
        </select>

        <label for="login">Логин</label>
        <input class="textarea" type="text" id="login" name="login" placeholder="имя для входа в систему" required>

        <label for="password">Пароль</label>
        <input class="textarea" type="password" id="password" name="password" placeholder="не менее 12 символов" required>
      
        <input type="submit" class="form-button" name="registration" value="Создать профиль">

        <a href="avt.php" class="textmini">У меня уже есть аккаунт</a>
    </form>

    <script>
        document.getElementById('town').addEventListener('change', function() {
            var cityId = this.value;
            var areaSelect = document.getElementById('area');
            areaSelect.innerHTML = '';
            fetch('get_areas.php?city_id=' + cityId)
            .then(response => response.json())
            .then(data => {
                data.forEach(function(area) {
                    var option = document.createElement('option');
                    option.value = area.id;
                    option.text = area.name;
                    areaSelect.add(option);
                });
            })
            .catch(error => console.error('Ошибка:', error));
        });
    </script>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "lapka";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registration'])) {
        $name = $_POST['name'];
        $age = $_POST['age'];
        $sex = $_POST['sex'];
        $city_id = $_POST['city'];
        $area_id = $_POST['area'];
        $login = $_POST['login'];
        $password = $_POST['password'];

        // Хэширование пароля
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO accounts (name, age, sex, city_id, area_id, login, password) 
                VALUES ('$name', '$age', '$sex', '$city_id', '$area_id', '$login', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            header("Location: ads.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    ?>
</body>
</html>

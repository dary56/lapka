<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="imgs/icon.png"/>
    <title>Редактирование профиля</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Flamenco:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="reg_styles_mob.css" media="only screen and (max-width: 768px)">
</head>
<body>
    <a href="ads.php" class="backbut"><img src="imgs/back.png"></a>
    <div class="text">Данные профиля</div>

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

    if (!isset($_SESSION['user_id'])) {
        header("Location: avt.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    $sql = "SELECT a.name, a.age, a.sex, a.city_id, a.area_id, a.login, a.password, c.name AS city_name, ar.name AS area_name 
            FROM accounts a 
            JOIN cities c ON a.city_id = c.id 
            JOIN areas ar ON a.area_id = ar.id 
            WHERE a.id = $user_id";

    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    ?>

    <form action="" method="post" class="form">
        <label for="name">Имя</label>
        <input class="textarea" type="text" id="name" name="name" value="<?php echo $user['name']; ?>" readonly>

        <label for="date">Возраст</label>
        <input class="textarea" type="number" id="date" name="age" value="<?php echo $user['age']; ?>" readonly>

        <label for="sex">Пол</label>
        <select class="textarea" id="sex" name="sex" disabled>
            <option value="м" <?php if ($user['sex'] == 'м') echo 'selected'; ?>>Мужской</option>
            <option value="ж" <?php if ($user['sex'] == 'ж') echo 'selected'; ?>>Женский</option>
            <option value="другой" <?php if ($user['sex'] == 'другой') echo 'selected'; ?>>Другой</option>
        </select>

        <label for="town">Город</label>
        <select class="textarea" id="town" name="city" disabled>
            <?php
            $sql = "SELECT id, name FROM cities";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "' " . ($row['id'] == $user['city_id'] ? 'selected' : '') . ">" . $row['name'] . "</option>";
                }
            }
            ?>
        </select>

        <label for="area">Район</label>
        <select class="textarea" id="area" name="area" disabled>
            <option value="<?php echo $user['area_id']; ?>"><?php echo $user['area_name']; ?></option>
        </select>

        <label for="login">Логин</label>
        <input class="textarea" type="text" id="login" name="login" value="<?php echo $user['login']; ?>" readonly>

        <label for="password">Пароль</label>
        <input class="textarea" type="password" id="password" name="password" placeholder="Пароль" readonly>

        <input type="button" class="form-button" id="edit-button" value="Редактировать">
        <input type="submit" class="form-button" id="save-button" name="save" value="Сохранить" style="display:none;">

        <a href="logout.php" class="textmini">Выйти</a>
    </form>

    <script>
        document.getElementById('edit-button').addEventListener('click', function() {
            var fields = document.querySelectorAll('.textarea');
            fields.forEach(function(field) {
                field.removeAttribute('readonly');
                field.removeAttribute('disabled');
            });
            document.getElementById('edit-button').style.display = 'none';
            document.getElementById('save-button').style.display = 'block';
        });

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
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
        $name = $_POST['name'];
        $age = $_POST['age'];
        $sex = $_POST['sex'];
        $city_id = $_POST['city'];
        $area_id = $_POST['area'];
        $login = $_POST['login'];
        $password = $_POST['password'];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE accounts 
                    SET name='$name', age='$age', sex='$sex', city_id='$city_id', area_id='$area_id', login='$login', password='$hashed_password'
                    WHERE id=$user_id";
        } else {
            $sql = "UPDATE accounts 
                    SET name='$name', age='$age', sex='$sex', city_id='$city_id', area_id='$area_id', login='$login'
                    WHERE id=$user_id";
        }

        if ($conn->query($sql) === TRUE) {
            echo "Данные успешно обновлены";
            echo "<script>window.location.href = window.location.href;</script>";
        } else {
            echo "Ошибка: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    ?>
</body>
</html>

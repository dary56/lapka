<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="imgs/icon.png"/>
    <title>Создание объявления</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Flamenco:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="reg_styles_mob.css" media="only screen and (max-width: 768px)">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <a href="ads.php" class="backbut"><img src="imgs/back.png"></a>
    <div class="text">Заполните форму</div>

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

    $sql = "SELECT a.name, a.city_id, a.area_id, c.name AS city_name, ar.name AS area_name 
            FROM accounts a 
            JOIN cities c ON a.city_id = c.id 
            JOIN areas ar ON a.area_id = ar.id 
            WHERE a.id = $user_id";

    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    ?>

    <form action="" method="post" enctype="multipart/form-data" class="form">
        <label for="name">Тип объявления</label>
        <select class="textarea" id="ad_type" name="ad_type" required>
            <option value="lost">О пропаже</option>
            <option value="found">О находке</option>
        </select>

        <label for="photo">Фото животного</label>
        <input type="file" id="photo" name="photo" accept="image/*">

        <label for="pet_type">Вид животного</label>
        <select class="textarea" id="pet_type" name="pet_type" required onchange="updateBreeds()">
            <?php
            $sql = "SELECT id, name FROM pet_types";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
            ?>
        </select>

        <label for="name">Кличка/имя</label>
        <input type="text" id="name" name="name">

        <label for="age">Возраст</label>
        <input type="text" id="age" name="age">

        <label for="breed">Порода</label>
        <select class="textarea" id="breed" name="breed">
            <option value="">-</option>
        </select>

        <label for="color">Окрас</label>
        <input type="text" id="color" name="color">

        <label for="features">Отличительные черты</label>
        <input type="text" id="features" name="features">

        <label for="city">Город</label>
        <select class="textarea" id="city" name="city">
            <?php
            $sql = "SELECT id, name FROM cities";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $selected = ($row['id'] == $user['city_id']) ? 'selected' : '';
                    echo "<option value='" . $row['id'] . "' " . $selected . ">" . $row['name'] . "</option>";
                }
            }
            ?>
        </select>

        <label for="area">Район</label>
        <select class="textarea" id="area" name="area">
            <option value="<?php echo $user['area_id']; ?>" selected><?php echo $user['area_name']; ?></option>
        </select>

        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <label>Добавить отметку на карте?</label>
        <input type="radio" id="addMarkerYes" name="add_marker" value="yes" onchange="toggleMap(true)"> Да
        <input type="radio" id="addMarkerNo" name="add_marker" value="no" onchange="toggleMap(false)" checked> Нет

        <div id="map" style="height: 50vw; width: 100%; display: none;"></div>

        <label for="comment">Комментарий</label>
        <textarea id="comment" name="comment"></textarea>

        <label for="username">Ник пользователя</label>
        <input type="text" id="username" name="username" readonly value="<?php echo htmlspecialchars($user['name']); ?>">

        <label for="phone">Номер телефона</label>
        <input type="text" id="phone" name="phone" pattern="8\d{10}" required>

        <input type="submit" class="form-button" name="create_ad" value="Создать объявление">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_ad'])) {
        $ad_type = $_POST['ad_type'];
        $pet_type = $_POST['pet_type'];
        $name = $_POST['name'];
        $age = $_POST['age'];
        $breed = $_POST['breed'];
        $color = $_POST['color'];
        $features = $_POST['features'];
        $city = $_POST['city'];
        $area = $_POST['area'];
        $lat = $_POST['latitude'];
        $lng = $_POST['longitude'];
        $comment = $_POST['comment'];
        $user_id = $_SESSION['user_id'];
        $phone = $_POST['phone'];

        $photo_filename = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $photo_tmp_name = $_FILES['photo']['tmp_name'];
            $photo_filename = basename($_FILES['photo']['name']);
            $target_directory = 'imgs/';
            $target_file = $target_directory . $photo_filename;
            if (!move_uploaded_file($photo_tmp_name, $target_file)) {
                echo "Ошибка при загрузке файла.";
                exit;
            }
        }

        $sql = "INSERT INTO ads (ad_type, photo, pet_type_id, name, age, breed_id, color, features, city_id, area_id, latitude, longitude, comment, user_id, phone) 
        VALUES ('$ad_type', '$photo_filename', '$pet_type', '$name', '$age', '$breed', '$color', '$features', '$city', '$area', '$lat', '$lng', '$comment', '$user_id', '$phone')";

        if ($conn->query($sql) === TRUE) {
            echo "Объявление успешно создано";
            echo "<script>window.location.href = window.location.href;</script>";
            header("Location: ads.php");
        } else {
            echo "Ошибка: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    ?>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        function updateBreeds() {
            var petType = document.getElementById('pet_type').value;
            var breedSelect = document.getElementById('breed');
            
            breedSelect.innerHTML = '';

            if (petType) {
                fetch('get_breeds.php?pet_type=' + petType)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        data.forEach(breed => {
                            var option = document.createElement('option');
                            option.value = breed.id;
                            option.textContent = breed.name;
                            breedSelect.appendChild(option);
                        });
                    } else {
                        var option = document.createElement('option');
                        option.value = '';
                        option.textContent = '-';
                        breedSelect.appendChild(option);
                    }
                });
            } else {
                var option = document.createElement('option');
                option.value = '';
                option.textContent = '-';
                breedSelect.appendChild(option);
            }
        }

        function toggleMap(show) {
            var mapContainer = document.getElementById('map');
            if (show) {
                mapContainer.style.display = 'block';
            } else {
                mapContainer.style.display = 'none';
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                if (marker) {
                    map.removeLayer(marker);
                }
            }
        }

        window.onload = function() {
            updateBreeds();

            var lat = <?php echo json_encode($user['latitude']); ?>;
            var lng = <?php echo json_encode($user['longitude']); ?>;
            var map = L.map('map').setView([45.0355, 38.9753], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            var marker;

            map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                if (marker) {
                    map.removeLayer(marker);
                }

                marker = L.marker([lat, lng]).addTo(map);

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            });
        }
        document.getElementById('city').addEventListener('change', function() {
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
</body>
</html>

<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="imgs/icon.png"/>
    <title>Доска объявлений</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Flamenco:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="ads_styles_mob.css" media="only screen and (max-width: 768px)">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<body>
    <div class="wrapper">
        <input type="checkbox" id="menu-toggle" class="menu-toggle">
        <label for="menu-toggle" class="menu-btn"><img src="imgs/icon.png" alt="menu"></label>
        <div class="menu">
            <nav class="menu-list">
                <a href="main.html">Главная</a>
                <a href="edit.php">Профиль</a>
                <a href="#">Телеграм</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <img src="imgs/adsboard.png" alt="ads_board">
    </div>

    <div class="filters">
        <input type="checkbox" id="filter-toggle" class="filter-toggle">
        <label for="filter-toggle" class="filter-btn"><img src="imgs/filter.png" alt="filter"></label>
        <div class="filter">
            <form method="GET" action="">
                <div class="filter-list">
                    <ul>
                        <label for="adtype" class="toggle-btn">Вид объявления</label>
                        <select name="adtype" id="adtype">
                            <option value="">Все</option>
                            <?php
                            $conn = new mysqli("localhost", "root", "", "lapka");
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }
                            $adTypesResult = $conn->query("SELECT DISTINCT ad_type FROM ads");
                            while ($adType = $adTypesResult->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($adType['ad_type']) . '">' . htmlspecialchars($adType['ad_type']) . '</option>';
                            }
                            ?>
                        </select>
                    </ul>
                    <ul>
                        <label for="town" class="toggle-btn">Город</label>
                        <select name="town" id="town" onchange="loadAreas(this.value)">
                            <option value="">Все</option>
                            <?php
                            $citiesResult = $conn->query("SELECT id, name FROM cities");
                            while ($city = $citiesResult->fetch_assoc()) {
                                echo '<option value="' . $city['id'] . '">' . htmlspecialchars($city['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </ul>
                    <ul>
                        <label for="area" class="toggle-btn">Район</label>
                        <select name="area" id="area">
                            <option value="">Все</option>
                        </select>
                    </ul>
                    <ul>
                        <label for="pettype" class="toggle-btn">Вид животного</label>
                        <select name="pettype" id="pettype" onchange="loadBreeds(this.value)">
                            <option value="">Все</option>
                            <?php
                            $petTypesResult = $conn->query("SELECT id, name FROM pet_types");
                            while ($petType = $petTypesResult->fetch_assoc()) {
                                echo '<option value="' . $petType['id'] . '">' . htmlspecialchars($petType['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </ul>
                    <ul>
                        <label for="breed" class="toggle-btn">Порода</label>
                        <select name="breed" id="breed">
                            <option value="">Все</option>
                        </select>
                    </ul>
                </div>
                <input type="submit" value="Фильтровать">
            </form>
        </div>
    </div>

    <input type="submit" class="create-button" value="Создать объявление" onclick="location.href='create.php'">

    <div class="ads-container">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "lapka";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        function filterAds($conn, $adType, $town, $area, $petType, $breed) {
            $sql = "SELECT ads.id, ads.photo, pet_types.name AS pet_type, ads.name, ads.age, breeds.name AS breed, ads.color, ads.features, cities.name AS city, areas.name AS area, ads.latitude, ads.longitude, ads.comment, accounts.name AS username, ads.phone 
                    FROM ads 
                    JOIN pet_types ON ads.pet_type_id = pet_types.id 
                    JOIN breeds ON ads.breed_id = breeds.id 
                    JOIN cities ON ads.city_id = cities.id
                    JOIN areas ON ads.area_id = areas.id
                    JOIN accounts ON ads.user_id = accounts.id";

            $conditions = [];
            if (!empty($adType)) {
                $conditions[] = "ads.ad_type = '" . $conn->real_escape_string($adType) . "'";
            }
            if (!empty($town)) {
                $conditions[] = "cities.id = '" . $conn->real_escape_string($town) . "'";
            }
            if (!empty($area)) {
                $conditions[] = "areas.id = '" . $conn->real_escape_string($area) . "'";
            }
            if (!empty($petType)) {
                $conditions[] = "pet_types.id = '" . $conn->real_escape_string($petType) . "'";
            }
            if (!empty($breed)) {
                $conditions[] = "breeds.id = '" . $conn->real_escape_string($breed) . "'";
            }

            if (count($conditions) > 0) {
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            return $conn->query($sql);
        }

        $adType = $_GET['adtype'] ?? '';
        $town = $_GET['town'] ?? '';
        $area = $_GET['area'] ?? '';
        $petType = $_GET['pettype'] ?? '';
        $breed = $_GET['breed'] ?? '';

        $result = filterAds($conn, $adType, $town, $area, $petType, $breed);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="ad">';
                echo '<img src="imgs/' . htmlspecialchars($row['photo']) . '" alt="Фото питомца" class="pet-photo">';
                echo '<div class="ad-details">';
                echo '<p><strong>Вид животного:</strong> ' . htmlspecialchars($row['pet_type']) . '</p>';
                echo '<p><strong>Кличка:</strong> ' . htmlspecialchars($row['name']) . '</p>';
                echo '<p><strong>Возраст:</strong> ' . htmlspecialchars($row['age']) . '</p>';
                echo '<p class="extra-detail"><strong>Порода:</strong> ' . htmlspecialchars($row['breed']) . '</p>';
                echo '<p class="extra-detail"><strong>Окрас:</strong> ' . htmlspecialchars($row['color']) . '</p>';
                echo '<p class="extra-detail"><strong>Отличительные черты:</strong> ' . htmlspecialchars($row['features']) . '</p>';
                echo '<p class="extra-detail"><strong>Город:</strong> ' . htmlspecialchars($row['city']) . '</p>';
                echo '<p class="extra-detail"><strong>Район:</strong> ' . htmlspecialchars($row['area']) . '</p>';
                echo '<div class="map-container" style="height: 0; overflow: hidden;">';
                echo '<div id="map' . $row['id'] . '" class="map"></div>';
                echo '</div>';
                echo '<script>
                    var map' . $row['id'] . ' = L.map("map' . $row['id'] . '").setView([' . $row['latitude'] . ', ' . $row['longitude'] . '], 13);
                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                        attribution: "© OpenStreetMap contributors"
                    }).addTo(map' . $row['id'] . ');
                    L.marker([' . $row['latitude'] . ', ' . $row['longitude'] . ']).addTo(map' . $row['id'] . ')
                        .bindPopup("Примерное местоположение питомца")
                        .openPopup();
                </script>';
                echo '<p class="extra-detail"><strong>Комментарий:</strong> ' . htmlspecialchars($row['comment']) . '</p>';
                echo '<p class="extra-detail"><strong>Ник пользователя:</strong> ' . htmlspecialchars($row['username']) . '</p>';
                echo '<p class="extra-detail"><strong>Номер телефона:</strong> ' . htmlspecialchars($row['phone']) . '</p>';
                echo '<button class="expand-btn">Развернуть</button>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="no">Объявлений нет.</p>';
        }

        $conn->close();
        ?>
    </div>

    <script>
        document.querySelectorAll('.expand-btn').forEach(button => {
            button.addEventListener('click', function() {
                const adDetails = this.closest('.ad-details');
                const extraDetails = adDetails.querySelectorAll('.extra-detail');
                extraDetails.forEach(detail => {
                    detail.classList.toggle('show');
                });
                this.textContent = extraDetails[0].classList.contains('show') ? 'Свернуть' : 'Развернуть';
                
                const mapContainer = adDetails.querySelector('.map-container');
                if (mapContainer) {
                    if (extraDetails[0].classList.contains('show')) {
                        mapContainer.style.height = '55vh';
                        mapContainer.style.overflow = 'auto';
                    } else {
                        mapContainer.style.height = '0';
                        mapContainer.style.overflow = 'hidden';
                    }
                }
            });
        });

       

        function loadAreas(cityId) {
            fetch('get_areas_filt.php?city_id=' + cityId)
                .then(response => response.json())
                .then(data => {
                    const areaSelect = document.getElementById('area');
                    areaSelect.innerHTML = '<option value="">Все</option>';
                    data.forEach(area => {
                        const option = document.createElement('option');
                        option.value = area.id;
                        option.textContent = area.name;
                        areaSelect.appendChild(option);
                    });
                });
        }

        function loadBreeds(petTypeId) {
            fetch('get_breeds_filt.php?pet_type_id=' + petTypeId)
                .then(response => response.json())
                .then(data => {
                    const breedSelect = document.getElementById('breed');
                    breedSelect.innerHTML = '<option value="">Все</option>';
                    data.forEach(breed => {
                        const option = document.createElement('option');
                        option.value = breed.id;
                        option.textContent = breed.name;
                        breedSelect.appendChild(option);
                    });
                });
        }  

    </script>

    <footer>
        <div class="footer-container">
            <img src="imgs/logo.png" alt="логотип" style="width: 50%;">
            <div class="contact-info">
                <p>Телефон<b>: +7 (123) 456-7890</b></p>
                <p>Почта<b>: example@example.com</b></p>
            </div>
        </div>
    </footer> 
</body>
</html>
<div id="hidden-content" style="display: none;">
<?php
$city_id = intval($_GET['city_id']);
$conn = new mysqli("localhost", "root", "", "lapka");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$areasResult = $conn->query("SELECT id, name FROM areas WHERE city_id = $city_id");
$areas = [];
while ($area = $areasResult->fetch_assoc()) {
    $areas[] = $area;
}
$conn->close();
echo json_encode($areas);
?>
<?php
$pet_type_id = intval($_GET['pet_type_id']);
$conn = new mysqli("localhost", "root", "", "lapka");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$breedsResult = $conn->query("SELECT id, name FROM breeds WHERE pet_type_id = $pet_type_id");
$breeds = [];
while ($breed = $breedsResult->fetch_assoc()) {
    $breeds[] = $breed;
}
$conn->close();
echo json_encode($breeds);
?>
</div>

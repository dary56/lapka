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

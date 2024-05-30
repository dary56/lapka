<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lapka";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$city_id = $_GET['city_id'];

$sql = "SELECT id, name FROM areas WHERE city_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $city_id);
$stmt->execute();
$result = $stmt->get_result();

$areas = [];
while($row = $result->fetch_assoc()) {
    $areas[] = $row;
}

echo json_encode($areas);

$stmt->close();
$conn->close();
?>

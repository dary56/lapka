<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lapka";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pet_type = $_GET['pet_type'];

$sql = "SELECT id, name FROM breeds WHERE pet_type_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pet_type);
$stmt->execute();
$result = $stmt->get_result();

$breeds = [];
while($row = $result->fetch_assoc()) {
    $breeds[] = $row;
}

echo json_encode($breeds);

$stmt->close();
$conn->close();
?>

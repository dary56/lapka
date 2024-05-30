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

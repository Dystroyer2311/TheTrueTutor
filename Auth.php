<?php
header("Content-Type: application/json");

$servername = "localhost";
$username   = "root";
$password   = ""; // empty string by default
$dbname     = "thetruetutor_db"; // create or check in phpMyAdmin

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if ($action === 'signup') {
    $name = $conn->real_escape_string($data['TutorName']);
    $pass = $conn->real_escape_string($data['Password']);

    $check = $conn->query("SELECT TutorID FROM tblTutors WHERE TutorName='$name'");
    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Tutor Name already exists"]);
        exit;
    }

    $conn->query("INSERT INTO tblTutors (TutorName, Password) VALUES ('$name', '$pass')");
    echo json_encode(["success" => true]);
}
elseif ($action === 'login') {
    $name = $conn->real_escape_string($data['TutorName']);
    $pass = $conn->real_escape_string($data['Password']);

    $result = $conn->query("SELECT TutorID FROM tblTutors WHERE TutorName='$name' AND Password='$pass'");
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "TutorID" => $row['TutorID']]);
    } else {
        echo json_encode(["success" => false]);
    }
}

$conn->close();
?>

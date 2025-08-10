<?php
header("Content-Type: application/json");

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "thetruetutor_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if ($action === 'signup') {
    $name = $conn->real_escape_string($data['TutorName']);
    $pass = $conn->real_escape_string($data['Password']);

    $check = $conn->query("SELECT Tutor_Number FROM tutor_tbl WHERE Tutor_Name_Surname='$name'");
    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Tutor Name already exists"]);
        exit;
    }

    $conn->query("INSERT INTO tutor_tbl (Tutor_Name_Surname, Tutor_Password) VALUES ('$name', '$pass')");
    echo json_encode(["success" => true]);
}

elseif ($action === 'login') {
    $name = $conn->real_escape_string($data['TutorName']);
    $pass = $conn->real_escape_string($data['Password']);

    $result = $conn->query("SELECT Tutor_Number FROM tutor_tbl WHERE Tutor_Name_Surname='$name' AND Tutor_Password='$pass'");
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "TutorID" => $row['Tutor_Number']]);
    } else {
        echo json_encode(["success" => false]);
    }
}

elseif ($action === 'get_hours') {
    $TutorID = intval($_GET['TutorID'] ?? 0);
    $result = $conn->query("SELECT id, Lesson_Date, Student_Name_Surname, Subject, Grade, Travel_Fee, Number_Hours, Rate_Hour 
                            FROM hours_tbl 
                            WHERE TutorID = $TutorID 
                            ORDER BY Lesson_Date DESC");
    $hours = [];
    while ($row = $result->fetch_assoc()) {
        $hours[] = $row;
    }
    echo json_encode($hours);
}

elseif ($action === 'add_hour') {
    $TutorID = intval($data['TutorID']);
    $Lesson_Date = $conn->real_escape_string($data['Date']);
    $Student_Name_Surname = $conn->real_escape_string($data['StudentName']);
    $Subject = $conn->real_escape_string($data['Subject']);
    $Grade = $conn->real_escape_string($data['Grade']);
    $Travel_Fee = intval($data['TravelFee']);
    $Number_Hours = intval($data['NoOfHours']);
    $Rate_Hour = intval($data['Rate']);

    $stmt = $conn->prepare("INSERT INTO hours_tbl (TutorID, Lesson_Date, Student_Name_Surname, Subject, Grade, Travel_Fee, Number_Hours, Rate_Hour) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiii", $TutorID, $Lesson_Date, $Student_Name_Surname, $Subject, $Grade, $Travel_Fee, $Number_Hours, $Rate_Hour);
    $success = $stmt->execute();

    echo json_encode(["success" => $success]);
}

else {
    echo json_encode(["error" => "Invalid action"]);
}

$conn->close();
?>

<?php
$servername = getenv('DBHOST');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$dbname = getenv('MYSQL_DATABASE');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, firstname, lastname FROM MyGuests";
$result = $conn->query($sql);
print "<img width='500' src='friends.jpg' />";
print "<h1>List of Friends from " . $_SERVER['SERVER_ADDR'];
print "</h1><br><br>";

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
    }
} else {
    echo "<p>0 results</p>";
}
$conn->close();
?>

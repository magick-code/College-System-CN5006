
<?php
$servername = "localhost";
		$dbusername = "root";
		$dbpassword = "";
		$dbname = "schoolDB";
		$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		mysqli_set_charset($conn, "utf8");

?>

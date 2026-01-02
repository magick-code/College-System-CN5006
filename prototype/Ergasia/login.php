<html>
<head>
<style>
</style>
</head>
<body>
<?php

session_start();

//κενές μεταβλητές
$email = $password = $username = "";
$emailError = $passwordError = $loginError = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){


	$email = trim($_POST["email"]);
	$password = trim($_POST["password"]);

	//ελέγχει αν οι μεταβλητές είναι κενές
	if(empty($email)){
		$emailError = "Email required";
	}
	if(empty($password)){
		$passwordError = "Password required";
	}


	//αν οι μεταβλητές "μηνύματα σφάλματων" είναι κενα, δλδ αν δεν υπάρχουν σφάλματα προσπαθεί να συνδέθει
	if(empty($emailError)&& empty($passwordError)) {

		//καλούμε το αρχείο "connect_to_db.php" και συνδέεται στη βάση δεδομένων
		include "connect_to_db.php";

		$valid = false;
		$userRole = "";


		//τα prepared statements είναι ασφαλέστερα έναντιον των SQL injections
		$stmt = $conn->prepare("SELECT id,username,password FROM Students WHERE email= ?");
		$stmt-> bind_param("s", $email);
		$stmt-> execute();
		$result = $stmt->get_result();

		//αναζητά φοιτητή στη βάση δεδομένων

		if($result-> num_rows == 1){
			$row = $result->fetch_assoc();

			//χρησιμοποιήται το password_verify για τον έλεγχο με το hash του φοιτητή
			if(password_verify($password, $row["password"])){
				$valid = true;
				$userRole = "Student";

				//αποθηκεύεται  το id, username και ο ρόλος του φοιτητή στο session
				$_SESSION["user_id"]=$row["id"];
				$_SESSION["username"]=$row["username"];
				$_SESSION["role_id"]=$userRole;
			}
		}
		$stmt-> close();

		//αναζητά καθηγητή στη βάση δεδομένων
		if(!$valid){

			$stmt = $conn->prepare("SELECT id, username, password FROM Professors WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

			
			if($result-> num_rows ==1){
				$row = $result->fetch_assoc();

				//χρησιμοποιήται το password_verify για τον έλεγχο με το hash του καθηγητή
				if(password_verify($password, $row["password"])){
					$valid = true;
					$userRole = "Professor";

					//αποθηκεύεται  το id, username και ο ρόλος του καθηγητή στο session
					$_SESSION["user_id"]=$row["id"];
					$_SESSION["username"]=$row["username"];
					$_SESSION["role_id"]=$userRole;
				}
			}
			$stmt-> close();
		}


		//αν η σύνδεση είναι επτυχής, τότε αποθηκεύει το username στο session και κατευθύνει τον χρήστη στην σελίδα dashboard
		if($valid){

			header("Location: dashboard.php");
			exit();
		}else{
			$loginError = "Wrong email or password";
		}
		$conn->close();
	}
}
?>
<?php include "header.php";?>

<h1>Σελίδα Σύνδεσης</h1><br>
<form method="post" action="<?php echo $_SERVER["PHP_SELF"]?>">
<label>Email:</label><br>
<input type="text" name="email">  <span class="error"><?php echo $emailError; ?></span><br>
<label>Κωδικός:</label><br>
<input type="password" name="password"> <span class="error"><?php echo $passwordError; ?></span><br><br>

<span class="error"><?php echo $loginError; ?></span><br>


<input type="submit" value="Login">

</form>
<br><br>
<?php include "footer.php"?>

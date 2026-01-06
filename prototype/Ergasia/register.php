<?php

$usernameError = $emailError = $passwordError = $roleError = $regcodeError = $registrationError = "";
$username = $email = $password = $role = $regcode = "";
$registrationSuccessful = "";

if($_SERVER["REQUEST_METHOD"]==="POST"){


    //καθαρίζει τις εισόδους
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"] ?? "";
    $regcode = trim($_POST["regcode"]);


    //οι κένες μεταβλήτες οδηγούν σε μηνύματα σφάλματων
    if (empty($username)){
        $usernameError = "Username είναι υποχρεωτικό";
    }
    if (empty($email)){
        $emailError = "Το Email είναι υποχρεωτικό";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $emailError = "Εισάγετε μια έγκυρη διεύθυνση email";
    }
    if (empty($password)){
        $passwordError = "Ο κωδικός είναι υποχρεωτικός";
    }
    if (empty($role)){
        $roleError = "Επιλέξτε έναν ρόλο";
    }
    if (empty($regcode)) {
        $regcodeError = "Ο κωδικός εγγραφής είναι υποχρεωτικός";
    }


    if(empty($usernameError) && empty($emailError) && empty($passwordError) && empty($roleError)&& empty($regcodeError)){
        $valid = false;
        $table = "";

        //αν ο ρόλος και ο κώδικός εγγραφής είναι σωστοί εισάγει τον χρήστη στο κατάλληλο πίνακα
        if($role=="student" && $regcode=="STUD2025"){
            $valid = true;
            $table = "Students";
        }
        else if($role=="professor" && $regcode=="PROF2025"){
            $valid = true;
            $table = "Professors";
        }
        else{
            $regcodeError = "Λάθος κωδικός εγγραφής";
        }

        //αν όλα είναι σωστά, εισάγει τα στοιχεία του χρήστη στη βάση δεδομένων
        if($valid){

            //κρυπτογραφούμε τον κωδικό με την χρήση του hash
            $password = password_hash($password, PASSWORD_DEFAULT);

            require_once "connect_to_db.php";

            try{
                //τα prepared statements είναι πιο ασφαλή ενάντια SQL injections
                $sql = "INSERT INTO $table (username,email,password) VALUES (?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt-> bind_param("sss",$username, $email, $password);

                if ($stmt->execute()) {
                    $registrationSuccessful =  "Εγγραφή επιτυχής";
                } 
                $stmt-> close();
            }catch(mysqli_sql_exception $e){
                //έλεγχει αν υπάρχει duplicate entry στο email
                if($stmt-> errno===1062){
                    $emailError = "Αυτό το email χρησιμοποιείται ήδη";
                }else{
                    $registrationError= "Σφάλμα κατά την εγγραφή";
                }
            } 
            $conn-> close();
        }
    }
}
?>
<?php include "header.php";?>

<h1>Σελίδα Εγγραφής</h1><br>
<form method="post" action="<?php echo $_SERVER["PHP_SELF"]?>">
<span style="color: green; display: block; min-height: 1.4em;"><?php echo $registrationSuccessful; ?></span>
<span class="error"><?php echo $registrationError; ?></span>    
<label>Username:</label><br>
<input type="text" name="username"> <span class="error"><?php echo $usernameError; ?></span> <br>
<label>Email:</label><br>
<input type="text" name="email"> <span class="error"><?php echo $emailError; ?></span><br>
<label>Κωδικός:</label><br><input type="password" name="password"><br><br>
<label>Ρόλος:</label>


<div class="radio-group">
   <input type="radio" name="role" value="professor">Καθηγητής
<br>
    <input type="radio" name="role" value="student">Φοιτητής
</div> <span class="error"><?php echo $roleError; ?></span>
<br>
<label>Κωδικός Εγγραφής:</label><br>
<input type="text" name="regcode"> <span class="error"><?php echo $regcodeError; ?></span><br>
<br><br>
<input type="submit" value="Submit">
<br><br>
</form>

<?php include "footer.php"?>

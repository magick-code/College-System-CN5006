<?php
// έλεγχει αν ο χρήστης είναι καθηγητής
//χρησιμοποιείται require_once αντί για include επειδή το αρχείο είναι απαραίτητο να φορτώσει μια φορά

require_once "../check_role.php";
check("Professor");

// σύνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$professor_id = $_SESSION["user_id"];
$success ="";
$error= "";

//δημιουργία εργασίων
if($_SERVER["REQUEST_METHOD"]==="POST"){
    //υα δεδομένα από τη φόρμα αποθηκεύονται σε αυτές τις μεταβλήτες
    $course_id = $_POST["course_id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $due_date = $_POST["due_date"];
    //αν τα πεδία τίτλου, id μαθημάτος ή προθεμσία υποβολής είναι κένα εμφανίζει μυνήμα σφάλματος
    if(empty($title)||empty($course_id)||empty($due_date)){
        $error="Συμπληρώστε όλα τα  πεδία της φόρμας.";
    }else{
        //ελέγχει αν το μάθημα ανήκει στον καθηγητή που κάνει ανάρτηση εργασίας
        //αυτό αποτρέπει σε καθηγητή άλλου μαθήματος να ανάρτησει σε μάθημα που δεν του ανήκει
        $sql="SELECT course_id FROM Courses WHERE course_id = ? AND professor_id = ?";
        $check_stmt= $conn-> prepare($sql);
        $check_stmt-> bind_param("ii", $course_id, $professor_id);
        $check_stmt-> execute();
        $check_result= $check_stmt-> get_result();

        if($check_result-> num_rows>0){
            $sql="INSERT INTO Assignments(course_id, title, description, due_date) VALUES (?,?,?,?)";
            $stmt= $conn-> prepare($sql);
            $stmt-> bind_param("isss", $course_id, $title, $description, $due_date);
            if($stmt-> execute()){
                //το μήνυμα επιτυχίας αποθηκεύεται σε μεταβλητή session
                $_SESSION["success"]="Η εργασία αναρτήθηκε με επιτυχία!";
                //αποτρέπει την φόρμα από το να ξαναστάλει όταν κάνει ο χρήστης refresh την σελίδα
                header("Location: ".$_SERVER["PHP_SELF"]);
                exit();
            }else{
                $error="Error: ".$conn-> error;
            }$stmt-> close();
        }else{
            $error= "Δεν έχετε δικαίωμα ανάρτησης εργασίας σε αυτό το μάθημα.";
        }
        $check_stmt-> close();
    }
}


//επιλέγει τα μαθήματα του καθηγητή για το <select> της φόρμας
$sql_courses= "SELECT course_id, title FROM Courses WHERE professor_id = ?";
$stmt= $conn-> prepare($sql_courses);
$stmt-> bind_param("i", $professor_id);
$stmt-> execute();
$result_courses= $stmt-> get_result();
?>

<?php include "../header2.php";?>
<h1>Ανάρτηση Εργασίας</h1>

<?php
if(isset($_SESSION["success"])):?>
<div style="color:green">
<?php echo $_SESSION["success"];
//διαγράφεται το μήνυμα από το session επείδη χρειάζεται μόνο μια φορά (για εμφάνιση)
unset($_SESSION["success"]); ?>
</div>
<?php endif;?>

<?php if($error):?>
<div style="color:red">
<?php echo $error?>
</div>
<?php endif;?>


<form method="POST">
<label>Μάθημα:</label><br>
<select name="course_id" required style="">
<option>Επιλέξτε ένα μάθημα</option>

<?php
//εμφάνιζει τα μαθήματα του καθηγητή ώς <option> της φόρμας
if($result_courses-> num_rows>0){
    while($row= $result_courses-> fetch_assoc()){
        echo "<option value='".$row["course_id"]. "'>". htmlspecialchars($row["title"])."</option>";
    }
}
?>
</select>
<br><br>
<label>Τίτλος Εργασίας:</label><br>
<input type="text" name="title" required><br><br>
<label>Περιγραφή:</label><br>
<textarea name="description" rows="5" cols="25"></textarea><br><br>
<label>Προθεσμία Υποβολής:</label><br>
<input type="datetime-local" name="due_date" required><br><br>
<input type="submit" value="Ανάρτηση">
</form>

<?php include "../footer.php"; ?>

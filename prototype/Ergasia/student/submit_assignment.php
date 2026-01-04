<?php
// έλεγχει αν ο χρήστης είναι φοιτητής
require_once "../check_role.php";
check("Student");

// σύνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$success = "";
$error = "";

$student_id = $_SESSION["user_id"];
  
//ελέγχει αν υπάρχει μήνυμα επιτυχίας
if (isset($_SESSION["success"])) {
    $success = $_SESSION["success"]; 
    //διαγράφει το μήνυμα μετά την εμφάνιση
    unset($_SESSION["success"]);
}

if($_SERVER["REQUEST_METHOD"]==="POST"){
    //ο φάκελος στον οποίο θα αποθηκεύονται τα αρχεία
    $target_dir = "../uploads/";
    //ελέγχει αν επιλέχθηκε εργασία
    if(empty($_POST["assignment_id"])){
        $error = "Επιλέξτε εργασία.";
    }
    //ελέγχει αν επιλέχθηκε αρχείο
    elseif(empty($_FILES["file_to_upload"]["name"])){
        $error = "Επιλέξτε αρχείο.";
    }else{
        $assignment_id = $_POST["assignment_id"];
        $file = $_FILES["file_to_upload"];

        //παίρνει το αρχικό όνομα του αρχείου
        $original_name = basename($file["name"]);
        //παίρνει την κατάληξη του αρχείου
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $file_temp_name = $file["tmp_name"];
        //ελέγχει το MIME τύπο του αρχείου
        //ώστε να ανιχνεύει από αρχεία με λάθος κατάληξη
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_temp_name);
        finfo_close($finfo);        

        //έλεγχει την κατάληξη του αρχείου
        $allowed_extensions = ["pdf","doc","docx","txt"];
        //οι επιτρεπόμενοι MIME τύποι
        $allowed_mime_types = ["application/pdf","application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "text/plain"];

        if(!in_array($extension, $allowed_extensions)|| !in_array($mime, $allowed_mime_types)){ 
            $error = "Λάθος τύπος αρχείου.<br>Επιλέξτε αρχείο με κατάληξη .pdf, .doc, .docx ή .txt";
        }else{
            //επιλέγει το όνομα του φοιτητή και της εργασίας 
            $sql = "SELECT Students.username, Assignments.title
                FROM Students
                JOIN Assignments ON Assignments.assignment_id = ?
                WHERE Students.id = ?"; 
            $stmt_get_names = $conn->prepare($sql);
            $stmt_get_names-> bind_param("ii", $assignment_id, $student_id);
            $stmt_get_names-> execute();
            $result_name = $stmt_get_names-> get_result();
                
            if($row = $result_name-> fetch_assoc()){
                //καθαρίζει τα ονόματα από κενά και περίεργους χαρακτήρες
                $student_name = preg_replace("/[^a-zA-Z0-9α-ωΑ-Ω]/u", "_", $row["username"]);
                $course_title = preg_replace("/[^a-zA-Z0-9α-ωΑ-Ω]/u", "_", $row["title"]);
                //μεταονομάζει το αρχείο με μοναδικό όνομα
                //το ονομά είναι timestamp_ΌνομαΦοιτητή_ΤίτλοςΕργασίας.κατάληξη 
                $file_name = time() . "_" . $student_name. "_". $course_title . ".". $extension;
                $target_file = $target_dir . $file_name;
                //προσπαθεί να μετακινήσει το αρχείο από το temp φάκελο στο uploads
                if(move_uploaded_file($file["tmp_name"], $target_file)){
                    $sql="INSERT INTO Submissions(assignment_id, student_id, file_path, submission_date)
                        VALUES (?,?,?, NOW())  
                        ON DUPLICATE KEY UPDATE file_path = VALUES(file_path),
                        submission_date= NOW()"; 
                    $stmt_insert_submission = $conn->prepare($sql);
                    $stmt_insert_submission->bind_param("iis", $assignment_id, $student_id, $target_file);
                        
                    if($stmt_insert_submission->execute()){
                        $_SESSION["success"] = "Η εργασία υποβλήθηκε με επιτυχία!";
                        header("Location: ".$_SERVER["PHP_SELF"]);
                        exit();
                    }else{
                        $error ="Σφάλμα βάσης δεδομένων: ".$conn-> error;
                    }
                    $stmt_insert_submission-> close();
                }else{
                    $error="Σφάλμα κατά την αποθήκευση στον φάκελο uploads.";
                    }       
                }else{
                    $error = "Δεν βρέθηκαν στοιχεία για τον φοιτητή ή για την εργασία.";
                }
                $stmt_get_names-> close();
        } 
    } 
} 

//επιλέγει τις εργασίες των μαθημάτων που είναι εγγράμμενος ο φοιτητής
$sql = "SELECT Assignments.assignment_id, Assignments.title, Courses.title AS course_name
    FROM Assignments 
    JOIN Courses ON Assignments.course_id = Courses.course_id
    JOIN Enrollments ON Courses.course_id = Enrollments.course_id 
    WHERE Enrollments.student_id = ?";

$stmt_fetch_assignments = $conn-> prepare($sql);
$stmt_fetch_assignments-> bind_param("i", $student_id);
$stmt_fetch_assignments-> execute();
$result = $stmt_fetch_assignments-> get_result(); 
?>

<?php include "../header2.php"; ?> 

<h1>Υποβολή Εργασίας</h1>
<?php if($success):?>
    <span style="color:green;"><?php echo $success; ?></span>
<?php endif;?>
<?php if($error):?>
    <span style="color:red;"><?php echo $error; ?></span>
<?php endif;?>

<form method="post" enctype="multipart/form-data"> 
<label>Επιλέξτε Εργασία:</label><br>  
<select name="assignment_id" required>
<option value="">Επιλέξτε Εργασία</option>
<?php while($row=$result-> fetch_assoc()):?>  
<option value="<?= $row['assignment_id']?>">
    <?php echo htmlspecialchars($row["course_name"]. "-". $row["title"]) ?>
</option>
<?php endwhile;?>
</select>
<br><br>
<label>Επιλέξτε αρχεία για υποβολή:</label><br>
<input type="file" name="file_to_upload" required> 
<br><br>
<input type="submit" value="Υποβολή"> 
</form>

<?php 
$stmt_fetch_assignments->close();
include "../footer.php";
?>

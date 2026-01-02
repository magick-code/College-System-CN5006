<?php
// έλεγχει αν ο χρήστης είναι φοιτητής
require_once "../check_role.php";
check("Student");

// σύνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$success = "";
$error = "";
$student_id = $_SESSION["user_id"];
  
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success']; 
    unset($_SESSION['flash_success']);
}
if($_SERVER["REQUEST_METHOD"]==="POST"){
    //ο φάκελος στον οποίο θα αποθηκεύονται τα αρχεία
    $target_dir = "../uploads/";
    //ελέγχει αν επιλέχθηκε εργασία
    if(!isset($_POST["assignment_id"])|| empty($_POST["assignment_id"])){
        $error = "Επιλέξτε εργασία.";
    }
    //ελέγχει αν επιλέχθηκε αρχείο
    elseif(!isset($_FILES["file_to_upload"])|| empty($_FILES["file_to_upload"]["name"])){
        $error = "Επιλέξτε αρχείο.";
    }else{
        $assignment_id = $_POST["assignment_id"];
        $original_name= basename($_FILES["file_to_upload"]["name"]);
        
        $file_temp_name = $_FILES["file_to_upload"]["tmp_name"];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
        //έλεγχει την κατάληξη του αρχείου
        $allowed_extensions = ["pdf","doc","docx","txt"];  

        if(!in_array($extension, $allowed_extensions)){ 
            $error = "Λάθος τύπος αρχείου.<br>Επιλέξτε αρχείο με κατάληξη .pdf, .doc, .docx ή .txt";
        }else{
            //ελέγχει το MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file_temp_name); 
            finfo_close($finfo);

            $allowed_mime_types = ["application/pdf","application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "text/plain"];

            if(!in_array($mime, $allowed_mime_types)){
                $error = "Λάθος αρχείο.";
            }else{
                //επιλέγει το όνομα του φοιτητή και της εργασίας 
                $sql = "SELECT Students.username, Assignments.title
                    FROM Students, Assignments
                    WHERE Students.id = ? AND Assignments.assignment_id=?";
                
                $stmt_get_names = $conn->prepare($sql);
                $stmt_get_names-> bind_param("ii", $student_id, $assignment_id);
                $stmt_get_names-> execute();
                $result_name = $stmt_get_names-> get_result();
                
                if($result_name->num_rows>0){
                    $data = $result_name-> fetch_assoc();
                    //καθαρίζει τα ονόματα από κενά και περίεργους χαρακτήρες
                    $student_name = preg_replace("/[^a-zA-Z0-9α-ωΑ-Ω]/u", "_", $data["username"]);
                    $course_title = preg_replace("/[^a-zA-Z0-9α-ωΑ-Ω]/u", "_", $data["title"]);
                    
                    //δίνει το όνομα στο αρχείο που θα αποθηκεύτει στο /uploads/
                    $file_name = time() . "_" . $student_name. "_". $course_title . ".". $extension;
                    $target_file = $target_dir . $file_name;
                    if(empty($error) && move_uploaded_file($file_temp_name, $target_file)){
                        $sql="INSERT INTO Submissions(assignment_id, student_id, file_path, submission_date)
                            VALUES (?,?,?, NOW())  
                            ON DUPLICATE KEY UPDATE  
                            file_path = VALUES(file_path),
                            submission_date= NOW()"; 
                        $stmt_insert_submission = $conn->prepare($sql);
                        $stmt_insert_submission->bind_param("iis", $assignment_id, $student_id, $target_file);
                        
                        if($stmt_insert_submission->execute()){
                            $_SESSION['flash_success'] = "Η εργασία υποβλήθηκε με επιτυχία!";
                            header("Location: ".$_SERVER["PHP_SELF"]);
                            exit();
                        }else{
                            $error ="Error: ".$conn-> error;
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
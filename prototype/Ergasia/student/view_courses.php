<?php
// έλεγχει αν ο χρήστης είναι φοιτητής
//χρησιμοποιείται require_once αντί για include επειδή το αρχείο είναι απαραίτητο να φορτώσει μια φορά

require_once "../check_role.php";
check("Student");

// σύνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$student_id = $_SESSION["user_id"];
$success = "";
$error = "";

if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["course_id"])){
    $course_to_enroll= $_POST["course_id"];

    $sql="INSERT IGNORE INTO Enrollments(student_id, course_id) VALUES (?,?)";
    $stmt= $conn-> prepare($sql);
    $stmt-> bind_param("ii",$student_id, $course_to_enroll);

    if($stmt-> execute()){
        if($stmt-> affected_rows>0){
            $success="";
        }else{
            $error="";
        }
    }else{
        $error="". $conn->error;
    } $stmt-> close();
}
//παίρνει ολα τα στοιχεία των μαθημάτων μαζί με το όνομα του καθηγητή τους χρησιμοποιόντας το LEFT JOIN
$sql_courses = "SELECT Courses.course_id, Courses.title, Courses.description, 
    Professors.username AS professor_name
    FROM Courses
    LEFT JOIN Professors ON Courses.professor_id = Professors.id";
$result_courses = $conn-> query($sql_courses);
//βρίσκει τα μαθήματα πού είναι ήδη γραμμένος ο φοιτητής χρησιμοποιοντάς το LEFT JOIN
//αποθηκεύονται τα id των φοιτητών στο array για γρηγορότερο έλεγχο
$enrolled_courses = array();
$sql_enrollments = "SELECT course_id FROM Enrollments WHERE student_id = $student_id";
$result_enrollments = $conn-> query($sql_enrollments);

while($row= $result_enrollments-> fetch_assoc()){
    $enrolled_courses[] = $row["course_id"];
}
?>
<?php include "../header2.php" ?>
<h1>Διαθέσιμα Μαθήματα</h1>

<?php if($success):?>
<div style="color:green;"><?php echo $success;?></div>
<?php endif; ?>

<?php if($error):?>
<div style="color:red;"><?php echo $error;?></div>
<?php endif; ?>

<?php if($result_courses-> num_rows>0): ?>
<table>
    <thead>
        <tr>
            <th>Τίτλος Μαθήματος</th>
            <th>Καθηγητής</th>
            <th>Περιγραφή</th>
            <th>Κατάσταση</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row=$result_courses-> fetch_assoc()): ?>
        <?php
            $is_enrolled= in_array($row["course_id"],$enrolled_courses);
        ?>
        <tr>
            <td>
                <?php echo htmlspecialchars($row["title"]); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($row["professor_name"]); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($row["description"]); ?>
            </td>
            <td>
                <?php if($is_enrolled): ?>
                <span style="color:green;">Εγγεγραμμένος</span>
                <?php else: ?>
                <form method="post">
                <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                <input type="submit" value="Εγγραφή">
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else:?>
<h3 style="color:grey;">Δεν βρέθηκαν μαθήματα..</h3>
<?php endif;?>
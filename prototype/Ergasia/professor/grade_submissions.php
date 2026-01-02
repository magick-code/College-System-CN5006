<?php
// έλεγχει αν ο χρήστης είναι καθηγητής
//χρησιμοποιείται require_once αντί για include επειδή το αρχείο είναι απαραίτητο να φορτώσει μια φορά

require_once "../check_role.php";
check("Professor");

// σύνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$professor_id = $_SESSION["user_id"];
$success = "";
$error = "";

if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["save_grade"])){
    //το intval μετατρέπε την τιμή σε ακέραιο 
    $submission_id = intval($_POST["submission_id"]);
    //το floatval μετατρέπε την τιμή σε δεκαδικό 
    $grade = floatval($_POST["grade"]);
    //ελέγχει αν ο βαθμός είναι μικρότερος από το 0 ή μεγαλύτερος από το 100 
    //αν ναι εμφανίζει μήνυμα σφάλματος
    if($grade<0 || $grade >100){
        $error = "Ο βαθμός πρέπει να είναι από 0 έως 100.";
    }else{
        //ενημερώνει τον βαθμό
        $sql = "UPDATE Submissions SET grade=? WHERE submission_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt-> bind_param("di", $grade, $submission_id);
        if($stmt-> execute()){
            $success="Ο βαθμός καταχωρήθηκε με επιτυχία!";
        }else{
            $error="Σφάλμα στην καταχώρηση του βαθμού:".$conn-> error;
        }$stmt->close();
    }
}

/*
Συνέει την υποβολή με την εργασία,την εργασία με το μάθημα και την υποβολή με τον φοιτητή
Επιστρέφει μόνο υποβολές που ανήκουν στα μαθήματα του καθήγητη και αυτές που δεν είναι βαθμόλογημενες (δλδ ο βαθμός είναι NULL)
Η ταξινόμηση γίνεται από την πιο πρόσφατη στην παλαιότερη υποβολή
*/
$sql="SELECT Submissions.submission_id, Submissions.file_path,
    Submissions.submission_date,
    Students.username AS student_name,
    Students.email AS student_email,
    Assignments.assignment_id,
    Assignments.title AS assignment_title,
    Courses.course_id,
    Courses.title AS course_title
    FROM Submissions
    JOIN Assignments ON Submissions.assignment_id = Assignments.assignment_id
    JOIN Courses ON Assignments.course_id = Courses.course_id
    JOIN Students ON Submissions.student_id = Students.id
    WHERE Courses.professor_id = ? AND Submissions.grade IS NULL
    ORDER BY Submissions.submission_date DESC";
    $stmt = $conn-> prepare($sql);
    $stmt-> bind_param("i", $professor_id);
    $stmt-> execute();
    $result = $stmt-> get_result();
?>
<?php include "../header2.php";?>
<h1>Βαθμολόγηση Φοιτητών</h1>
<?php if($success):?>
    <div style="color:green;"><?php echo $success;?></div>
<?php endif;?>
<?php if($error):?>
    <div style="color:red;"><?php echo $error;?></div>
<?php endif;?>
<h2>Ο Αριθμός των Υποβολών για Βαθμολόγηση είναι: <?php echo $result-> num_rows;?></h2>
<?php if($result-> num_rows>0):?>
<table>
    <thead>
        <tr>
            <th>Εργασία</th>
            <th>Φοιτητής</th>
            <th>Ημερομηνία Υποβολής</th>
            <th>Αρχείο</th>
            <th>Βαθμολογία</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row=$result-> fetch_assoc()): ?>
        <tr>
        <td>
            <!--μάθημα-->
            <small style="color:grey;"><?php echo htmlspecialchars($row["course_title"]);?></small><br>
            <!--εργασία μαθήματος-->
            <?php echo htmlspecialchars($row["assignment_title"]);?>
        </td>
        <td>
            <?php echo htmlspecialchars($row["student_name"]);?>
        </td>
        <td>
            <?php
            $date = new DateTime($row["submission_date"]);
            echo $date-> format("d/m/Y H:i");
            ?> 
        </td>
        <td>
            <a href="<?php echo htmlspecialchars($row['file_path']);?>" download>
            Λήψη
            </a>
        </td>
        <td>
            <form method="POST">
            <input type="hidden" name="submission_id" value="<?php echo $row['submission_id'];?>">
            <input type="number" name="grade" min="0" max="100" step="0.5" required>
            <input type="submit" name="save_grade" value="Καταχώρηση">
            </form>
        </td>
        </tr>
        <?php endwhile;?>
    </tbody>
</table>
<?php else:?>
    <h3 style="color:grey;">Δεν  υπάρχουν υποβολές ακόμα..</h3>
<?php endif;?>

<?php
$stmt-> close();
$conn-> close();
include "../footer.php";
?>
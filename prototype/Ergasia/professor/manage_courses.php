<?php
//έλεγχει αν ο χρήστης είναι καθηγητής
//χρησιμοποιείται require_once αντί για include επειδή το αρχείο είναι απαραίτητο να φορτώσει μόνο μια φορά
require_once "../check_role.php";
check("Professor");

//συνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$professor_id = $_SESSION["user_id"];
$success ="";
$error= "";

//δημιουργία μάθηματός
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_course"])){
    //καθαρίζει τις ειδόδους
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    //αν ο χρήστης δεν συμπληρώσει το πεδίο του τιτλού εμφανίζεται σφάλμα
    if(empty($title)){
        $error = "Ο τίτλος μαθήματος είναι υποχρεωτικός";
    }else{
        $sql = "INSERT INTO Courses(title, description, professor_id) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt -> bind_param("ssi", $title, $description, $professor_id);

        if($stmt-> execute()){
            $success = "Το νέο μάθημα δημιουργήθηκε με επιτυχία!";
            
            //αποτρέπει την φόρμα από το να ξαναστάλει όταν κάνει ο χρήστης refresh την σελίδα
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit();
        }else{
            $error = "Error: ".$conn-> error;
        }
        $stmt->close();
    }
}

//διαγραφή μαθήματος
if(isset($_GET["delete"]) && is_numeric($_GET["delete"])){
    $course_id = intval($_GET["delete"]);

    //ελέγχει αν το μάθημα ανήκει στον καθήγητη που θέλει να το διαγράψει
    //ώστε να μην επιτρέπει την διαγραφή μαθήματος άλλου καθηγητή
    $sql = "DELETE FROM Courses WHERE course_id = ? AND professor_id= ?";
    $stmt = $conn-> prepare($sql);
    $stmt-> bind_param("ii", $course_id, $professor_id);

    if($stmt-> execute() && $stmt-> affected_rows>0){
        $success="Το μάθημα διαγράφηκε επιτυχώς!";
    }$stmt-> close();
}

//προβολή μαθήματων
$sql="SELECT Courses.course_id, Courses.title, Courses.description,
    COUNT(DISTINCT Enrollments.student_id) as student_count,
    COUNT(DISTINCT Assignments.assignment_id) as assignment_count
    FROM Courses
    LEFT JOIN Enrollments ON Courses.course_id = Enrollments.course_id
    LEFT JOIN Assignments ON Courses.course_id = Assignments.course_id
    WHERE Courses.professor_id = ?
    GROUP BY Courses.course_id, Courses.title, Courses.description
    ORDER BY Courses.course_id DESC ";

$stmt=$conn-> prepare($sql);
$stmt-> bind_param("i", $professor_id);
$stmt-> execute();
$result = $stmt-> get_result();
?>

 
<?php include "../header2.php";?>

<h1>Δημιουργία Μαθήματος</h1>

<form method="POST">
<label>Τίτλος:</label><br>
<input type="text" name="title" required><br><br>
<label>Περιγραφή:</label><br>
<textarea name="description" rows="3" cols="25"></textarea><br><br>
<input type="submit" name="create_course"><br><br>

<?php if($success):?>
<div style="color:green;"><?php echo htmlspecialchars($success);?></div>
<?php endif; ?>
<?php if($error):?>
<div style="color:red;"><?php echo htmlspecialchars($error);?></div>
<?php endif;?>

</form>

<!--ο κώδικας php βρίσκει τον αρίθμο των μαθημάτων επιστρέφοντάς τις στείλες του πίνακα μαθημάτων -->
<h2>Ο Αριθμός των Μαθημάτων σου είναι <?php echo $result-> num_rows;?></h2>
<?php if($result->num_rows>0):?>
<table>
    <thead>
        <tr>
            <th>Τίτλος</th>
            <th>Περιγραφή</th>
            <th>Φοιτητές</th>
            <th>Εργασίες</th>
            <th>Ενέργεια</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row=$result-> fetch_assoc()): ?>
        <tr>
        <td>
            <?php echo htmlspecialchars($row["title"]); ?>
        </td>
        <td>
            <?php 
            $desc = $row["description"]; 
            //αν η περιγραφή είναι κενή εμφανίζει -
            echo htmlspecialchars($desc ? $desc:"-");
            ?>
        </td>
        <td>
            <?php echo htmlspecialchars($row["student_count"]); ?>
        </td>
        <td>
            <?php echo htmlspecialchars($row["assignment_count"]); ?>
        </td>
        <td>
            <a href="?delete= <?php echo $row["course_id"]; ?>" class="delete_btn" onclick="return confirm('Επιθυμείτε να διαγράψεται το μάθημα και όλες τις εργάσιες του?');">
            Διαγραφή
            </a>
        </td>
        </tr>
        <?php endwhile;?>
    </tbody>
</table>
<?php else: ?>

<p style="color:grey;">Δεν υπάρχουν μαθήματα ακόμα..</p>
<?php endif; ?>

<?php 
$stmt-> close();
$conn-> close();
include "../footer.php";
?>

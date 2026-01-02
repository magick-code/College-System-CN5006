<?php
//έλεγχει αν ο χρήστης είναι καθηγητής
//χρησιμοποιείται require_once αντί για include επειδή το αρχείο είναι απαραίτητο να φορτώσει μόνο μια φορά
require_once "../check_role.php";
check("Professor");

//συνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$professor_id = $_SESSION["user_id"];

$sql="SELECT Submissions.submission_id, Submissions.file_path, 
    Submissions.submission_date, 
    Submissions.grade, 
    Students.username AS student_name,
    Assignments.title AS assignment_title,
    Courses.title AS course_title
    FROM Submissions 
    JOIN Assignments On Submissions.assignment_id = Assignments.assignment_id
    JOIN Courses On Assignments.course_id = Courses.course_id
    JOIN Students ON Submissions.student_id = Students.id
    WHERE Courses.professor_id = ?
    ORDER BY Submissions.submission_date DESC
";
$stmt=$conn-> prepare($sql);
$stmt-> bind_param("i", $professor_id);
$stmt-> execute();
$result = $stmt-> get_result();
?>

<?php include "../header2.php";?>
<h1>Υποβολές Φοιτητών</h1>
<h2>Ο Αριθμός τών Υποβολών είναι <?php echo $result->num_rows;?> μέχρι στιγμής</h2>

<?php if($result-> num_rows>0): ?>
<table>
    <thead>
        <tr>
            <th>Ημερομηνία</th>
            <th>Όνομα Φοιτητή</th>
            <th>Μάθημα</th>
            <th>Αρχείο</th>
            <th>Βαθμός</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result-> fetch_assoc()):?>
        <tr>
            <td>
                <!--ώρα-->
                <small style="color:grey;"><?php echo date("H:i A",strtotime($row["submission_date"]));?></small><br>
                <!--ημερομηνία-->
                <?php echo date("d/m/Y",strtotime($row["submission_date"]));?>
            </td>
            <td>
                <?php
                //εμφανίζει την ημέρομηνια σε string
                echo htmlspecialchars($row["student_name"]);
                ?>
            </td>
            <td>
                <!--μάθημα-->
                <small style="color:grey;"><?php echo htmlspecialchars($row["course_title"]);?></small><br>
                <!--εργασία μαθήματος-->
                <?php echo htmlspecialchars($row["assignment_title"]);?>
            </td>
            <td>
                <a href="<?php echo htmlspecialchars($row["file_path"]);?>" download>
                Λήψη
                </a>
            </td>
            <td>
                <?php
                //όταν ο βαθμός δέν έχει δωθεί εμφανίζει -
                if($row["grade"]===NULL){
                    echo "<span style='color:grey;'>-</span>";
                }else{
                    //όταν ο βαθμός είναι μεγαλύτερος από την βάση (δλδ 50) εμφανίζεται σε πράσινο χρώμα
                    //όταν είναι μικρότερος απο την βάση εμφανίζεται σε κόκκινο χρώμα
                    $color = ($row["grade"]< 50)? "red" : "green";
                    echo "<span style='color:$color;'>". $row["grade"]."/100</span>";
                }
                ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <h3>Δεν  υπάρχουν υποβολές ακόμα..</h3>
<?php endif;?>

<?php include "../footer.php";
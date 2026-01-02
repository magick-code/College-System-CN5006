<?php
// έλεγχει αν ο χρήστης είναι φοιτητής
//χρησιμοποιείται require_once αντί για include επειδή το αρχείο είναι απαραίτητο να φορτώσει μια φορά

require_once "../check_role.php";
check("Student");

// σύνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$student_id = $_SESSION["user_id"];

$sql = "SELECT Assignments.assignment_id,
    Assignments.title,
    Assignments.description,
    Assignments.due_date,
    Courses.title AS course_title,
    Submissions.submission_id,
    Submissions.grade,
    Submissions.submission_date
    FROM Assignments
    JOIN Courses ON Assignments.course_id = Courses.course_id
    JOIN Enrollments On Courses.course_id = Enrollments.course_id
    LEFT JOIN Submissions ON Assignments.assignment_id = Submissions.assignment_id AND Submissions.student_id = ?
    WHERE Enrollments.student_id =?
    ORDER BY Assignments.due_date ASC";

$stmt= $conn-> prepare($sql);
$stmt-> bind_param("ii",$student_id, $student_id);
$stmt-> execute();
$result=$stmt-> get_result();
?>
<?php include "../header2.php";?>
<h1>Οι Εργασίες μου</h1>
<?php if($result-> num_rows>0): ?>
<table>
    <thead>
        <tr>
            <th>Μάθημα</th>
            <th>Τίτλος Εργασίας</th>
            <th>Περιγραφή</th>
            <th>Προθεσμία</th>
            <th>Κατάσταση</th>
            <th>Βαθμός</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row=$result-> fetch_assoc()): ?>
        <?php
            $due_date = strtotime($row["due_date"]);
            $is_submitted = !empty($row["submission_id"]);
            $is_overdue = (time()>$due_date) && !$is_submitted;
        ?>
        <tr>
            <td>
                <?php echo htmlspecialchars($row["course_title"]); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($row["title"]); ?>
            </td>
            <td>
            <small>
                <?php echo htmlspecialchars($row["description"]? $row["description"] : "-"); ?>
            </small>
            </td>
            <td>
                <?php echo date("d/m/Y", $due_date);?>
                <?php if($is_overdue) echo "<br><small>Έχει λήξει..";?>
            </td>
            <td>
                <?php if($is_submitted): ?>
                <span style="color:green;">Υποβλήθηκε</span><br>
                <small><?php echo date("d/m/Y", strtotime($row["submission_date"]))?></small>
                <?php elseif($is_overdue): ?>
                <span style="color:red;">Ληξιπρόθεσμη</span>
                <?php else: ?>
                <span style="color:orange;">Εκκρεμεί</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if(isset($row["grade"])): ?>
                    <?php echo $row["grade"];?>/100
                <?php else: ?>
                    <span style="color:grey;">-</span>
                <?php endif;?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>


<?php else:?>
    <h3>Δεν υπάρχουν εργασίες..</h3>
<?php endif;?>

<?php include "../footer.php"; 
$stmt-> close();
$conn-> close();
?>
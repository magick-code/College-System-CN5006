<?php
// έλεγχει αν ο χρήστης είναι φοιτητής
//χρησιμοποιείται require_once αντί για include επειδή το αρχείο είναι απαραίτητο να φορτώσει μια φορά
require_once "../check_role.php";
check("Student");

// σύνδέεται με τη βάση δεδομένων
require_once "../connect_to_db.php";

$student_id= $_SESSION["user_id"];

$sql = "SELECT Submissions.submission_date, Submissions.grade, Submissions.file_path,
    Assignments.title AS assignment_title,
    Courses.title AS course_title
    FROM Submissions 
    JOIN Assignments ON Submissions.assignment_id = Assignments.assignment_id
    JOIN Courses ON Assignments.course_id = Courses.course_id
    WHERE Submissions.student_id = ?
    ORDER BY Submissions.submission_date DESC";
    $stmt = $conn-> prepare($sql);
    $stmt-> bind_param("i",$student_id);
    $stmt-> execute();
    $result = $stmt-> get_result();

    $total_grade =0;
    $graded_count = 0;
    $rows=[];
    while($row=$result-> fetch_assoc()){
        $rows[]=$row;
        if($row["grade"]!==NULL){
            $total_grade += $row["grade"];
            $graded_count++;
        }
    }
    $average=($graded_count>0)? round($total_grade/$graded_count, 1):0;
?>

<?php include "../header2.php";?>
<h1>Οι Βαθμοί μου</h2>
<h2>Μέσος Όρος:</h2>
<?php
    //αν μέσος όρος είναι μεγαλύτερος από την βάση (δλδ το 50) εμφανίζεται σε πράσινο χρώμα
    //όταν είναι μικρότερος απο την βάση εμφανίζεται σε κόκκινο χρώμα
    $color = ($average< 50)? "red" : "green";
    echo "<h2 style='color:$color;'>". $average."</h2>";
?>



</h2>

<?php if(count($rows)>0): ?>
<table>
    <thead>
        <tr>
            <th>Μάθημα</th>
            <th>Εργασία</th>
            <th>Ημερομηνία Υποβολής</th>
            <th>Αρχείο</th>
            <th>Βαθμός</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($rows as $row):?>
        <tr>
            <td>
            <?php echo htmlspecialchars($row["course_title"]);?>
            </td>
            <td>
            <?php echo htmlspecialchars($row["assignment_title"]);?>
            </td>
            <td>
            <small style="color:grey;"><?php echo date("H:i A",strtotime($row["submission_date"]));?></small><br>
            <?php echo date("d/m/Y",strtotime($row["submission_date"]));?>
            </td>
            <td>
            <a href="<?php echo htmlspecialchars($row['file_path']);?>" download>Λήψη</a>
            </td>
            <td>
            <?php if($row["grade"]===NULL):?>
            <span style="color:grey;">-</span>
            <?php else:?>
                <?php
                //αν βαθμός είναι μεγαλύτερος από την βάση (δλδ το 50) εμφανίζεται σε πράσινο χρώμα
                //όταν είναι μικρότερος απο την βάση εμφανίζεται σε κόκκινο χρώμα
                $color = ($row["grade"]< 50)? "red" : "green";
                echo "<span style='color:$color;'>". $row["grade"]."/100</span>";
                ?>
            <?php endif;?>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<?php else:?>
<h3>Δεν δοθεί βαθμολογίες ακόμα..</h3>
<?php endif;?>
<?php include "../footer.php";?>

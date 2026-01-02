<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "schoolDB";

    //συνδέεται χωρίς να επιλέξει βάση δεδομένων
    $conn = new mysqli($servername, $username, $password);
    if($conn->connect_error){
        die("Σφάλμα στην σύνδεση: ".$conn->connect_error);
    } echo "Η σύνδεση ήταν επιτυχής<br>";

    //δημιουργεί την βάση δεδομένων
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Η Βάση Δεδομένων δημιουργήθηκε επιτυχώς<br>";
    } else {
        echo "Σφάλμα στην δημιουργία της Βάση Δεδομένων: " . $conn->error."<br>";
    }
    $conn->close();

    //συνδέεται στη βάση
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Σφάλμα στην σύνδεση: ". $conn->connect_error . "<br>");
    }
    mysqli_set_charset($conn, "utf8");

    //δημιουργεί πίνακα φοιτητών
    $sql = "CREATE TABLE IF NOT EXISTS Students (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL )" ;

        if ($conn->query($sql) === TRUE) {
            echo "Ο πίνακας των φοιτητών δημιουργήθηκε επιτυχώς <br>";
        } else {
            echo "Σφάλμα στην δημιουργία του πίνακα φοιτητών: " . $conn->error."<br>";
            }



    //δημιουργεί πίνακα καθηγητών
    $sql = "CREATE TABLE IF NOT EXISTS Professors (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL)";

        if ($conn->query($sql) === TRUE) {
            echo "Ο πίνακας των καθηγητών δημιουργήθηκε επιτυχώς<br>";
        } else {
            echo "Σφάλμα στην δημιουργία του πίνακα καθηγητών: " . $conn->error;
            }


    //ΔΕΥΤΕΡΟ ΜΕΡΟΣ

    //δημιουργεί πίνακα μαθημάτων
    $sql = "CREATE TABLE IF NOT EXISTS Courses(
        course_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        professor_id INT(6) UNSIGNED,
        FOREIGN KEY (professor_id) REFERENCES Professors(id) ON DELETE CASCADE
        )";

    if ($conn->query($sql) === TRUE) {
            echo "Ο πίνακας των μαθημάτων δημιουργήθηκε επιτυχώς<br>";
        } else {
            echo "Σφάλμα στην δημιουργία του πίνακα μαθημάτων: " . $conn->error;
            }
    //δημιουργεί πίνακα εγγραφών
    $sql = "CREATE TABLE IF NOT EXISTS Enrollments(
        enrollment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_id INT(10) UNSIGNED,
        course_id INT UNSIGNED,
        enrollment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES Students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES Courses(course_id) ON DELETE CASCADE,
        UNIQUE KEY unique_enrollment (student_id, course_id)
        )";

    if ($conn->query($sql) === TRUE) {
            echo "Ο πίνακας των εγγραφών δημιουργήθηκε επιτυχώς<br>";
        } else {
            echo "Σφάλμα στην δημιουργία του πίνακα εγγραφών: " . $conn->error;
            }

    //δημιουργεί πίνακα εργασιών
    $sql = "CREATE TABLE IF NOT EXISTS Assignments(
        assignment_id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        course_id INT UNSIGNED,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        due_date DATETIME,
        FOREIGN KEY(course_id) REFERENCES Courses(course_id) ON DELETE CASCADE
        )";

    if ($conn->query($sql) === TRUE) {
            echo "Ο πίνακας των εργασιών δημιουργήθηκε επιτυχώς<br>";
        } else {
            echo "Σφάλμα στην δημιουργία του πίνακα εργασιών: " . $conn->error;
            }

    //δημιουργεί πίνακα υποβολών
    $sql = "CREATE TABLE IF NOT EXISTS Submissions(
        submission_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        assignment_id INT(10) UNSIGNED,
        student_id INT(10) UNSIGNED,
        file_path VARCHAR(255) NOT NULL,
        grade DECIMAL (5,2) DEFAULT NULL,
        submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (assignment_id) REFERENCES Assignments(assignment_id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES Students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_submission (assignment_id, student_id)
        )";

    if ($conn->query($sql) === TRUE) {
            echo "Ο πίνακας των υποβολών δημιουργήθηκε επιτυχώς<br>";
        } else {
            echo "Σφάλμα στην δημιουργία του πίνακα υποβολών: " . $conn->error;
            }


    $conn->close();
    echo "Όλα είναι έτοιμα! <br>";


?>

<a href="index.php">Αρχική Σελίδα</a>

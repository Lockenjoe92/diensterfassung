<?php
// Initialize the session
session_start();

// Fetch requirements
require_once "./tools/permission_checker.php";
require_once "./forms/diensterfassung.php";

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
} else {
    // Catch mislead users
    permission_checker_with_redirect('team');
}

// Fetch Form Elements
$DiensttypDropdown = dropdown_diensttypen('diensttyp');
$FormTable = diensterfassung_table_form_element();


#    require_once "./configs/db_config.php";

?>



<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neuen Dienst erfassen</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Dienst erfassen</h2>
    <p>Bitte fülle das Formular aus um einen neuen Dienst zu erfassen.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <?php echo $DiensttypDropdown; ?>
        </div>
        <div class="form-group">
            <label>Datum</label>
            <input type="date" name="datum" class="form-control <?php echo (!empty($vorname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $vorname; ?>">
            <span class="invalid-feedback"><?php echo $vorname_err; ?></span>
        </div>

        <?php echo $FormTable; ?>

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Absenden">
            <input type="reset" class="btn btn-secondary ml-2" value="Reset">
        </div>
        <p>Falsch hier? <a href="dashboard.php">Hier gehts zurück</a>.</p>
    </form>
</div>
</body>
</html>

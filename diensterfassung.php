<?php
// Initialize the session
session_start();

// Fetch requirements
require_once "./tools/permission_checker.php";
require_once "./forms/diensterfassung.php";
require_once "./configs/db_config.php";

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
} else {
    // Catch mislead users
    permission_checker_with_redirect('team');
}

$ParserOutput = diensterfassung_form_parser($mysqli,120);

$KommentarOben = $ParserOutput['kommentar'];
$FormInputs = $ParserOutput['form_inputs'];
$FormButtons = $ParserOutput['form_buttons'];

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
    <p><?php echo $KommentarOben; ?></p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <?php echo $FormInputs; ?>
        <?php echo $FormButtons; ?>

        <p>Abbrechen? <a href="dashboard.php">Hier gehts zurück</a>.</p>
    </form>
</div>
</body>
</html>

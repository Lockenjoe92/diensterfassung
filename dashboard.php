<?php
// Initialize the session
session_start();

// Load dependencies
require_once "configs/db_config.php";
require_once "tools/status_bar_funktionen.php";
require_once "configs/settings_config.php";

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Load Admin-View Buttons
require_once "./tools/permission_checker.php";
if(permission_checker_boolean('admin')){
    $adminFunctions = "<p><a href='dienst-anlegen.php' class='btn btn-dark'>Diensttyp anlegen</a></p>";
} else {
    $adminFunctions = "";
}

// Load Team-View Buttons
if(permission_checker_boolean('team')){
    $teamFunctions = "<p><a href='diensterfassung.php' class='btn btn-dark'>Dienst erfassen</a><a href='erfassungsuebersicht.php' class='btn btn-dark ml-3'>Übersicht Erfassungsstand</a></p>";
} else {
    $teamFunctions = "";
}

// Load general status-bar
$GesamtDienste = gesamt_dienste_statustool($mysqli, 'nums', DATEBEGINERFASSUNG, DATEENDEERFASSUNG);
$GesamtVollstaendingkeit = gesamt_dienste_statustool($mysqli, 'vollstaendigkeit', DATEBEGINERFASSUNG, DATEENDEERFASSUNG);
$GeneralStatusBar = "<div class='container-sm'><table class='table table-bordered'><thead><tr><th scope='col'>Gesamtzahl erfasste Dienste</th><th scope='col'>Vollständigkeit erfasste Dienste</th></tr></thead><tbody><tr><td>".$GesamtDienste."</td><td>".$GesamtVollstaendingkeit."%</td></tr></tbody></table></div>";

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; text-align: center;}
    </style>
</head>
<body>
<h1 class="my-5">Hi, <b><?php echo htmlspecialchars($_SESSION["vorname"]); ?></b>.<br>Willkommen auf unserer Bereitschaftsdiensterfassungsseite.</h1>
<?php echo $GeneralStatusBar; ?>
<?php echo $adminFunctions; ?>
<?php echo $teamFunctions; ?>
<p>
    <a href="./reset_password.php" class="btn btn-warning">Passwort ändern</a>
    <a href="./logout.php" class="btn btn-danger ml-3">Ausloggen</a>
</p>
</body>
</html>
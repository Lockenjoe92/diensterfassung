<?php
// Initialize the session
session_start();

// load dependencies
require_once "./tools/permission_checker.php";
require_once "tools/uebersichten.php";
require_once "forms/uebersichten.php";
require_once "configs/db_config.php";

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
} else {
    // Catch mislead users
    permission_checker_with_redirect('team');
}

// Load data from Parser
$ParserOutput = parser_erfassunguebersicht($mysqli);

?>



<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Erfassungsübersicht</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Erfassungsübersicht</h2>

    <p>Hier entsteht eine tabellarische Übersicht über den Erfassungsstand der Bereitschaftsdienste.</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <?php echo $ParserOutput['filterForm']; ?>
        <?php echo $ParserOutput['table']; ?>

        <p>Abbrechen? <a href="dashboard.php">Hier gehts zurück</a>.</p>
    </form>
</div>
</body>
</html>
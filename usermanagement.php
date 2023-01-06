<?php
// Initialize the session
session_start();

// Fetch requirements
require_once "./tools/permission_checker.php";
require_once "./forms/userwesen.php";
require_once "./configs/db_config.php";

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
} else {
    // Catch mislead users
    permission_checker_with_redirect('admin');
}

$Table = usermanagement_table($mysqli);

?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Usermanagement</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Usermanagement</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <?php echo $Table; ?>

        <p>Abbrechen? <a href="dashboard.php">Hier gehts zurück</a>.</p>
    </form>
</div>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>

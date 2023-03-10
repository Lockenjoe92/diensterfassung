<?php
// Include config file
require_once "./configs/db_config.php";
require_once "./tools/site_body.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = $vorname = "";
$username_err = $password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate Users Name
    if(empty(trim($_POST["vorname"]))){
        $vorname_err = "Bitte gib einen Vornamen an!";
    } else {
        $vorname = trim($_POST["vorname"]);
    }

    if(empty(trim($_POST["nachname"]))){
        $nachname_err = "Bitte gib einen Nachnamen an!";
    } else {
        $nachname = trim($_POST["nachname"]);
    }

    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Bitte gib einen Nutzernamen an!";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Nutzername kann nur Buchstaben, Nummern und Unterstriche enthalten.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";

        if($stmt = $mysqli->prepare($sql)){
            // Set parameters
            $param_username = trim($_POST["username"]);

            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);

            // Attempt to execute the prepared statement
            if($stmt->execute()){

                // store result
                $stmt->store_result();

                if($stmt->num_rows == 1){
                    $username_err = "Dieser Nutzrname ist bereits vergeben.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Da ist etwas schiefgegangen Bitte versuche es ein anderes Mal.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Bitte gib ein Passwort ein.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Passwort muss mindestens 6 Zeichen enthalten.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Bitte best??tige dein Passwort.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Passw??rter stimmen nicht ??berein.";
        }
    }

    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($vorname_err) && empty($nachname_err)){

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, vorname, nachname, nutzergruppen) VALUES (?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){

            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $param_username, $param_password, $param_vorname, $param_nachname, $param_groups);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_groups = 'nutzer,team';
            $param_vorname = $vorname;
            $param_nachname = $nachname;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Redirect to login page
                header("location: login.php");
            } else{
                echo "Fehler beim Datenbankzugriff";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
<?php
echo nav_bar('not-logged-in');
?>
<div class="wrapper">
    <h2>Anmeldung</h2>
    <p>Bitte f??lle das Formular aus um ein Nutzerkonto anzulegen.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Nutzername</label>
            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
            <span class="invalid-feedback"><?php echo $username_err; ?></span>
        </div>
        <div class="form-group">
            <label>Vorname</label>
            <input type="text" name="vorname" class="form-control <?php echo (!empty($vorname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $vorname; ?>">
            <span class="invalid-feedback"><?php echo $vorname_err; ?></span>
        </div>
        <div class="form-group">
            <label>Nachname</label>
            <input type="text" name="nachname" class="form-control <?php echo (!empty($nachname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nachname; ?>">
            <span class="invalid-feedback"><?php echo $nachname_err; ?></span>
        </div>
        <div class="form-group">
            <label>Passwort</label>
            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <label>Passwort Best??tigen</label>
            <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Absenden">
            <input type="reset" class="btn btn-secondary ml-2" value="Reset">
        </div>
        <p>Du hast schon ein Account? <a href="login.php">Hier einloggen</a>.</p>
    </form>
</div>
</body>
</html>
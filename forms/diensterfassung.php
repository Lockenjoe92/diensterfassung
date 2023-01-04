<?php
function diensterfassung_table_form_element(){

    // Define Dienstzeiten Types
    $Dienstzeiten = ['Bereitschaft', 'Arbeit'];

    // initialize table element
    $Output = "<table class='table table-borderless'>";

    // Render Table header
    $Header = "<thead>";
    $Header .= "<tr>";
    $Header .= "<th scope='col'>Typ</th>";
    for($a=0;$a<=23;$a++){
        $Header .= "<th scope='col'>".$a." - ".($a+1)."</th>";
    }
    $Header .= "</tr>";
    $Header .= "</thead>";

    // Render Table Body
    $Body = "<tbody>";
    foreach ($Dienstzeiten as $Dienstzeit){
        $Body .= "<tr>";
        $Body .= "<th scope='row'>".$Dienstzeit."</th>";
        for($a=0;$a<=23;$a++){
            $Body .= "<td>o</td>";
        }
        $Body .= "</tr>";
    }
    $Body .= "</tbody>";

    // close table element
    $Output .= $Header;
    $Output .= $Body;
    $Output .= "</table>";

    return $Output;
}
function dropdown_diensttypen($name, $day, $mysqli, $selected='', $disabled=''){

    require_once "./configs/db_config.php";
    $Output = "<select class='custom-select' name='".$name."' ".$disabled.">";
    if(empty($selected)){
        $Output .= "<option selected>Bitte Diensttyp wählen</option>";
    } else {
        $Output .= "<option>Bitte Diensttyp wählen</option>";
    }

    // Prepare a select statement
    $sql = "SELECT id, dienstname, von, bis, diensttage FROM diensttypen ORDER BY dienstname ASC";
    if($stmt = $mysqli->query($sql)){

            while ($row = $stmt->fetch_assoc()) {

                if(in_array($day, explode(',',$row['diensttage']))){
                    if($selected==$row["id"]){
                        $Output .= '<option value="'.$row["id"].'" selected>'.$row["dienstname"].'</option>';
                    } else {
                        $Output .= '<option value="'.$row["id"].'">'.$row["dienstname"].'</option>';
                    }
                }
            }
    }

    $Output .= "</select>";
    return $Output;
}
function diensterfassung_form_parser($mysqli){

    //Initialize dependencies
    require_once "./configs/db_config.php";

    var_dump($_POST);

    // Initialize Ouputs
    $ParserOutput['kommentar'] = '';
    $ParserOutput['form_inputs'] = '';
    $ParserOutput['form_buttons'] = '';

    // Step 1 - no POST active
    if(empty($_POST)){
        $ParserOutput['kommentar'] = 'Um einen neuen Dienst zu erfassen, fülle das Formular Schritt für Schritt aus.';
        //Form inputs
        $ParserOutput['form_inputs'] = '<div class="form-group"><label>Datum</label><input type="date" name="datum"></div>';
        //Form Buttons
        $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step1"><input type="submit" class="btn btn-secondary ml-2" value="Reset" name="reset1"></div>';
    } elseif (isset($_POST['reset2'])){
        $ParserOutput['kommentar'] = 'Um einen neuen Dienst zu erfassen, fülle das Formular Schritt für Schritt aus.';
        //Form inputs
        $ParserOutput['form_inputs'] = '<div class="form-group"><label>Datum</label><input type="date" name="datum" value="'.$_POST['datum'].'"></div>';
        //Form Buttons
        $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step1"><input type="submit" class="btn btn-secondary ml-2" value="Reset" name="reset1"></div>';
    }

    // Step 2 - step1 is activated
    if(isset($_POST['step1'])){

        // Initialize Error Handling
        $ErrCount = 0;
        $ErrMess = "";

        // Catch empty inputs
        if(empty($_POST['datum'])){
            $ErrCount++;
            $ErrMess .= "Bitte wähle ein Datum aus!";
        }

        if($ErrCount>0){

            $ParserOutput['kommentar'] = 'Um einen neuen Dienst zu erfassen, fülle das Formular Schritt für Schritt aus.';
            //Form inputs
            $ParserOutput['form_inputs'] = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-invalid" value=""><span class="invalid-feedback">'.$ErrMess.'</span></div>';
            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="step1"><input type="submit" class="btn btn-secondary ml-2" value="reset1"></div>';

        } else {

            $ParserOutput['kommentar'] = 'Schritt 2: Bitte wähle den zu erfassenden Diensttyp aus.';

            //Form inputs
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            # Parse Date 2 weekday
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group">'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli).'</div>';
            $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";

            $ParserOutput['form_inputs'] = $Inputs;

            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step2"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset2"></div>';

        }

    }

    return $ParserOutput;

}
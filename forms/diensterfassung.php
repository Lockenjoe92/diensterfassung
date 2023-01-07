<?php
function diensterfassung_table_form_element($mysqli, $Datum, $Diensttyp, $ProtocolText, $granulationMins){

    // Fetch dependencies
    require_once "tools/time_stuff.php";

    // Define Dienstzeiten Types
    $Dienstzeiten = ['Bereitschaft', 'Arbeit'];

    // Load Diensttyp Meta and calculate table-boundaries
    $DiensttypMeta = lade_diensttyp_meta($mysqli, $Diensttyp);
    $DiensttypMetaVon = $DiensttypMeta['von'];
    $DiensttypMetaBis = $DiensttypMeta['bis'];
    $StartTimeString = $EndTimeString = date('Y-m-d G:i:s');

        // Case 1 - von < bis -> same-day
        if(strtotime($DiensttypMetaVon)<strtotime($DiensttypMetaBis)){
            $StartTimeString = $Datum.' '.$DiensttypMetaVon;
            $EndTimeString = $Datum.' '.$DiensttypMetaBis;
        }

        // Case 2 - von > bis -> overnight
        if(strtotime($DiensttypMetaVon)>strtotime($DiensttypMetaBis)){
            $StartTimeString = $Datum.' '.$DiensttypMetaVon;
            $EndTimeString = date('Y-m-d', strtotime('+1 day',strtotime($Datum))).' '.$DiensttypMetaBis;
        }

        // Make them time()-objects
        $StartTimeObj = strtotime($StartTimeString);
        $EndTimeObj = strtotime($EndTimeString);

        // Calculate necessary runs for Table-generator
        $NecessaryRuns = calculate_total_mins_diff($StartTimeObj, $EndTimeObj)/$granulationMins;

    // initialize table element
    $Output = "<table class='table table-borderless align-middle'>";

    // Render Table header
    $Header = "<thead>";
    $Header .= "<tr>";
    $Header .= "<th scope='col'>Zeitraum</th>";
    $Header .= "<th scope='col'>Arbeits-/Bereitschaftszeit</th>";
    $Header .= "</tr>";
    $Header .= "</thead>";

    // Render Table Body
    $Body = "<tbody>";
    for($a=0;$a<$NecessaryRuns;$a++){

        $Body .= "<tr>";

        // Calculate displayed times
        $CurrentCellTimeMinutesOperator = '+'.$a*$granulationMins.' minutes';
        $CurrentCellTimeMinutesOperatorEnd = '+'.(($a*$granulationMins)+$granulationMins).' minutes';
        $CurrentCellTimeBegin = date('G:i', strtotime($CurrentCellTimeMinutesOperator, $StartTimeObj));
        $CurrentCellTimeEnd = date('G:i', strtotime($CurrentCellTimeMinutesOperatorEnd, $StartTimeObj));

        $DienstzeitDisplay = $CurrentCellTimeBegin." - ".$CurrentCellTimeEnd;
        $Body .= "<th scope='row'>".$DienstzeitDisplay."</th>";
        $Body .= "<td>";

        $b = 1;
        foreach ($Dienstzeiten as $Dienstzeit){

            // calculate checkboxes
            $radioButtonID = $b.'-'.($a+1);
            $search='';
            if($Dienstzeit=='Bereitschaft'){
                $search = ($a+1).':bd';
            }elseif ($Dienstzeit=='Arbeit'){
                $search = ($a+1).':az';
            }

            if(!empty($ProtocolText)){
                if(in_array($search,explode(',',$ProtocolText))){
                    $checked = 'checked';
                } else {
                    $checked = '';
                }
            } else {
                $checked = '';
            }

            $Body .= '<div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="inlineRadioOptions'.$radioButtonID.'" id="inlineRadio'.$radioButtonID.'" value="option'.$radioButtonID.'" '.$checked.'><label class="form-check-label" for="inlineRadio'.$radioButtonID.'">'.$Dienstzeit.'</label></div>';
            $b++;
        }

        $Body .= "</td>";
        $Body .= "</tr>";
    }
    $Body .= "</tbody>";

    // close table element
    $Output .= $Header;
    $Output .= $Body;
    $Output .= "</table>";

    return $Output;
}
function dropdown_diensttypen($name, $day, $mysqli, $selected='', $disabled='', $valid='', $datum=''){

    require_once "./configs/db_config.php";
    $Output = "<select class='custom-select ".$valid."' name='".$name."' ".$disabled.">";
    if(empty($selected)){
        $Output .= "<option value='' selected>Bitte Diensttyp wählen</option>";
    } else {
        $Output .= "<option value=''>Bitte Diensttyp wählen</option>";
    }

    // Prepare a select statement
    $sql = "SELECT id, dienstname, von, bis, diensttage FROM diensttypen ORDER BY dienstname ASC";
    if($stmt = $mysqli->query($sql)){

            while ($row = $stmt->fetch_assoc()) {

                if(in_array($day, explode(',',$row['diensttage']))){
                    if(!empty($datum)){
                        if(!dienst_schon_eingetragen($mysqli, $datum, $row["id"])){
                            if($selected==$row["id"]){
                                $Output .= '<option value="'.$row["id"].'" selected>'.$row["dienstname"].'</option>';
                            } else {
                                $Output .= '<option value="'.$row["id"].'">'.$row["dienstname"].'</option>';
                            }
                        }
                    } else {
                        if($selected==$row["id"]){
                            $Output .= '<option value="'.$row["id"].'" selected>'.$row["dienstname"].'</option>';
                        } else {
                            $Output .= '<option value="'.$row["id"].'">'.$row["dienstname"].'</option>';
                        }
                    }
                }
            }
    }

    $Output .= "</select>";
    return $Output;
}
function diensterfassung_form_parser($mysqli){

    //Initialize dependencies
    require_once "configs/db_config.php";
    require_once "configs/settings_config.php";
    require_once "tools/dienste_funktionen.php";
    require_once "tools/time_stuff.php";
    setlocale(LC_ALL, 'de_DE');

    #dump($_POST);

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
    }

    // Step 2 - step1 is activated -> chose date
    if(isset($_POST['step1'])){

        // Initialize Error Handling
        $ErrCount = 0;
        $ErrMess = "";

        // Catch empty inputs
        if(empty($_POST['datum'])){
            $ErrCount++;
            $ErrMess .= "Bitte wähle ein Datum aus!";
        }

        // Catch out of range dates
        if($_POST['datum']>DATEENDEERFASSUNG){
            $ErrCount++;
            $ErrMess .= "Bitte wähle ein anderes Datum aus! Die Erfassung geht nur bis einschließlich ".strftime('%a, den %e. %B %G', strtotime(DATEENDEERFASSUNG));
        }

        if($_POST['datum']<DATEBEGINERFASSUNG){
            $ErrCount++;
            $ErrMess .= "Bitte wähle ein anderes Datum aus! Die Erfassung beginnt erst am ".strftime('%a, den %e. %B %G', strtotime(DATEBEGINERFASSUNG));
        }

        if($_POST['datum']>date('Y-m-d')){
            $ErrCount++;
            $ErrMess .= "Du kannst nur vergangene Dienste erfassen! Bitte überprüfe das gewählte Datum!";
        }

        if($ErrCount>0){

            $ParserOutput['kommentar'] = 'Um einen neuen Dienst zu erfassen, fülle das Formular Schritt für Schritt aus.';
            //Form inputs
            $ParserOutput['form_inputs'] = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-invalid" value=""><span class="invalid-feedback">'.$ErrMess.'</span></div>';
            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step1"></div>';

        } else {

            $ParserOutput['kommentar'] = 'Schritt 2: Bitte wähle den zu erfassenden Diensttyp aus.';

            //Form inputs
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            # Parse Date 2 weekday
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, '', '', '', $_POST['datum']).'</div>';
            $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";

            $ParserOutput['form_inputs'] = $Inputs;

            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step2"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset2"></div>';

        }

    }

    // Step 3 - step2 is activated -> chose Diensttype
    if(isset($_POST['step2'])){

        // Initialize Error Handling
        $ErrCount = 0;
        $ErrMess = "";

        // Catch empty inputs
        if(empty($_POST['datum'])){
            $ErrCount++;
            $ErrMess .= "Bitte wähle ein Datum aus!";
        }

        if(intval($_POST['diensttyp']) == 0){
            $ErrCount++;
            $ErrMess .= "Bitte wähle einen Diensttyp aus!";
        } else {
            // Catch already filled in forms
            if (dienst_schon_eingetragen($mysqli, $_POST['datum'], $_POST['diensttyp'])) {
                $ErrCount++;
                $ErrMess .= "Die maximale Anzahl dieses Diensttyps am ausgewählen Tag wurde bereits erfasst!";
            }
        }

        if($ErrCount>0){

            $ParserOutput['kommentar'] = 'Schritt 2: Bitte wähle den zu erfassenden Diensttyp aus.';
            //Form inputs
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp'], '', 'is-invalid', $_POST['datum']).'<span class="invalid-feedback">'.$ErrMess.'</span></div>';
            $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";

            $ParserOutput['form_inputs'] = $Inputs;
            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step2"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset2"></div>';

        } else {

            $ParserOutput['kommentar'] = 'Schritt 3: Bitte Erfasse die Arbeits-/Bereitschaftsdienstzeiten.';

            //Form inputs
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            # Parse Date 2 weekday
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp'], 'disabled', 'is-valid').'</div>';
            $Inputs .= '<div class="form-group"><label>Arbeits-/Bereitschaftszeiten</label>'.diensterfassung_table_form_element($mysqli, $_POST['datum'], $_POST['diensttyp'], '', DIENSTEGRANULATIONMINS).'</div>';
            $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";
            $Inputs .= "<input type='hidden' name='diensttyp' value='".$_POST['diensttyp']."'>";

            $ParserOutput['form_inputs'] = $Inputs;

            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step3"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset3"></div>';

        }

    }

    // Step 4 - step3 is activated -> added times, check for completeness
    if(isset($_POST['step3'])){

        // Initialize Error Handling
        $ErrCount = 0;
        $ErrMess = "";

        // Auto-Parse all checkboxes
        // Define Dienstzeiten Types

        // Load Diensttyp Meta and calculate table-boundaries
        $DiensttypMeta = lade_diensttyp_meta($mysqli, $_POST['diensttyp']);
        $DiensttypMetaVon = $DiensttypMeta['von'];
        $DiensttypMetaBis = $DiensttypMeta['bis'];
        $StartTimeString = $EndTimeString = date('Y-m-d G:i:s');

        // Case 1 - von < bis -> same-day
        if(strtotime($DiensttypMetaVon)<strtotime($DiensttypMetaBis)){
            $StartTimeString = $_POST['datum'].' '.$DiensttypMetaVon;
            $EndTimeString = $_POST['datum'].' '.$DiensttypMetaBis;
        }

        // Case 2 - von > bis -> overnight
        if(strtotime($DiensttypMetaVon)>strtotime($DiensttypMetaBis)){
            $StartTimeString = $_POST['datum'].' '.$DiensttypMetaVon;
            $EndTimeString = date('Y-m-d', strtotime('+1 day',strtotime($_POST['datum']))).' '.$DiensttypMetaBis;
        }

        // Make them time()-objects
        $StartTimeObj = strtotime($StartTimeString);
        $EndTimeObj = strtotime($EndTimeString);

        // Calculate necessary runs for Table-generator
        $NecessaryRuns = calculate_total_mins_diff($StartTimeObj, $EndTimeObj)/DIENSTEGRANULATIONMINS;
        $CounterArbeitszeit = 0;
        $CounterBereitschaft = 0;
        $ProtocolText = "";

        for($a=0;$a<$NecessaryRuns;$a++){
            $radioButtonIDbd = 'inlineRadioOptions1-'.($a+1);
            $radioButtonIDaz = 'inlineRadioOptions2-'.($a+1);

            // Generate timeslot strings
            $beginOperator = '+'.(DIENSTEGRANULATIONMINS*$a).' minutes';
            $endOperator = '+'.((DIENSTEGRANULATIONMINS*$a)+DIENSTEGRANULATIONMINS).' minutes';
            $BeginTimeslotString = date("G:i", strtotime($beginOperator, $StartTimeObj));
            $EndTimeslotString = date("G:i", strtotime($endOperator, $StartTimeObj));;

            // case 1 - all-click
            if(isset($_POST[$radioButtonIDbd])&&isset($_POST[$radioButtonIDaz])){
                $ErrCount++;
                $ErrMess .= "Du hast im Zeitraum ".$BeginTimeslotString." bis ".$EndTimeslotString." Uhr doppelt geclickt - bitte trage die Werte neu ein!<br>";
            }
            // case 2 - no-click
            elseif(!isset($_POST[$radioButtonIDbd])&&!isset($_POST[$radioButtonIDaz])){
                $ErrCount++;
                $ErrMess .= "Du hast im Zeitraum ".$BeginTimeslotString." bis ".$EndTimeslotString." Uhr nichts geclickt - bitte trage diesen Zeitraum noch nach!<br>";
            }
            else {
                // count respective item
                if(isset($_POST[$radioButtonIDbd])){$CounterBereitschaft++;
                    $ProtocolText .= ($a+1).":bd,";}
                if(isset($_POST[$radioButtonIDaz])){$CounterArbeitszeit++;$ProtocolText .= ($a+1).":az,";}
            }
        }

        if($ErrCount>0){

            $ParserOutput['kommentar'] = "Schritt 3: Bitte Erfasse die Arbeits-/Bereitschaftsdienstzeiten.";

            //Form inputs
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            # Parse Date 2 weekday
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp'], 'disabled', 'is-valid').'</div>';
            $Inputs .= '<div class="form-group"><label>Arbeits-/Bereitschaftszeiten</label><div class="alert alert-primary" role="alert">'.$ErrMess.'</div>'.diensterfassung_table_form_element($mysqli, $_POST['datum'], $_POST['diensttyp'], $ProtocolText, DIENSTEGRANULATIONMINS).'</div>';
            $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";
            $Inputs .= "<input type='hidden' name='diensttyp' value='".$_POST['diensttyp']."'>";

            $ParserOutput['form_inputs'] = $Inputs;

            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step3"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset3"></div>';


        }elseif($ErrCount==0){

            // Render Table header
            $Header = "<thead>";
            $Header .= "<tr>";
            $Header .= "<th scope='col'>Arbeitszeit</th>";
            $Header .= "<th scope='col'>Bereitschaftszeit</th>";
            $Header .= "</tr>";
            $Header .= "</thead>";

            // Render Table Body
            $Body = "<tbody>";
            $Body .= "<tr>";
            $Body .= "<td>".convertToHoursMins($CounterArbeitszeit*DIENSTEGRANULATIONMINS)."h</td>";
            $Body .= "<td>".convertToHoursMins($CounterBereitschaft*DIENSTEGRANULATIONMINS)."h</td>";
            $Body .= "</tr>";
            $Body .= "</tbody>";

            $TableStep4 = "<table class='table table-borderless align-middle'>";
            $TableStep4 .= $Header;
            $TableStep4 .= $Body;
            $TableStep4 .= "</table>";

            $ParserOutput['kommentar'] = 'Schritt 4: Bitte Bestätige die Arbeits-/Bereitschaftsdienstzeiten - anschließend wird der Eintrag in der Datenbank gespeichert und du kannst die ID auf dem Zettel festhalten.';

            //Form inputs
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            # Parse Date 2 weekday
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp'], 'disabled', 'is-valid').'</div>';
            $Inputs .= '<div class="form-group"><label>Arbeits-/Bereitschaftszeiten</label>'.$TableStep4.'</div>';
            $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";
            $Inputs .= "<input type='hidden' name='diensttyp' value='".$_POST['diensttyp']."'>";
            $Inputs .= "<input type='hidden' name='dienstprotokoll' value='".$ProtocolText."'>";
            $Inputs .= "<input type='hidden' name='dienstsummeaz' value='".($CounterArbeitszeit*DIENSTEGRANULATIONMINS)."'>";
            $Inputs .= "<input type='hidden' name='dienstsummebd' value='".($CounterBereitschaft*DIENSTEGRANULATIONMINS)."'>";

            $ParserOutput['form_inputs'] = $Inputs;

            //Form Buttons
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Eintragen" name="step4"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset4"></div>';

        }
    }

    // Step 5 - step4 is activated -> Eintrag
    if(isset($_POST['step4'])){

        $Answer = dienst_eintragen($mysqli, $_POST['diensttyp'], $_POST['datum'],$_SESSION["id"], $_POST['dienstprotokoll'], $_POST['dienstsummeaz'], $_POST['dienstsummebd']);

        if($Answer['bool']){
            $ParserOutput['kommentar'] = 'Schritt 5: Eintrag in Datenbank.';
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            # Parse Date 2 weekday
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp'], 'disabled', 'is-valid').'</div>';
            $Inputs .= '<h3>Eintrag erfolgreich!</h3><p>Der Eintrag trägt die ID:<b>'.$Answer['answer'].'</b></p>';
            $ParserOutput['form_inputs'] = $Inputs;
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="finish5"></div>';
        } else {
            $ParserOutput['kommentar'] = 'Schritt 5: Eintrag in Datenbank.';
            $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
            # Parse Date 2 weekday
            $ChosenWeekday = date('l', strtotime($_POST['datum']));
            $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp'], 'disabled', 'is-valid').'</div>';
            $Inputs .= '<h3>Fehler beim Eintragen</h3><p><b>'.$Answer['answer'].'</b></p>';
            $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";
            $Inputs .= "<input type='hidden' name='diensttyp' value='".$_POST['diensttyp']."'>";
            $ParserOutput['form_inputs'] = $Inputs;
            $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset5"></div>';
        }

    }

    // RESETS

    // go back to step 1
    if (isset($_POST['reset2'])){
        $ParserOutput['kommentar'] = 'Um einen neuen Dienst zu erfassen, fülle das Formular Schritt für Schritt aus.';
        //Form inputs
        $ParserOutput['form_inputs'] = '<div class="form-group"><label>Datum</label><input type="date" name="datum" value="'.$_POST['datum'].'"></div>';
        //Form Buttons
        $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step1"><input type="submit" class="btn btn-secondary ml-2" value="Reset" name="reset1"></div>';
    }

    // go back to step 2
    if(isset($_POST['reset3'])){
        $ParserOutput['kommentar'] = 'Schritt 2: Bitte wähle den zu erfassenden Diensttyp aus.';

        //Form inputs
        $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
        # Parse Date 2 weekday
        $ChosenWeekday = date('l', strtotime($_POST['datum']));
        $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp']).'</div>';
        $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";

        $ParserOutput['form_inputs'] = $Inputs;

        //Form Buttons
        $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step2"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset2"></div>';

    }

    // go back to step 3
    if(isset($_POST['reset4']) OR isset($_POST['reset5'])){
        $ParserOutput['kommentar'] = 'Schritt 3: Bitte Erfasse die Arbeits-/Bereitschaftsdienstzeiten.';

        //Form inputs
        $Inputs = '<div class="form-group"><label>Datum</label><input type="date" name="datum" class="form-control is-valid" value="'.$_POST['datum'].'" disabled></div>';
        # Parse Date 2 weekday
        $ChosenWeekday = date('l', strtotime($_POST['datum']));
        $Inputs .= '<div class="form-group"><label>Diensttyp</label>'.dropdown_diensttypen('diensttyp', $ChosenWeekday, $mysqli, $_POST['diensttyp'], 'disabled', 'is-valid').'</div>';
        $Inputs .= '<div class="form-group"><label>Arbeits-/Bereitschaftszeiten</label>'.diensterfassung_table_form_element($mysqli, $_POST['datum'], $_POST['diensttyp'], $_POST['dienstprotokoll'], $granulationMins).'</div>';
        $Inputs .= "<input type='hidden' name='datum' value='".$_POST['datum']."'>";
        $Inputs .= "<input type='hidden' name='diensttyp' value='".$_POST['diensttyp']."'>";

        $ParserOutput['form_inputs'] = $Inputs;

        //Form Buttons
        $ParserOutput['form_buttons'] = '<div class="form-group"><input type="submit" class="btn btn-primary" value="Absenden" name="step3"><input type="submit" class="btn btn-secondary ml-2" value="Zurück" name="reset3"></div>';
    }

    // Go Back to dashboard
    if(isset($_POST['finish5'])){
        // Redirect user to welcome page
        header("location: dashboard.php");
    }

    return $ParserOutput;

}
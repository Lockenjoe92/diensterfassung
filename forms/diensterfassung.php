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
function dropdown_diensttypen($name, $selected=''){

    require_once "./configs/db_config.php";
    $Output = "<select class='custom-select' name='".$name."'>";
    if(empty($selected)){
        $Output .= "<option selected>Bitte Diensttyp wählen</option>";
    } else {
        $Output .= "<option>Bitte Diensttyp wählen</option>";
    }

    // Prepare a select statement
    $sql = "SELECT id, dienstname, von, bis FROM diensttypen ORDER BY dienstname ASC";
    if($stmt = $mysqli->query($sql)){

            while ($row = $stmt->fetch_assoc()) {
                if($selected==$row["id"]){
                    $Output .= '<option value="'.$row["id"].'" selected>'.$row["dienstname"].'</option>';
                } else {
                    $Output .= '<option value="'.$row["id"].'">'.$row["dienstname"].'</option>';
                }
            }
    }

    $Output .= "</select>";
    return $Output;
}
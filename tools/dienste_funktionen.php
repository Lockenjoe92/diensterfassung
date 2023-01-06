<?php

function lade_diensttyp_meta($mysqli, $diensttyp){

    $sql = "SELECT * FROM diensttypen WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $diensttyp);
        if($stmt->execute()){
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
    }

}

function lade_anzahl_dienste_spez_typ_an_datum_x($mysqli, $datum, $diensttyp){

    $sql = "SELECT id FROM dienste WHERE typ = ? AND datum = ? AND storno_user = 0";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("is", $diensttyp, $datum);
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // store result
            $stmt->store_result();
            return $stmt->num_rows;
        } else {
            return false;
        }
    } else {
        return false;
    }

}

function lade_anzahl_dienste_an_datum_x($mysqli, $datum){

    $sql = "SELECT id FROM dienste WHERE datum = ? AND storno_user = 0";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("s", $datum);
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // store result
            $stmt->store_result();
            return $stmt->num_rows;
        } else {
            return false;
        }
    } else {
        return false;
    }

}

function dienst_schon_eingetragen($mysqli, $datum, $diensttyp){

    #dump($datum, $diensttyp);

    // load all we need to know about the Diensttyp
    $Dienstmeta = lade_diensttyp_meta($mysqli, $diensttyp);

    // check if Dienste of this type on this date have already been added to the db
    $AnzahlInDB = lade_anzahl_dienste_spez_typ_an_datum_x($mysqli, $datum, $diensttyp);
    #dump($AnzahlInDB);

    // Check if number of entered Dienste is less than max. according to setting
    if (intval($AnzahlInDB) < intval($Dienstmeta['max_pro_tag'])){
        return false;
    } else {
        return true;
    }

}

function dienst_eintragen($mysqli, $typ, $datum, $erfasser, $auswertungProtokoll, $auswertungAZ, $auswertungBD){

    // Prepare answer Array
    $Answer['bool'] = null;
    $Answer['answer'] = '';

    // Prepare an insert statement
    $sql = "INSERT INTO dienste (typ, datum, erfasser, auswertung, auswertung_arbeitszeit, auswertung_bereitschaftszeit) VALUES (?, ?, ?, ?, ?, ?)";

    if($stmt = $mysqli->prepare($sql)){

        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("isisii", $typ, $datum, $erfasser, $auswertungProtokoll, $auswertungAZ, $auswertungBD);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Close statement
            $stmt->close();
            $Answer['bool'] = true;
            $Answer['answer'] = $mysqli->insert_id;
            return $Answer;
        } else{
            // Close statement
            $stmt->close();
            $Answer['bool'] = false;
            $Answer['answer'] = 'Fehler beim Datenbankzugriff!';
            return $Answer;
        }

    }

}

function lade_soll_alle_diensttypen_an_wochentag($mysqli, $day){
    // Prepare a select statement
    $sql = "SELECT id, diensttage, max_pro_tag FROM diensttypen ORDER BY dienstname ASC";
    if($stmt = $mysqli->query($sql)){
        $counter = 0;
        while ($row = $stmt->fetch_assoc()) {
            if(in_array($day, explode(',',$row['diensttage']))){
                $counter += $row['max_pro_tag'];
            }
        }
    }

    return $counter;
}

function get_array_with_all_diensttypen($mysqli){

    $AlleDienste = array();
    $sql = "SELECT * FROM diensttypen ORDER BY dienstname ASC";
    if($stmt = $mysqli->query($sql)){
        // store result
        while ($row = $stmt->fetch_assoc()) {
            $AlleDienste[] = $row;
        }
    }
    return $AlleDienste;
}

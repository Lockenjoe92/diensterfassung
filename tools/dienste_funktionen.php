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

    $sql = "SELECT id FROM dienste WHERE typ = ? AND datum = ? AND storno_user IS NULL";
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

function dienst_schon_eingetragen($mysqli, $datum, $diensttyp){

    // load all we need to know about the Diensttyp
    $Dienstmeta = lade_diensttyp_meta($mysqli, $diensttyp);

    // check if Dienste of this type on this date have already been added to the db
    $AnzahlInDB = lade_anzahl_dienste_spez_typ_an_datum_x($mysqli, $datum, $diensttyp);

    // Check if number of entered Dienste is less than max. according to setting
    if (intval($AnzahlInDB) < intval($Dienstmeta['max_pro_tag'])){
        return false;
    } else {
        return true;
    }

}
<?php
function gesamt_dienste_statustool($mysqli, $mode, $begin='', $end=''){

    require_once "tools/time_stuff.php";
    require_once "tools/dienste_funktionen.php";

    if($mode=='nums'){
        $sql = "SELECT id FROM dienste WHERE datum >= '".$begin."' AND datum <= '".$end."' AND storno_user = 0";
        $Abfrage = mysqli_query($mysqli, $sql);
        return mysqli_num_rows($Abfrage);
    }

    if($mode=='vollstaendigkeit'){

        $SollDienstCounter = 0;
        $IstDienstCounter = 0;
        $AnzahlTage = calculate_total_days_diff($begin, $end);
        $DateToday = date('Y-m-d');

        for($a=0;$a<=$AnzahlTage;$a++){
            $Command = "+".$a." days";
            $CurrentDate = date('Y-m-d', strtotime($Command, strtotime($begin)));

            if($DateToday<=date('Y-m-d', strtotime($end))){
                $Ist = lade_anzahl_dienste_an_datum_x($mysqli, $CurrentDate);
                $Soll = lade_soll_alle_diensttypen_an_wochentag($mysqli, date('l', strtotime($Command, strtotime($begin))));

                $IstDienstCounter += $Ist;
                $SollDienstCounter += $Soll;
            }

        }

        return round($IstDienstCounter/$SollDienstCounter, 2);
    }

}
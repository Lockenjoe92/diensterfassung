<?php
function parser_erfassunguebersicht($mysqli){

    require_once "configs/settings_config.php";
    require_once "tools/dienste_funktionen.php";
    require_once "tools/time_stuff.php";

    $Antwort['filterForm'] = "";
    $Antwort['table'] = table_erfassungsuebersicht($mysqli, DATEBEGINERFASSUNG, DATEENDEERFASSUNG);

    return $Antwort;
}

function table_erfassungsuebersicht($mysqli, $BeginDate, $EndeDate){

    // Initialize Stuff
    $Response = '';
    require_once "tools/time_stuff.php";

    // Static table head
    $Response .= '<div class="container">';
    $Response .= '<table class="table table-striped">';
    $Response .= '<thead>';
    $Response .= '<tr>';
    $Response .= '<th scope="col">KW</th>';
    $Response .= '<th scope="col">Montag</th>';
    $Response .= '<th scope="col">Dienstag</th>';
    $Response .= '<th scope="col">Mittwoch</th>';
    $Response .= '<th scope="col">Donnerstag</th>';
    $Response .= '<th scope="col">Freitag</th>';
    $Response .= '<th scope="col">Samstag</th>';
    $Response .= '<th scope="col">Sonntag</th>';
    $Response .= '</tr>';
    $Response .= '</thead>';
    $Response .= '<tbody>';

    // Dynamic table Body
    $TotalDaysToRun = calculate_total_days_diff($BeginDate, $EndeDate);
    $NecessaryWeeks = ceil($TotalDaysToRun/7);
    $FirstKW = strftime('%U', strtotime($BeginDate));
    $FirstDayNumber = strftime('%u', strtotime($BeginDate));
    $TotalDaysAchieved = 0;

    for($a=0;$a<$NecessaryWeeks;$a++){

        // Generate Rows
        $Response .= '<tr>';

        // catch first week indent
        if($a==0){
            for($b=0;$b<=7;$b++){

                // Fill KW Field
                if($b==0){
                    $Response .= '<td>'.($FirstKW+$a).'</td>';
                }

                //start on correct day
                if($b>=$FirstDayNumber){
                    $Response .= generate_td_element_uebersicht($mysqli, $BeginDate, $TotalDaysAchieved);
                    $TotalDaysAchieved++;
                } elseif(($b>=1) && ($b<$FirstDayNumber)) {
                    $Response .= '<td class="table-secondary"></td>';
                }
            }

        } else {
            for($b=0;$b<=7;$b++){

                // Fill KW Field
                if($b==0){
                    $Response .= '<td>'.($FirstKW+$a).'</td>';
                } else {
                    $Response .= generate_td_element_uebersicht($mysqli, $BeginDate, $TotalDaysAchieved);
                    $TotalDaysAchieved++;
                }

            }
        }

        $Response .= '</tr>';

    }

    $Response .= '</tbody>';
    $Response .= '</table>';
    $Response .= '</div>';

    return $Response;

}

function generate_td_element_uebersicht($mysqli, $BeginDate, $Iteration){

    require_once "tools/dienste_funktionen.php";

    // Calculate Cell Date
    $Command = "+".$Iteration." days";
    $datum = date("Y-m-d", strtotime($Command, strtotime($BeginDate)));

    // fetch current stats
    $IST = lade_anzahl_dienste_an_datum_x($mysqli, $datum);
    $SOLL = lade_soll_alle_diensttypen_an_wochentag($mysqli, date('l', strtotime($Command, strtotime($datum))));
    $Div = $IST/$SOLL;

    // Coloring
    $Coloring = "";
    if($Div>=0.9){
        $Coloring = "table-success";
    } elseif (($Div<0.9)&&($Div>0)){
        $Coloring = "table-warning";
    } elseif ($Div==0){
        $Coloring = "table-danger";
    }

    return "<td class='".$Coloring."'></td>";
}
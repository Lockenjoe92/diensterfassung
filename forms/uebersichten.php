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
    require_once "tools/dienste_funktionen.php";

    // Static table head
    $Response .= '<div class="container">';
    $Response .= '<table class="table table-striped align-middle text-center">';
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

    $AlleDienste = get_array_with_all_diensttypen($mysqli);

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
                    $Response .= generate_td_element_uebersicht($mysqli, $BeginDate, $TotalDaysAchieved, $AlleDienste);
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
                    $Response .= generate_td_element_uebersicht($mysqli, $BeginDate, $TotalDaysAchieved, $AlleDienste);
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

function generate_td_element_uebersicht($mysqli, $BeginDate, $Iteration, $AlleDienste){

    require_once "tools/dienste_funktionen.php";

    // Calculate Cell Date
    $Command = "+".$Iteration." days";
    $datum = date("Y-m-d", strtotime($Command, strtotime($BeginDate)));
    $datumLeserlich = date("d.m.Y", strtotime($Command, strtotime($BeginDate)));

    //Get All Holidays -> if so, "Weekday" Holiday or Holiday-1 is used to fetch correct Diensttyp
    $Holidays = get_array_with_all_dates_from_holidays();
    $DayBeforeHoliday=false;
    $TodayHoliday=false;
    foreach ($Holidays as $Holiday){
        $dateDayBeforeHoliday=date('Y-m-d', strtotime('-1 day', strtotime($Holiday)));
        if($Holiday == $datum){
            $TodayHoliday=true;
        }
        if($dateDayBeforeHoliday == $datum){
            $DayBeforeHoliday=true;
        }
    }
    $Weekday = "";
    if($TodayHoliday){
        $Weekday = "Holiday";
    } elseif ($DayBeforeHoliday) {
        $Weekday = "Holiday-1";
    } else {
        $Weekday = date('l', strtotime($datum));
    }

    // fetch current stats
    $IST = lade_anzahl_dienste_an_datum_x($mysqli, $datum);
    $SOLL = lade_soll_alle_diensttypen_an_wochentag($mysqli, $Weekday);
    $Div = $IST/$SOLL;

    // Coloring
    $Coloring = "";
    if($datum<=date('Y-m-d')){
        if($Div>=0.9){
            $Coloring = "table-success";
        } elseif (($Div<0.9)&&($Div>0)){
            $Coloring = "table-warning";
        } elseif ($Div==0){
            $Coloring = "table-danger";
        }

        // fetch missing dienste for tooltip
        $Missing = '';
        $MissingCount = 0;

        foreach ($AlleDienste as $Dienst){
            if(in_array($Weekday, explode(',',$Dienst['diensttage']))){
                if(!dienst_schon_eingetragen($mysqli, $datum, $Dienst["id"])) {
                    $Missing .= $Dienst["dienstname"]."<br>";
                    $MissingCount++;
                }
            }
        }

        if($MissingCount>0){
            $Missing = $datumLeserlich.'<br><b>Fehlende Einträge:</b><br>'.$Missing;
        } else {
            $Missing = $datumLeserlich.'<br>Alle Dienste erfasst';
        }

        $Tooltip = '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="'.$Missing.'">'.$IST.'/'.$SOLL.'</a></a>';

    } else {
        $Coloring = "table-secondary";
        $Tooltip = '';
    }

    return "<td class='".$Coloring."'>".$Tooltip."</td>";
}
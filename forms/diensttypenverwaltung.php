<?php
 function diensttypen_verwalten_table($mysqli){

     require_once "tools/dienste_funktionen.php";

     // get dem Users
     $Diensttypen = get_array_with_all_diensttypen($mysqli);

     // build table
     $Table = '<div class="container-xl"><table class="table table-striped align-middle text-center">';
     $Table .= '<thead>';
     $Table .= '<tr>';
     $Table .= '<th scope="col">ID</th>';
     $Table .= '<th scope="col">Dienst</th>';
     $Table .= '<th scope="col">Erfasster Zeitraum</th>';
     $Table .= '<th scope="col">Gültige Wochentage</th>';
     $Table .= '<th scope="col">Max. Anzahl pro Tag</th>';
     $Table .= '<th scope="col">Funktionen</th>';
     $Table .= '</tr>';
     $Table .= '</thead>';
     $Table .= '<tbody>';

     // dynamic part
     foreach ($Diensttypen as $Diensttyp) {

         $Zeitraum = $Diensttyp['von']." - ".$Diensttyp['bis'];

         $Tage = '<ul>';
         $DTs = explode(',',$Diensttyp['diensttage']);
         foreach ($DTs as $DT){
             $Tage .= "<li>".$DT."</li>";
         }
         $Tage .= '</ul>';

         $Table .= '<tr>';
         $Table .= '<td>'.$Diensttyp['id'].'</td>';
         $Table .= '<td>'.$Diensttyp['dienstname'].'</td>';
         $Table .= '<td>'.$Zeitraum.'</td>';
         $Table .= '<td>'.$Tage.'</td>';
         $Table .= '<td>'.$Diensttyp['max_pro_tag'].'</td>';
         $Table .= '<td></td>';
         $Table .= '</tr>';
     }

     $Table .= '</tbody>';
     $Table .= '</table></div>';

     return $Table;

 }
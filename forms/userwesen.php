<?php

function usermanagement_table($mysqli){

    require_once "tools/user_funktionen.php";

    // get dem Users
    $Users = get_array_of_all_users($mysqli);

    // build table
    $Table = '<div class="container-lg"><table class="table table-striped align-middle">';
    $Table .= '<thead>';
    $Table .= '<tr>';
    $Table .= '<th scope="col">ID</th>';
    $Table .= '<th scope="col">Vorname</th>';
    $Table .= '<th scope="col">Registriert am</th>';
    $Table .= '<th scope="col">Nutzergruppen</th>';
    $Table .= '<th scope="col">Funktionen</th>';
    $Table .= '</tr>';
    $Table .= '</thead>';
    $Table .= '<tbody>';

    // dynamic part
    foreach ($Users as $user) {

        $Rollen = '<ul>';
        $NGs = explode(',',$user['nutzergruppen']);
        foreach ($NGs as $NG){
            $Rollen .= "<li>".$NG."</li>";
        }
        $Rollen .= '</ul>';

        $Table .= '<tr>';
        $Table .= '<td>'.$user['id'].'</td>';
        $Table .= '<td>'.$user['vorname'].'</td>';
        $Table .= '<td>'.$user['created_at'].'</td>';
        $Table .= '<td>'.$Rollen.'</td>';
        $Table .= '<td></td>';
        $Table .= '</tr>';
    }

    $Table .= '</tbody>';
    $Table .= '</table></div>';

    return $Table;
}
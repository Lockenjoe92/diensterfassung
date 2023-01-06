<?php
function get_array_of_all_users($mysqli){

    $AlleUser = array();
    $sql = "SELECT * FROM users ORDER BY vorname ASC";
    if($stmt = $mysqli->query($sql)){
        // store result
        while ($row = $stmt->fetch_assoc()) {
            $AlleUser[] = $row;
        }
    }
    return $AlleUser;

}

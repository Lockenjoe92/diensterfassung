<?php
function calculate_total_mins_diff($TimeObjStart, $TimeObjEnd){

    // Calculate Difference
    $mins = ($TimeObjEnd-$TimeObjStart) / 60;

    return $mins;

}
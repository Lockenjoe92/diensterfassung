<?php
function calculate_total_mins_diff($TimeObjStart, $TimeObjEnd){

    // Calculate Difference
    $mins = ($TimeObjEnd-$TimeObjStart) / 60;

    return $mins;

}
function convertToHoursMins($time, $format = '%02d:%02d') {
    if ($time < 1) {
        return null;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}
<?php
require_once "configs/settings_config.php";

function calculate_total_mins_diff($TimeObjStart, $TimeObjEnd){

    // Calculate Difference
    $mins = ($TimeObjEnd-$TimeObjStart) / 60;

    return $mins;

}

function calculate_total_days_diff($begin, $end){
    $earlier = new DateTime($begin);
    $later = new DateTime($end);
    return $later->diff($earlier)->format("%a");

}

function convertToHoursMins($time, $format = '%02d:%02d') {
    if ($time < 1) {
        return null;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

function get_array_with_all_dates_from_holidays(){

    return explode(',',FEIERTAGE);

}

function calculate_weekday_or_holiday_by_input_date($Datum){

    //Get All Holidays -> if so, "Weekday" Holiday or Holiday-1 is used to fetch correct Diensttyp
    $Holidays = get_array_with_all_dates_from_holidays();
    $DayBeforeHoliday=false;
    $TodayHoliday=false;
    foreach ($Holidays as $Holiday){
        $dateDayBeforeHoliday=date('Y-m-d', strtotime('-1 day', strtotime($Holiday)));
        if($Holiday == $Datum){
            $TodayHoliday=true;
        }
        if($dateDayBeforeHoliday == $Datum){
            $DayBeforeHoliday=true;
        }
    }
    $Weekday = "";
    if($TodayHoliday){
        $Weekday = "Holiday";
    } elseif ($DayBeforeHoliday) {
        $Weekday = "Holiday-1";
    } else {
        $Weekday = date('l', strtotime($Datum));
    }

    return $Weekday;
}


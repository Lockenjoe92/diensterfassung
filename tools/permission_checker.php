<?php
function permission_checker_with_redirect($RequiredGroup){
    if (!in_array($RequiredGroup, explode(',',$_SESSION["nutzergruppen"]))){
        header("location: dashboard.php");
        exit;
    }
}
function permission_checker_boolean($RequiredGroup){
    if (!in_array($RequiredGroup, explode(',',$_SESSION["nutzergruppen"]))){
        return false;
    } else {
        return true;
    }
}
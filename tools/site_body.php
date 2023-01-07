<?php
function nav_bar($mode='not-logged-in'){

    if($mode=='not-logged-in'){
        $response = '<nav class="navbar" style="background-color: #eeeee4;"><div class="container-fluid"><span class="navbar-brand mb-0 h1">BD-Zeitenerfassung</span></div></nav>';
    } elseif ($mode=='logged-in'){
        $response = '<nav class="navbar" style="background-color: #eeeee4;"><div class="container-fluid"><a class="navbar-brand" href="dashboard.php">BD-Zeitenerfassung</a></div></nav>';
    }

    return $response;

}

function site_skeleton($SiteTitle, $LoginMode, $BodyInput){

    $Return = '<!DOCTYPE html>
                <html lang="de">
                    <head>
                        <meta charset="UTF-8">
                        <title>'.$SiteTitle.'</title>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
                        <style>
                            body{ font: 14px sans-serif; }
                            .wrapper{ width: 360px; padding: 20px; }
                        </style>
                    </head>
                    <body>';
    $Return .= nav_bar($LoginMode);
    $Return .= $BodyInput;
    $Return .= '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
                <script src="js/main.js"></script>
                </body>';

    return $Return;
}
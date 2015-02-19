<?php
session_start(); 
require('o365/Office365Service.php');
unset($_SESSION['userName']);
unset($_SESSION['accessToken']);
unset($_SESSION['refreshToken']);
$redirectUri = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/home.php";
header("Location: ".Office365Service::getLogoutUrl($redirectUri));
?>
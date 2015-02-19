<?php
session_start(); 
require('Office365Service.php');
// Get the 'code' and 'session_state' parameters from
// the GET request
$code = $_GET['code'];
$session_state = $_GET['session_state'];

if (is_null($code)) {
  // Display error 
  echo "NO CODE!";
}
else {
  error_log("authorize.php called with code: ".$code);
  $redirectUri = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/o365/authorize.php"; 
  
  error_log("Calling getTokenFromAuthCode");
  $tokens = Office365Service::getTokenFromAuthCode($code, $redirectUri);
  error_log("getTokenFromAuthCode returned:");
  error_log("  access_token: ".$tokens['access_token']);
  error_log("  refresh_token: ".$tokens['refresh_token']);
  
  $_SESSION['accessToken'] = $tokens['access_token'];
  $_SESSION['refreshToken'] = $tokens['refresh_token'];
  $_SESSION['userName'] = Office365Service::getUserName($tokens['id_token']);

  $homePage = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/home.php"; 
  header("Location: ".$homePage);
  exit;
}
?>
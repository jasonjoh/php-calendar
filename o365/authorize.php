<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.

// This file serves as the redirect target for the first part of the auth code grant flow.
// The user is directed to the Azure login site, and once they login, they are redirected here.
session_start(); 
require_once('Office365Service.php');
// Get the 'code' and 'session_state' parameters from
// the GET request
$code = $_GET['code'];
$session_state = $_GET['session_state'];

$errorPage = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/error.php"; 

if (is_null($code)) {
  // Display error 
  $msg = "There was no 'code' parameter in the query string.";
  error_log($msg);
  header("Location: ".$errorPage."?errorMsg=".urlencode($msg));
  exit;
}
else {
  error_log("authorize.php called with code: ".$code);
  $redirectUri = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/o365/authorize.php"; 
  
  error_log("Calling getTokenFromAuthCode");
  // Use the code supplied by Azure to request an access token.
  $tokens = Office365Service::getTokenFromAuthCode($code, $redirectUri);
  if ($tokens['access_token']) {
    error_log("getTokenFromAuthCode returned:");
    error_log("  access_token: ".$tokens['access_token']);
    error_log("  refresh_token: ".$tokens['refresh_token']);
    
    // Save the access token and refresh token to the session.
    $_SESSION['accessToken'] = $tokens['access_token'];
    $_SESSION['refreshToken'] = $tokens['refresh_token'];
    // Parse the id token returned in the response to get the user name.
    $_SESSION['userName'] = Office365Service::getUserName($tokens['id_token']);

    // Redirect back to the homepage.
    $homePage = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/home.php"; 
    header("Location: ".$homePage);
    exit;
  }
  else {
    $msg = "Error retrieving access token: ".$tokens['error'];
    error_log($msg);
    header("Location: ".$errorPage."?errorMsg=".urlencode($msg));
  }
}

/*
 MIT License: 
 
 Permission is hereby granted, free of charge, to any person obtaining 
 a copy of this software and associated documentation files (the 
 ""Software""), to deal in the Software without restriction, including 
 without limitation the rights to use, copy, modify, merge, publish, 
 distribute, sublicense, and/or sell copies of the Software, and to 
 permit persons to whom the Software is furnished to do so, subject to 
 the following conditions: 
 
 The above copyright notice and this permission notice shall be 
 included in all copies or substantial portions of the Software. 
 
 THE SOFTWARE IS PROVIDED ""AS IS"", WITHOUT WARRANTY OF ANY KIND, 
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
 LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
 OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
?>
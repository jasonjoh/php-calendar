<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file. 
session_start(); 
require('o365/Office365Service.php');
// Clear user info from the session.
unset($_SESSION['userName']);
unset($_SESSION['accessToken']);
unset($_SESSION['refreshToken']);

// Build a URL back to the homepage.
$redirectUri = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/home.php";
// Redirect the user to the Azure logout URL, which will then redirect back to the homepage.
header("Location: ".Office365Service::getLogoutUrl($redirectUri));

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
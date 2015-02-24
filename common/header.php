<?php 
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.
require('site-event-list.php');
session_start(); 
?>
<html>
<head>
  <title><?php echo $page['title'];?></title>
  <link href="common/css/styles.css" rel="stylesheet">
</head>
<body>

<!-- top menu bar -->
<div id="info-bar">
  <span id="app-title"><a class="nav" href="/php-calendar/home.php"><strong>php-calendar</strong></a></span>
  <?php
    $userName = $_SESSION['userName'];
    if ($userName) {
  ?>
    <span id="logout"><?php echo $userName ?><a class="nav" href="logout.php">logout</a></span>
  <?php
    }
  ?>
</div>

<!-- header ends -->

<?php
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

<?php 
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

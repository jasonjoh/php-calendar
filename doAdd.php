<?php
require('site-event-list.php');
require('o365/Office365Service.php');
session_start(); 

$showIndex = $_GET['showIndex'];
error_log("doAdd.php called.");
error_log("showIndex parameter: ".$showIndex);

$accessToken = $_SESSION['accessToken'];

$event = $_SESSION['events'][$showIndex];
error_log("Retrieved event '".$event->title."' from session.");

$eventId = Office365Service::addEventToCalendar($accessToken, $event->title, $event->location,
  $event->startTime, $event->endTime);
  
error_log("Create returned: ".$eventId);
  
if ($event->voucherRequired) {
  $attachmentData = "This is your voucher for '".$event->title."' on ".date_format($event->startTime, "M j, Y");
  Office365Service::addAttachmentToEvent($accessToken, $eventId, $attachmentData);
}

$homePage = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/home.php";   
header("Location: ".$homePage);
?>
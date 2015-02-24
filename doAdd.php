<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.
require('site-event-list.php');
require_once('o365/Office365Service.php');
require_once('sessionManager.php');
session_start(); 

$homePage = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/home.php"; 
$errorPage = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/error.php"; 

error_log("doAdd.php called.");

// Get the show index from the query parameters.
$showIndex = $_POST['showIndex'];
if (is_null($showIndex)) {
  error_log("No 'showIndex' in POST parameters.");
  header("Location: ".$homePage);
  exit;
}

$attendeeString = $_POST['attendees'];
error_log("Value of attendees: ".$attendeeString);

error_log("showIndex parameter: ".$showIndex);

// Get the access token from the session.
$accessToken = $_SESSION['accessToken'];

// Get the event from the array of events in the session.
$event = $_SESSION['events'][$showIndex];
error_log("Retrieved event '".$event->title."' from session.");

// Add the event to the Office 365 calendar.
$eventId = Office365Service::addEventToCalendar($accessToken, $event->title, $event->location,
  $event->startTime, $event->endTime, $attendeeString);  
error_log("Create returned: ".$eventId);
if (SessionManager::checkResponseAndRefreshToken($eventId)) {
  // Pick up new access token
  $accessToken = $_SESSION['accessToken'];
  // Retry request
  $eventId = Office365Service::addEventToCalendar($accessToken, $event->title, $event->location,
    $event->startTime, $event->endTime, $attendeeString);
}

if (is_array($eventId) && $eventId['error']) {
  $msg = "Error adding event to calendar: ".$eventId['error'];
  error_log($msg);
  header("Location: ".$errorPage."?errorMsg=".urlencode($msg));
  exit;
}

// If a voucher is required, add it as an attachment to the event.  
if ($event->voucherRequired) {
  $attachmentData = "This is your voucher for '".$event->title."' on ".date_format($event->startTime, "M j, Y");
  $result = Office365Service::addAttachmentToEvent($accessToken, $eventId, $attachmentData);
  if (SessionManager::checkResponseAndRefreshToken($result)) {
    // Pick up new access token
    $accessToken = $_SESSION['accessToken'];
    // Retry request
    $result = Office365Service::addAttachmentToEvent($accessToken, $eventId, $attachmentData);
  }

  if ($result['error']) {
    $msg = "Error adding attachment to event: ".$result['error'];
    error_log($msg);
    header("Location: ".$errorPage."?errorMsg=".urlencode($msg));
    exit;
  }
}

// Finally, redirect the user back to the home page.
header("Location: ".$homePage);

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
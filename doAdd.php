<!-- Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file. -->
<?php
require('site-event-list.php');
require('o365/Office365Service.php');
session_start(); 

// Get the show index from the query parameters.
$showIndex = $_GET['showIndex'];
error_log("doAdd.php called.");
error_log("showIndex parameter: ".$showIndex);

// Get the access token from the session.
$accessToken = $_SESSION['accessToken'];

// Get the event from the array of events in the session.
$event = $_SESSION['events'][$showIndex];
error_log("Retrieved event '".$event->title."' from session.");

// Add the event to the Office 365 calendar.
$eventId = Office365Service::addEventToCalendar($accessToken, $event->title, $event->location,
  $event->startTime, $event->endTime);
  
error_log("Create returned: ".$eventId);

// If a voucher is required, add it as an attachment to the event.  
if ($event->voucherRequired) {
  $attachmentData = "This is your voucher for '".$event->title."' on ".date_format($event->startTime, "M j, Y");
  Office365Service::addAttachmentToEvent($accessToken, $eventId, $attachmentData);
}

// Finally, redirect the user back to the home page.
$homePage = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/home.php";   
header("Location: ".$homePage);
?>

<!--
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
-->
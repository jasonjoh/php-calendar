<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.
// create an array to set page-level variables
$page = array();
$page['title'] = 'Add to Calendar';

// include the page header
include('common/header.php');
require_once('o365/Office365Service.php');
require_once('sessionManager.php');

// Get the index of the show from the query parameters
$showIndex = $_GET['showIndex'];
error_log("addToCalendar.php called.");
error_log("showIndex parameter: ".$showIndex);

// Get access token from the session.
$accessToken = $_SESSION['accessToken'];

// Get the event from the array of events in the session.
$event = $_SESSION['events'][$showIndex];
error_log("Retrieved event '".$event->title."' from session.");

// Get all events on the user's O365 calendar for that day.
$eventsOnThisDay = Office365Service::getEventsForDate($accessToken, $event->startTime);
if (SessionManager::checkResponseAndRefreshToken($eventsOnThisDay)) {
  // Pick up new access token
  $accessToken = $_SESSION['accessToken'];
  
  error_log("Retrying get events request");
  $eventsOnThisDay = Office365Service::getEventsForDate($accessToken, $event->startTime);
}

// Build a link URL to the doAdd.php file, which does the actual work to add
// the event to the O365 calendar.
$buttonUrl = "doAdd.php";

$altRow = false;
?>

<div id="content">
  <div id="event-details">
    <h1>Add Event To Calendar</h1>
    <table>
      <tr>
        <td>Show</td>
        <td><?php echo $event->title ?></td>
      </tr>
      <tr>
        <td>Location</td>
        <td><?php echo $event->location ?></td>
      </tr>
      <tr>
        <td>Date</td>
        <td><?php echo date_format($event->startTime, "M j, Y") ?></td>
      </tr>
      <tr>
        <td>Time</td>
        <td><?php echo date_format($event->startTime, "g:i a")." - ".date_format($event->endTime, "g:i a") ?></td>
      </tr>
      <tr>
        <td>Voucher required?</td>
        <td><?php echo $event->voucherRequired ? "Yes" : "No" ?></td>
      </tr>
    </table>
  </div>
  <div id="calendar-sidebar">
    <div id="cal-view-title">Your calendar for <?php echo date_format($event->startTime, "m/d/Y") ?></div>
    <?php 
      if ($eventsOnThisDay['error']) { 
        echo "<div text-align=\"center\">ERROR: ".$eventsOnThisDay['error']."</div>";
      }
    ?>
    <table class="cal-view">
      <tr>
        <th>Event</th>
        <th>Start</th>
        <th>End</th>
      </tr>
      <?php foreach($eventsOnThisDay['value'] as $event) { ?>
        <tr title="<?php echo $event['Subject'] ?>"<?php echo $altRow ? 'class="alt"' : "" ?>>
          <td>
            <?php 
              if (strlen($event['Subject']) <= 25) {
                echo $event['Subject'];
              }
              else {
                echo substr($event['Subject'], 0, 22)."...";
              }
            ?>
          </td>
          <td>
            <?php
              // Since date/time values are in UTC when they are returned by 
              // Exchange, convert them to the local time zone before displaying.
              $startDate = new DateTime($event['Start'], new DateTimeZone("UTC"));
              $startDate->setTimeZone(new DateTimeZone(date_default_timezone_get()));
              echo date_format($startDate, "g:i a"); 
            ?>
          </td>
          <td>
            <?php
              // Since date/time values are in UTC when they are returned by 
              // Exchange, convert them to the local time zone before displaying.
              $endDate = new DateTime($event['End'], new DateTimeZone("UTC"));
              $endDate->setTimeZone(new DateTimeZone(date_default_timezone_get()));
              echo date_format($endDate, "g:i a"); 
            ?>
          </td>
        </tr>
      <?php } ?>
    </table>
    <?php if (sizeof($eventsOnThisDay['value']) <= 0) { echo "<div text-align=\"center\">No events found</div>"; } ?>
  </div>
</div>

<form class="add-event" action="<?php echo $buttonUrl ?>" method="post">
  <input type="hidden" name="showIndex" id="showIndex" value="<?php echo $showIndex ?>"/>
  <label for="attendees">Enter email addresses (separated by ';') to invite friends!</label><br>
  <input type="text" name="attendees" id="attendees"/><br>
  <input type="submit" value="Add to my calendar"</input>
</form>
<?php
include('common/footer.php');
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
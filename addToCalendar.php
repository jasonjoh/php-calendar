<?php
// create an array to set page-level variables
$page = array();
$page['title'] = 'Add to Calendar';

// include the page header
include('common/header.php');
require('o365/Office365Service.php');

$showIndex = $_GET['showIndex'];
error_log("addToCalendar.php called.");
error_log("showIndex parameter: ".$showIndex);

$accessToken = $_SESSION['accessToken'];

$event = $_SESSION['events'][$showIndex];
error_log("Retrieved event '".$event->title."' from session.");

$eventsOnThisDay = Office365Service::getEventsForDate($accessToken, $event->startTime);

$buttonUrl = "doAdd.php?showIndex=".$showIndex;

$altRow = false;
?>

<div id="content">
  <div id="event-details">
    <h1>Add Event To Calendar</h1>
    <strong><?php echo $event->title ?></strong>
    <p>Location: <?php echo $event->location ?></p>
    <p>Date: <?php echo date_format($event->startTime, "M j, Y") ?></p>
    <p>Time: <?php echo date_format($event->startTime, "g:i a")." - ".date_format($event->endTime, "g:i a") ?></p>
    <a class="add" href="<?php echo $buttonUrl ?>">Add to my calendar</a>
  </div>
  <div id="calendar-sidebar">
    <div id="cal-view-title">Your calendar for <?php echo date_format($event->startTime, "m/d/Y") ?></div>
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
              $startDate = new DateTime($event['Start'], new DateTimeZone("UTC"));
              $startDate->setTimeZone(new DateTimeZone(date_default_timezone_get()));
              echo date_format($startDate, "g:i a"); 
            ?>
          </td>
          <td>
            <?php
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

<?php
include('common/footer.php');
?>
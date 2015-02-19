<?php
// create an array to set page-level variables
$page = array();
$page['title'] = 'Home';

// include the page header
include('common/header.php');
require('o365/Office365Service.php');

$loggedIn = !is_null($_SESSION['userName']);
if (!$loggedIn) {
  $redirectUri = "http".(($_SERVER["HTTPS"] == "on") ? "s://" : "://").$_SERVER["HTTP_HOST"]."/php-calendar/o365/authorize.php";
  $loginUrl = Office365Service::getLoginUrl($redirectUri);
}
?>

<h1>Welcome to php-calendar!</h1>
<div>Here are the upcoming shows for our Shakespearean Festival.</div>
<div><span id="table-title">Upcoming Shows</span></div>
<table class="show-list">
  <tr>
    <th class="button"></th>
    <th>Performance</th>
    <th>Location</th>
    <th>Voucher Required?</th>
    <th>Date</th>
    <th>Start</th>
    <th>End</th>
  </tr>

<?php
if ($_SESSION['events']) {
  $eventList = $_SESSION['events'];
}
else {
  $eventListMaker = new EventListMaker();
  $eventList = $eventListMaker->getEventList();
  $_SESSION['events'] = $eventList;
}

$altRow = false;

foreach($eventList as $index => $event) {
?>
  <tr<?php if ($altRow) echo ' class="alt"'; $altRow = !$altRow ?>>
    <?php
      if ($loggedIn) {
        $buttonUrl = "addToCalendar.php?showIndex=".$index;
        $buttonText = "Add to Calendar";
      }
      else {
        $buttonUrl = $loginUrl;
        $buttonText = "Connect my Calendar";
      }
    ?>
    <td class="button"><a class="action" href="<?php echo $buttonUrl ?>"><?php echo $buttonText ?></a></td>
    <td><?php echo $event->title ?></td>
    <td><?php echo $event->location ?></td>
    <td><?php echo ($event->voucherRequired ? "Yes" : "No") ?></td>
    <td><?php echo date_format($event->startTime, "M j, Y") ?></td>
    <td><?php echo date_format($event->startTime, "g:i a") ?></td>
    <td><?php echo date_format($event->endTime, "g:i a") ?></td>
  </tr>

<?php
}
?>

</table>

<?php
// include the page footer
include('common/footer.php');
?>
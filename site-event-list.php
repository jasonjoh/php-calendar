<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.
  // This file contains the EventList class, which generates a 
  // list of upcoming events to display on the website.
  
  class EventListMaker {
    private $_eventTitles = array("Romeo and Juliet",
                                  "A Midsummer Night's Dream",
                                  "Macbeth",
                                  "King Lear",
                                  "Othello",
                                  "Hamlet",
                                  "Much Ado About Nothing",
                                  "Henry V",
                                  "The Merchant of Venice",
                                  "The Taming of the Shrew");
                                  
    private $_eventLocations = array("Main Theater",
                                     "Theater in the Round",
                                     "Outdoor Amphitheater");
                                     
    public function getEventList(){
      error_log("getEventList called.");
      $eventList = array();
      
      // Start with today's date
      $currentTime = new DateTimeImmutable("now");
      error_log("Current time: ".date_format($currentTime, "m/d/Y H:i"));
      
      // For simplicity, all plays start at 7:00 PM
      $currentTime = $currentTime->setTime(19, 0, 0);
      error_log("Current time adjusted: ".date_format($currentTime, "m/d/Y H:i"));
      
      // Intervals for date manipulation
      $oneDay = new DateInterval("P1D");
      $playDuration = new DateInterval("PT3H");
      
      foreach ($this->_eventTitles as $title) {
        $event = new Event;
        $event->title = $title;
        $event->location = $this->_eventLocations[mt_rand(0,sizeof($this->_eventLocations)-1)];
        $event->voucherRequired = mt_rand(0,1) == 1;
        
        $event->startTime = $currentTime->add($oneDay);
        error_log("Start time: ".date_format($event->startTime, "m/d/Y H:i"));
        $event->endTime = $event->startTime->add($playDuration);
        error_log("End time: ".date_format($event->endTime, "m/d/Y H:i"));
        $eventList[] = $event;
        
        $currentTime = $currentTime->add($oneDay);
      }
      
      return $eventList;
    }
  }
  
  class Event {
    public $title;
    public $location;
    public $voucherRequired;
    public $startTime;
    public $endTime;
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
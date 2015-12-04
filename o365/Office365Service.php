<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.
  // This file contains the EventList class, which generates a 
  // list of upcoming events to display on the website.
  
  require_once("ClientReg.php");
  
  class Office365Service {
    private static $authority = "https://login.microsoftonline.com";
    private static $authorizeUrl = '/common/oauth2/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code';
    private static $tokenUrl = "/common/oauth2/token";
    private static $logoutUrl = '/common/oauth2/logout?post_logout_redirect_uri=%1$s';
    private static $outlookApiUrl = "https://outlook.office.com/api/v1.0";
    
    // Set this to true to enable Fiddler capture.
    // Note that if you have this set to true and you are not running Fiddler
    // on the web server, requests will silently fail.
    private static $enableFiddler = false;
    
    // Builds a login URL based on the client ID and redirect URI
    public static function getLoginUrl($redirectUri) {
      $loginUrl = self::$authority.sprintf(self::$authorizeUrl, ClientReg::$clientId, urlencode($redirectUri));
      error_log("Generated login URL: ".$loginUrl);
      return $loginUrl;
    }
    
    // Builds a logout URL based on the redirect URI.
    public static function getLogoutUrl($redirectUri) {
      $logoutUrl = self::$authority.sprintf(self::$logoutUrl, urlencode($redirectUri));
      error_log("Generated logout URL: ".$logoutUrl);
      return $logoutUrl;
    }
    
    // Sends a request to the token endpoint to exchange an auth code
    // for an access token.
    public static function getTokenFromAuthCode($authCode, $redirectUri) {
      // Build the form data to post to the OAuth2 token endpoint
      $token_request_data = array(
        "grant_type" => "authorization_code",
        "code" => $authCode,
        "redirect_uri" => $redirectUri,
        "resource" => "https://outlook.office365.com/",
        "client_id" => ClientReg::$clientId,
        "client_secret" => ClientReg::$clientSecret
      );
      
      // Calling http_build_query is important to get the data
      // formatted as Azure expects.
      $token_request_body = http_build_query($token_request_data);
      error_log("Request body: ".$token_request_body);
      
      $curl = curl_init(self::$authority.self::$tokenUrl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $token_request_body);
      
      if (self::$enableFiddler) {
        // ENABLE FIDDLER TRACE
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // SET PROXY TO FIDDLER PROXY
        curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:8888");
      }
      
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      if (self::isFailure($httpCode)) {
        return array('errorNumber' => $httpCode,
                     'error' => 'Token request returned HTTP error '.$httpCode);
      }
      
      // Check error
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      
      curl_close($curl);
      
      // The response is a JSON payload, so decode it into
      // an array.
      $json_vals = json_decode($response, true);
      error_log("TOKEN RESPONSE:");
      foreach ($json_vals as $key=>$value) {
        error_log("  ".$key.": ".$value);
      }
      
      return $json_vals;
    }
    
    // Sends a request to the token endpoint to get a new access token
    // from a refresh token.
    public static function getTokenFromRefreshToken($refreshToken) {
      // Build the form data to post to the OAuth2 token endpoint
      $token_request_data = array(
        "grant_type" => "refresh_token",
        "refresh_token" => $refreshToken,
        "resource" => "https://outlook.office365.com/",
        "client_id" => ClientReg::$clientId,
        "client_secret" => ClientReg::$clientSecret
      );
        
      $token_request_body = http_build_query($token_request_data);
      error_log("Request body: ".$token_request_body);
      
      $curl = curl_init(self::$authority.self::$tokenUrl);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $token_request_body);
      
      if (self::$enableFiddler) {
        // ENABLE FIDDLER TRACE
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // SET PROXY TO FIDDLER PROXY
        curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:8888");
      }
      
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      if (self::isFailure($httpCode)) {
        return array('errorNumber' => $httpCode,
                     'error' => 'Token request returned HTTP error '.$httpCode);
      }
      
      // Check error
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      
      curl_close($curl);
      
      // The response is a JSON payload, so decode it into
      // an array.
      $json_vals = json_decode($response, true);
      error_log("TOKEN RESPONSE:");
      foreach ($json_vals as $key=>$value) {
        error_log("  ".$key.": ".$value);
      }
      
      return $json_vals;
    }
    
    // Parses an ID token returned from Azure to get the user's
    // display name.
    public static function getUserName($id_token) {
      $token_parts = explode(".", $id_token);
      
      // First part is header, which we ignore
      // Second part is JWT, which we want to parse
      error_log("getUserName found id token: ".$token_parts[1]);
      
      // First, in case it is url-encoded, fix the characters to be 
      // valid base64
      $encoded_token = str_replace('-', '+', $token_parts[1]);
      $encoded_token = str_replace('_', '/', $encoded_token);
      error_log("After char replace: ".$encoded_token);
      
      // Next, add padding if it is needed.
      switch (strlen($encoded_token) % 4){
        case 0:
          // No pad characters needed.
          error_log("No padding needed.");
          break;
        case 2:
          $encoded_token = $encoded_token."==";
          error_log("Added 2: ".$encoded_token);
          break;
        case 3:
          $encoded_token = $encoded_token."=";
          error_log("Added 1: ".$encoded_token);
          break;
        default:
          // Invalid base64 string!
          error_log("Invalid base64 string");
          return null;
      }
      
      $json_string = base64_decode($encoded_token);
      error_log("Decoded token: ".$json_string);
      $jwt = json_decode($json_string, true);
      error_log("Found user name: ".$jwt['name']);
      return $jwt['name'];
    }
    
    // Uses the Calendar API's CalendarView to get all events
    // on a specific day. CalendarView handles expansion of recurring items.
    public static function getEventsForDate($access_token, $date) {
      error_log("getEventsForDate called:");
      error_log("  access token: ".$access_token);
      error_log("  date: ".date_format($date, "M j, Y g:i a (e)"));
      
      // Set the start of our view window to midnight of the specified day.
      $windowStart = $date->setTime(0,0,0);
      $windowStartUrl = self::encodeDateTime($windowStart);
      error_log("  Window start (UTC): ".$windowStartUrl);
      
      // Add one day to the window start time to get the window end.
      $windowEnd = $windowStart->add(new DateInterval("P1D"));
      $windowEndUrl = self::encodeDateTime($windowEnd);
      error_log("  Window end (UTC): ".$windowEndUrl);
      
      // Build the API request URL
      $calendarViewUrl = self::$outlookApiUrl."/Me/CalendarView?"
                        ."startDateTime=".$windowStartUrl
                        ."&endDateTime=".$windowEndUrl
                        ."&\$select=Subject,Start,End" // Use $select to limit the data returned
                        ."&\$orderby=Start";           // Sort the results by the start time.
      
      return self::makeApiCall($access_token, "GET", $calendarViewUrl);
    }
    
    // Use the Calendar API to add an event to the default calendar.
    public static function addEventToCalendar($access_token, $subject, $location, $startTime, $endTime, $attendeeString) {
      // Create a static body.
      $htmlBody = "<html><body>Added by php-calendar app.</body></html>";
      
      // Generate the JSON payload
      $event = array(
        "Subject" => $subject,
        "Location" => array("DisplayName" => $location),
        "Start" => self::encodeDateTime($startTime),
        "End" => self::encodeDateTime($endTime),
        "Body" => array("ContentType" => "HTML", "Content" => $htmlBody)
      );
      
      if (!is_null($attendeeString) && strlen($attendeeString) > 0) {
        error_log("Attendees included: ".$attendeeString);
        
        $attendeeAddresses = array_filter(explode(';', $attendeeString));
        
        $attendees = array();
        foreach($attendeeAddresses as $address) {
          error_log("Adding ".$address);
          
          $attendee = array(
            "EmailAddress" => array ("Address" => $address),
            "Type" => "Required"
          );
          
          $attendees[] = $attendee;
        }
        
        $event["Attendees"] = $attendees;
      }
      
      $eventPayload = json_encode($event);
      error_log("EVENT PAYLOAD: ".$eventPayload);
      
      $createEventUrl = self::$outlookApiUrl."/Me/Events";
      
      $response = self::makeApiCall($access_token, "POST", $createEventUrl, $eventPayload);
      
      // If the call succeeded, the response should be a JSON representation of the
      // new event. Try getting the Id property and return it.
      if ($response['Id']) {
        return $response['Id'];
      }
      
      else {
        error_log("ERROR: ".$response);
        return $response;
      }
    }
    
    // Use the Calendar API to add an attachment to an event.
    public static function addAttachmentToEvent($access_token, $eventId, $attachmentData) {
      // Generate the JSON payload
      $attachment = array(
        "@odata.type" => "#Microsoft.OutlookServices.FileAttachment",
        "Name" => "voucher.txt",
        "ContentBytes" => base64_encode($attachmentData)
      );
      
      $attachmentPayload = json_encode($attachment);
      error_log("ATTACHMENT PAYLOAD: ".$attachmentPayload);
      
      $createAttachmentUrl = self::$outlookApiUrl."/Me/Events/".$eventId."/Attachments";
      
      return self::makeApiCall($access_token, "POST", $createAttachmentUrl, $attachmentPayload);
    }
    
    // Make an API call.
    public static function makeApiCall($access_token, $method, $url, $payload = NULL) {
      // Generate the list of headers to always send.
      $headers = array(
        "User-Agent: php-calendar/1.0",         // Sending a User-Agent header is a best practice.
        "Authorization: Bearer ".$access_token, // Always need our auth token!
        "Accept: application/json",             // Always accept JSON response.
        "client-request-id: ".self::makeGuid(), // Stamp each new request with a new GUID.
        "return-client-request-id: true"        // Tell the server to include our request-id GUID in the response.
      );
      
      $curl = curl_init($url);
      
      if (self::$enableFiddler) {
        // ENABLE FIDDLER TRACE
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // SET PROXY TO FIDDLER PROXY
        curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1:8888");
      }
      
      switch(strtoupper($method)) {
        case "GET":
          // Nothing to do, GET is the default and needs no
          // extra headers.
          error_log("Doing GET");
          break;
        case "POST":
          error_log("Doing POST");
          // Add a Content-Type header (IMPORTANT!)
          $headers[] = "Content-Type: application/json";
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
          break;
        case "PATCH":
          error_log("Doing PATCH");
          // Add a Content-Type header (IMPORTANT!)
          $headers[] = "Content-Type: application/json";
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
          curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
          break;
        case "DELETE":
          error_log("Doing DELETE");
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
          break;
        default:
          error_log("INVALID METHOD: ".$method);
          exit;
      }
      
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      
      if (self::isFailure($httpCode)) {
        return array('errorNumber' => $httpCode,
                     'error' => 'Request returned HTTP error '.$httpCode);
      }
      
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        curl_close($curl);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      else {
        error_log("Response: ".$response);
        curl_close($curl);
        return json_decode($response, true);
      }
    }
    
    // This function convert a dateTime from local TZ to UTC, then
    // encodes it in the format expected by the Outlook APIs.
    public static function encodeDateTime($dateTime) {
      $utcDateTime = $dateTime->setTimeZone(new DateTimeZone("UTC"));
      
      $dateFormat = "Y-m-d\TH:i:s\Z";
      return date_format($utcDateTime, $dateFormat);
    }
    
    // This function generates a random GUID.
    public static function makeGuid(){
        if (function_exists('com_create_guid')) {
          error_log("Using 'com_create_guid'.");
          return strtolower(trim(com_create_guid(), '{}'));
        }
        else {
          error_log("Using custom GUID code.");
          $charid = strtolower(md5(uniqid(rand(), true)));
          $hyphen = chr(45);
          $uuid = substr($charid, 0, 8).$hyphen
                 .substr($charid, 8, 4).$hyphen
                 .substr($charid, 12, 4).$hyphen
                 .substr($charid, 16, 4).$hyphen
                 .substr($charid, 20, 12);
                 
          return $uuid;
        }
    }
    
    public static function isFailure($httpStatus){
      // Simplistic check for failure HTTP status
      return ($httpStatus >= 400);
    }
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

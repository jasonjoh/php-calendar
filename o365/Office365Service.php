<?php
  // This file contains the EventList class, which generates a 
  // list of upcoming events to display on the website.
  
  require("ClientReg.php");
  
  class Office365Service {
    private static $authority = "https://login.windows.net";
    private static $authorizeUrl = '/common/oauth2/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code';
    private static $tokenUrl = "/common/oauth2/token";
    private static $logoutUrl = '/common/oauth2/logout?post_logout_redirect_uri=%1$s';
    private static $outlookApiUrl = "https://outlook.office365.com/api/v1.0";
    
    private static $enableFiddler = true;
    
    public static function getLoginUrl($redirectUri) {
      $loginUrl = self::$authority.sprintf(self::$authorizeUrl, ClientReg::$clientId, urlencode($redirectUri));
      error_log("Generated login URL: ".$loginUrl);
      return $loginUrl;
    }
    
    public static function getLogoutUrl($redirectUri) {
      $logoutUrl = self::$authority.sprintf(self::$logoutUrl, urlencode($redirectUri));
      error_log("Generated logout URL: ".$logoutUrl);
      return $logoutUrl;
    }
    
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
      curl_close($curl);
      
      $json_vals = json_decode($response, true);
      error_log("TOKEN RESPONSE:");
      foreach ($json_vals as $key=>$value) {
        error_log("  ".$key.": ".$value);
      }
      
      return $json_vals;
    }
    
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
    
    public static function getEventsForDate($access_token, $date) {
      error_log("getEventsForDate called:");
      error_log("  access token: ".$access_token);
      error_log("  date: ".date_format($date, "M j, Y g:i a (e)"));
      
      $windowStart = $date->setTime(0,0,0);
      $windowStartUrl = self::encodeDateTime($windowStart);
      error_log("  Window start (UTC): ".$windowStartUrl);
      
      $windowEnd = $windowStart->add(new DateInterval("P1D"));
      $windowEndUrl = self::encodeDateTime($windowEnd);
      error_log("  Window end (UTC): ".$windowEndUrl);
      
      $calendarViewUrl = self::$outlookApiUrl."/Me/CalendarView?"
                        ."startDateTime=".$windowStartUrl
                        ."&endDateTime=".$windowEndUrl
                        ."&\$select=Subject,Start,End"
                        ."&\$orderby=Start";
      
      return self::makeApiCall($access_token, "GET", $calendarViewUrl);
    }
    
    public static function addEventToCalendar($access_token, $subject, $location, $startTime, $endTime) {
      $htmlBody = "<html><body>Added by php-calendar app.</body></html>";
      $event = array(
        "Subject" => $subject,
        "Location" => array("DisplayName" => $location),
        "Start" => self::encodeDateTime($startTime),
        "End" => self::encodeDateTime($endTime),
        "Body" => array("ContentType" => "HTML", "Content" => $htmlBody)
      );
      
      $eventPayload = json_encode($event);
      error_log("EVENT PAYLOAD: ".$eventPayload);
      
      $createEventUrl = self::$outlookApiUrl."/Me/Events";
      
      $response = self::makeApiCall($access_token, "POST", $createEventUrl, $eventPayload);
      
      // NEED TO PARSE RESPONSE (if not error) and get the ID
      // Then call addAttachmentToEvent to add the attachment.
      if ($response['Id']) {
        return $response['Id'];
      }
      
      else {
        error_log("ERROR: ".$response);
        return null;
      }
    }
    
    public static function addAttachmentToEvent($access_token, $eventId, $attachmentData) {
      $attachment = array(
        "@odata.type" => "#Microsoft.OutlookServices.FileAttachment",
        "Name" => "voucher.txt",
        "ContentBytes" => base64_encode($attachmentData)
      );
      
      $attachmentPayload = json_encode($attachment);
      error_log("ATTACHMENT PAYLOAD: ".$attachmentPayload);
      
      $createAttachmentUrl = self::$outlookApiUrl."/Me/Events/".$eventId."/Attachments";
      
      $response = self::makeApiCall($access_token, "POST", $createAttachmentUrl, $attachmentPayload);
    }
    
    public static function makeApiCall($access_token, $method, $url, $payload = NULL) {
      $headers = array(
        "User-Agent: php-calendar/1.0",
        "Authorization: Bearer ".$access_token,
        "Accept: application/json",
        "client-request-id: ".self::makeGuid(),
        "return-client-request-id: true"
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
          error_log("Doing GET");
          break;
        case "POST":
          error_log("Doing POST");
          $headers[] = "Content-Type: application/json";
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
          break;
        case "PATCH":
          error_log("Doing PATCH");
          break;
        case "DELETE":
          error_log("Doing DELETE");
          break;
        default:
          error_log("INVALID METHOD: ".$method);
          exit;
      }
      
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      if (!$response) {
        $error = curl_error($curl);
        error_log("HTTP ERROR: ".$error);
        curl_close($curl);
        return $error;
      }
      else {
        error_log("Response: ".$response);
        curl_close($curl);
        return json_decode($response, true);
      }
    }
    
    public static function encodeDateTime($dateTime) {
      $utcDateTime = $dateTime->setTimeZone(new DateTimeZone("UTC"));
      
      $dateFormat = "Y-m-d\TH:i:s\Z";
      return date_format($utcDateTime, $dateFormat);
    }
    
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
  }
    
?>
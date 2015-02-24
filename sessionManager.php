<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file.
  require_once('o365/Office365Service.php');

  class SessionManager {
    public static function checkResponseAndRefreshToken($response) {
      if ($response['errorNumber'] && $response['errorNumber'] == 401) {
        error_log("Request returned 401, attempting to refresh token.");
        // Use the refresh token to get a new token and update session variables.
        $newTokenInfo = Office365Service::getTokenFromRefreshToken($_SESSION['refreshToken']);
        if ($newTokenInfo['access_token']) {
          $_SESSION['accessToken'] = $newTokenInfo['access_token'];
          $_SESSION['refreshToken'] = $newTokenInfo['refresh_token'];
          
          error_log("Retrieved new token and updated session variables.");
          return true;
        }
        
        error_log("No access token returned.");
        return false;
      }
      else {
        return false;
      }
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
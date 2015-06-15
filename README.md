# PHP Calendar API Sample #

[日本 (日本語)](https://github.com/jasonjoh/php-calendar/blob/master/loc/readme-ja.md) (Japanese)

This sample shows how you can use the [Calendar API](https://msdn.microsoft.com/office/office365/APi/calendar-rest-operations) from PHP. The sample app is an "upcoming shows" app for a fictional community theater's Shakespearean festival. Users can connect their Office 365 account and add events to their calendar for the show times they are attending. The user has the option of inviting friends, which will send a meeting request to each invited friend. 

## API features used ##

- Creating events on a user's default calendar
- Adding attachments to events
- Adding attendees to events
- Using a [calendar view](https://msdn.microsoft.com/office/office365/APi/calendar-rest-operations#GetCalendarView) to expand recurring events and display all appointments for a single day.

## Required software ##

- [PHP 5.6](http://php.net/downloads.php)
- A web server capable of serving PHP.

In my testing I used IIS 8 installed on a Windows 8.1 laptop. I installed PHP 5.6.0 using the [Web Platform Installer](http://www.microsoft.com/web/downloads/platform.aspx) (Windows/IIS only).

## Running the sample ##

It's assumed that you have PHP installed before starting, and that your web server is configured to process and server PHP files. 

1. Download or fork the sample project.
1. Create a new directory in your web root directory called `php-calendar`. Copy the files from the repository to this directory.
1. [Register the app in Azure Active Directory](https://github.com/jasonjoh/office365-azure-guides/blob/master/RegisterAnAppInAzure.md). The app should be registered as a web app with a Sign-on URL of `http://localhost/php-calendar`, and should be given the permission to "Have full access to users' calendars", which is available in the "Delegated Permissions" dropdown.
1. Edit the `.\o365\ClientReg.php` file. 
	1. Copy the client ID for your app obtained during app registration and paste it as the value for the `$clientId` variable. 
	1. Copy the key you created during app registration and paste it as the value for the `$clientSecret` variable.
	1. Save the file.
1. If your PHP installation is not configured with updated CA certificates to verify SSL, requests will fail unless you run Fiddler on the server and set the `$enableFiddler` variable to `true` in `Office365Service.php`. Alternatively, you can insert the following line immediately before any call to `curl_exec`. **However,** it should be noted that doing so disables any SSL verification, which should NOT be done in production.

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
1. Open a web browser and browse to `http://localhost/php-calendar/home.php`.
1. You should see a list of upcoming show times for various Shakespearean plays. Click on any of the "Connect my Calendar" buttons to sign in to Office 365.
1. Once signed in you should be redirected back to the home page, and the buttons should now read "Add to Calendar." Click the button next to a specific show time to add it to your calendar. Events with a "Voucher Required" field of Yes will include the voucher as an attachment on the event.

## Copyright ##

Copyright (c) Microsoft. All rights reserved.

----------
Connect with me on Twitter [@JasonJohMSFT](https://twitter.com/JasonJohMSFT)

Follow the [Exchange Dev Blog](http://blogs.msdn.com/b/exchangedev/)
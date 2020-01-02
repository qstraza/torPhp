# Running the application
As an exmaple you can use the following docker-compose.yml file which will
help you get started.

```version: '2'
services:
  php:
    image: torphp
    volumes:
      - ./firefox-profiles:/root/.mozilla/firefox
      - ./google-jsons:/google-jsons:ro

  selenium:
    image: selenium/standalone-firefox-debug:3.4.0-chromium
    ports:
      - "5900:5900"
```

# Remote Desktop (VNC)
Used selenium image has VNC server on it. This means you can connect to it
using a VNC client (such as tigervnc) and view its progress. Connect to
127.0.0.1:5900. Password is 'secret'.

# Preparing Firefox Profile
Note that docker selenium image uses Firefox version 53. It is not possible
to create a profile with Firefox version >70. Download Firefox 53 or 54.

Run profile manager
(mac)
/Applications/Firefox.app/Contents/MacOS/firefox-bin -P

create new profile (name is not important) and save it.

Open up FF (make sure newly created profile is loaded) and
add user certificate for accessing TOR
under privacy & settings, "Certificates - When a server requests your personal certificate" select "Select one automatically"

Go to https://etor.mnz.gov.si/ and confirm/add the exception.

Close FF.

Go to profile folder and enter newly created profile. ZIP everything inside.
This ZIP has to be shared to php container.

# Spreadsheet Permissions
Go to Google Console
https://console.developers.google.com/apis/credentials

Create new Credentials -> Service account key.

Download JSON and put it in google-jsons/. Name it as company name.

Go to Google Spreadsheet and add editorial permission/access to the user which
you created in the console (open up JSON file and use email address).

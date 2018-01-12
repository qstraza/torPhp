# Preparing Firefox Profile

Run profile manager
(mac)
/Applications/Firefox.app/Contents/MacOS/firefox-bin -P

create new profile (name is not important) and save it.

Open up FF (make sure newly created profile is loaded) and
add user certificate for accessing TOR
under privacy & settings, "Certificates - When a server requests your personal certificateselect" select "Select one automatically"

Go to http://etor.mnz.gov.si/ and confirm/add the exception.

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
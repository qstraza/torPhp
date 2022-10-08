import os.path
import sys
import subprocess

users = {
    "rojal": {
        "spreadsheetId": "1Z1TnmRwORKK5GiljJFCg9s0ozn6UpSQsJgRjW2bV0KA", 
        "type": "",
        "actions": {
            "realiziraj": "orozje",
            "nabavi": "prevzemiOrozje",
            "realizirajStrelivo": "strelivo",
            "nabaviStrelivo": "prevzemiStrelivo"
        }
    },
    "rti": {
        "spreadsheetId": "1puIWqdHRmwz--eKGxWz50DwAYBkbELNOCQ9dUCzuqEw",
        "type": "multi",
        "actions": {
            "realiziraj": "zapisnik",
            "izdelaj": "zapisnik"
        }
    }
}

if len(sys.argv) != 2:
    sys.exit()

dir = sys.argv[1]
runsDir = dir + "/cron/runs/"
for file in os.listdir(runsDir):
    try:
        fileSplit = file.split("_")
        user = fileSplit[0]
        action = fileSplit[1]
        if len(fileSplit) == 3:
            if fileSplit[2] == "run":
                continue
            else:
                os.remove(runsDir + file)
    except: 
        os.remove(runsDir + file)
        continue
    if (not user in users):
        os.remove(runsDir + file)
        continue
    if (not action in users[user]['actions']):
        os.remove(runsDir + file)
        continue

    os.rename(runsDir + file, runsDir + file + "_run")
    subprocess.Popen([
        "/usr/local/bin/docker-compose", 
        "-f",
        dir + "/docker-compose.yml",
        "run",
        "--rm",
        "-w",
        "/app",
        "php",
        "php",
        "main.php",
        user,
        users[user]['spreadsheetId'],
        action,
        users[user]['actions'][action],
        users[user]['type']
    ])
    # os.remove(runsDir + file + "_run")

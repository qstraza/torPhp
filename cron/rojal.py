import os.path
import sys
import subprocess

spreadsheetId = "1Z1TnmRwORKK5GiljJFCg9s0ozn6UpSQsJgRjW2bV0KA"

if len(sys.argv) != 2:
    sys.exit()

dir = sys.argv[1]

for process in [["realiziraj", "orozje"], ["nabavi", "prevzemiOrozje"], ["realizirajStrelivo", "strelivo"], ["nabaviStrelivo", "prevzemiStrelivo"]]:
    try:
        action = process[0]
        sheetName = process[1]
        filepath = dir + "/cron/runs/rojal_" + sheetName
        if os.path.exists(filepath):
            os.rename(filepath, filepath + "_run")
            subprocess.call([
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
                "rojal",
                spreadsheetId,
                action,
                sheetName
            ])
            os.remove(filepath + "_run")
    except:
        pass


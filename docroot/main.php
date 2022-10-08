<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 30/11/2017
 * Time: 17:27
 */

require_once __DIR__ . '/vendor/autoload.php';

use qstraza\torphp\Data\NabavljenoOrozje;
use qstraza\torphp\Data\NabavljenoStrelivo;
use qstraza\torphp\Data\Orozje;
use qstraza\torphp\Data\OrozjeItem;
use qstraza\torphp\Data\Strelivo;
use qstraza\torphp\Data\StrelivoItem;
use qstraza\torphp\Realizacija\TorRealizacijaOrozja;
use qstraza\torphp\Realizacija\TorRealizacijaStreliva;
use qstraza\torphp\TorIzdelanoOrozje;
use qstraza\torphp\TorIskanje;
use qstraza\torphp\TorNabavljenoOrozje;
use qstraza\torphp\TorNabavljenoStrelivo;

function deleteJob($clientName, $action) {
    try {
        unlink("/runs/" . $clientName . "_" . $action . "_run");
    }
    catch (\Exception $e) {

    }
}

if (count($argv) >= 5) {
    date_default_timezone_set('Europe/Ljubljana');

    $clientName = $argv[1];
    $spreadsheetId = $argv[2];
    $action = $argv[3];
    $worksheetName = $argv[4];
    $GLOBALS['multiSerialNumbersOptionEnabled'] = false;
    if (isset($argv[5]) && $argv[5] == "multi") {
        // How do we want to parse the serial numbers and where to output the errors? If
        // this is set to true, logs will be written to a separate spreadsheet. If it is
        // set to false, each line next to the serial number within the same spreadsheet
        // will get a log status and also field "realizirano" will update.
        $GLOBALS['multiSerialNumbersOptionEnabled'] = true;
    }
//    switch ($argv[1]) {
//        case 'rti':
//            $spreadsheetId = '1puIWqdHRmwz--eKGxWz50DwAYBkbELNOCQ9dUCzuqEw';
//            break;
//        case 'rojal':
//            $spreadsheetId = '1Z1TnmRwORKK5GiljJFCg9s0ozn6UpSQsJgRjW2bV0KA';
//            break;
//    }
    try {

        switch ($action) {
            case 'realiziraj':
                $orozje = new Orozje($spreadsheetId, $worksheetName, $clientName);
                /** @var OrozjeItem $nerealiziranoOrozje */
                // Vrne vse nerealizirane itme iz spreadsheeta
                $nerealiziranoOrozje = $orozje->getNerealizirane();
                $tor = new TorRealizacijaOrozja($clientName);

                if ($GLOBALS['multiSerialNumbersOptionEnabled']) {
                    /** @var OrozjeItem $item */
                    $itemsPerRow = [];
                    foreach ($nerealiziranoOrozje as $item) {
                        $itemsPerRow[$item->getSpreadsheetEntry()['rowIndex']][] = $item;
                    }
                    foreach ($itemsPerRow as $row) {
                        /** @var OrozjeItem $item */
                        $batchLength = count($row);
                        $batchAllOK = true;
                        foreach ($row as $i => $item) {
                            try {
                                $item->realiziraj($tor);
                                $orozje->logsMulti($item->getSpreadsheetEntry()['rowIndex'], $item->getSerijska(), $item->getReturnMessage(), $item->isError());
                                if ($item->isError()) {
                                    $batchAllOK = false;
                                }
                            } catch (\Exception $e) {
                                $orozje->logsMulti($item->getSpreadsheetEntry()['rowIndex'], $item->getSerijska(), $e->getMessage(), true);
                                $batchAllOK = false;
                            }
                            // This was last element in the batch. If there were not errors, change field izdelano to DA.
                            if ($batchAllOK && $batchLength == $i + 1) {
                                $orozje->setFieldDa($item->getSpreadsheetEntry()['rowIndex'],"Realizirano");
                            }
                        }
                    }
                }
                else {
                    /** @var OrozjeItem $item */
                    foreach ($nerealiziranoOrozje as $item) {
                        try {
                            $item->realiziraj($tor);
                            $orozje->logs($item, "Realizirano", $item->getReturnMessage(), $item->isError());
                        } catch (\Exception $e) {
                            $orozje->logs($item, "Realizirano", $e->getMessage(), true);
                        }
                    }
                }

                break;
            case 'izdelaj':
                $orozje = new Orozje($spreadsheetId, $worksheetName, $clientName);
                /** @var OrozjeItem $nerealiziranoOrozje */
                // Vrne vse nerealizirane itme iz spreadsheeta
                $neizdelanoOrozje = $orozje->getNeizdelane();
                $tor = new TorIzdelanoOrozje($clientName);
                /** @var OrozjeItem $item */
                $itemsPerRow = [];
                foreach ($neizdelanoOrozje as $item) {
                    $itemsPerRow[$item->getSpreadsheetEntry()['rowIndex']][] = $item;
                }
                $prviItem = true;
                foreach ($itemsPerRow as $row) {
                    /** @var OrozjeItem $item */
                    $batchLength = count($row);
                    $batchAllOK = true;
                    foreach ($row as $i => $item) {
                        try {
                            if ($prviItem) {
                                $item->izdelaj($tor);
                                $prviItem = false;
                            }
                            else {
                                $item->izdelajIstiModel($tor);
                            }
                            $orozje->logsMulti($item->getSpreadsheetEntry()['rowIndex'], $item->getSerijska(), $item->getReturnMessage(), $item->isError());
                            if ($item->isError()) {
                                $batchAllOK = false;
                            }
                        } catch (\Exception $e) {
                            $orozje->logsMulti($item->getSpreadsheetEntry()['rowIndex'], $item->getSerijska(), $e->getMessage(), true);
                            $batchAllOK = false;
                        }
                        // This was last element in the batch. If there were not errors, change field izdelano to DA.
                        if ($batchAllOK && $batchLength == $i + 1) {
                            $orozje->setFieldDa($item->getSpreadsheetEntry()['rowIndex'],"Izdelano");
                        }
                    }
                }
                break;
            case 'nabavi':
                $orozje = new NabavljenoOrozje($spreadsheetId, $worksheetName, $clientName);
                /** @var OrozjeItem $nabavljeno */
                // Vrne vse nerealizirane itme iz spreadsheeta
                $nabavljeno = $orozje->getNabavljeno();
                $tor = new TorNabavljenoOrozje($clientName);
                /** @var OrozjeItem $item */
                $itemsPerRow = [];
                foreach ($nabavljeno as $item) {
                    $itemsPerRow[$item->getSpreadsheetEntry()['rowIndex']][] = $item;
                }
                $prviItem = true;
                foreach ($itemsPerRow as $row) {
                    /** @var OrozjeItem $item */
                    foreach ($row as $i => $item) {
                        try {
                            if ($prviItem) {
                                $item->nabavi($tor);
                                $prviItem = false;
                            }
                            else {
                                $item->nabaviOdistegaDobavitelja($tor);
                            }
                            $orozje->logs($item->getSpreadsheetEntry()['rowIndex'], $item->getSerijska(), $item->getReturnMessage(), $item->isError());
                        } catch (\Exception $e) {
                            $orozje->logs($item->getSpreadsheetEntry()['rowIndex'], $item->getSerijska(), $e->getMessage(), true);
                        }
                    }
                }
                break;
            case 'realizirajStrelivo':
                $strelivo = new Strelivo($spreadsheetId, $worksheetName, $clientName);
                /** @var StrelivoItem $nerealiziranoStrelivo */
                // Vrne vse nerealizirane itme iz spreadsheeta
                $nerealiziranoStrelivo = $strelivo->getNerealizirane();
                $tor = new TorRealizacijaStreliva($clientName);
                /** @var StrelivoItem $item */
                foreach ($nerealiziranoStrelivo as $item) {
                    try {
                        $item->realiziraj($tor);
                        $strelivo->logs($item->getSpreadsheetEntry()['rowIndex'], $item->getReturnMessage(), $item->isError());
                    } catch (\Exception $e) {
                        $strelivo->logs($item->getSpreadsheetEntry()['rowIndex'], $e->getMessage(), true);
                    }
                }

                break;
            case 'nabaviStrelivo':
                $strelivo = new NabavljenoStrelivo($spreadsheetId, $worksheetName, $clientName);
                /** @var StrelivoItem $nabavljeno */
                // Vrne vse nerealizirane itme iz spreadsheeta
                $nabavljeno = $strelivo->getNabavljeno();
                $tor = new TorNabavljenoStrelivo($clientName);
                $prviItem = true;
                /** @var StrelivoItem $item */
                foreach ($nabavljeno as $item) {
                    try {
                        if ($prviItem) {
                            $item->nabavi($tor);
                            $prviItem = false;
                        }
                        else {
                            $item->nabaviOdistegaDobavitelja($tor);
                        }
                        $strelivo->logs($item->getSpreadsheetEntry()['rowIndex'], $item->getReturnMessage());
                    } catch (\Exception $e) {
                        $strelivo->logs($item->getSpreadsheetEntry()['rowIndex'], $e->getMessage());
                    }
                }
                break;
            case 'trenutnoStrelivo':
                $tor = new TorIskanje($clientName);
                $tor->setStrelivoDelStreliva('Strelivo izraženo v kosih');
                $tor->setVrstaEvidence('Nabavljeno in prodano strelivo');
                $tor->confirmPage();
                sleep(1);
                $tor->lastPage();
                do {
                    sleep(2);
                    $pageNum = $tor->getCurrentPageNumber();
                    for ($i = 0; $i < $tor->getSteviloZadetkov(); $i++) {
                        sleep(2);
                        $tor->odpriZadetek($i);
                        sleep(2);
                        $ammoInfo = $tor->getAmmoInfo();

                        echo $ammoInfo->znamka . ";";
                        echo $ammoInfo->proizvajalec . ";";
                        echo $ammoInfo->vrsta . ";";
                        echo $ammoInfo->kaliber . ";";
                        echo $ammoInfo->qtyBought . ";";
                        echo $ammoInfo->stockLeft . ";";
                        echo $pageNum . ";";
                        echo($i + 1);
                        echo "\n";
                        $tor->goBack();
                    }
                    sleep(2);
                } while ($tor->prevPage());

                break;
            case 'getModels':
                $tor = new TorIskanje($clientName);

                $lines = explode("\n", $contents = file_get_contents('/app/lines.csv'));
                $i = 1;
                foreach ($lines as $line) {
                    $serial = trim($line);
                    if (strlen($serial) > 3) {
                        $tor->openItemBySerial($serial, 'Podrobnosti');
                        $model = $tor->getModelFromPage();
                        $dobavnica = $tor->getDobavnicaFromPage();
                        echo $serial . ';' . $model . ';' . $dobavnica . "\n";
                        file_put_contents("out.csv", $serial . ';' . $model . ';' . $dobavnica . "\n", FILE_APPEND | LOCK_EX);
                    } else {
                        continue;
                    }
                    $tempcontent = str_replace($line, "", $contents);
                    $contents = $tempcontent;
                    $fp = fopen('/app/lines.csv', "w");
                    fwrite($fp, $contents);
                    fclose($fp);
                }
                break;
            case 'fix':
                $tor = new TorIskanje($clientName);

                $lines = explode("\n", $contents = file_get_contents('/app/lines.csv'));
                print_r($contents);
                echo "\n";
                $i = 1;
                foreach ($lines as $line) {
                    $data = explode(";", $line);
                    if (is_array($data) && count($data) == 3) {
                        $serial = $data[2];
                        $type = $data[1];
                        $kategorija = $data[0];
                        echo $i++ . '/' . count($lines) . ' - ' . $serial . "\n";

                        $tor->openItemBySerial(trim($serial));
                        // $tor->setKategorijaOrozja(trim($kategorija));
                        // sleep(2);
                        $tor->setTipVrstaOrozja(trim($type));
                        $tor->savePage();


                    } else {
                        continue;
                    }
                    $tempcontent = str_replace($line, "", $contents);
                    $contents = $tempcontent;
                    $fp = fopen('/app/lines.csv', "w");
                    fwrite($fp, $contents);
                    fclose($fp);
                }
                break;
            case 'brisiRealizacijo':
                $tor = new TorIskanje($clientName);
                $lines = explode("\n", $contents = file_get_contents('/app/lines.csv'));
                print_r($contents);
                echo "\n";
                $i = 1;
                foreach ($lines as $line) {
                    $data = explode(";", $line);
                    if (is_array($data) && count($data) > 1) {
                        $serial = trim($data[0]);
                        $kategorija = trim($data[1]);
                        $tipvrsta = trim($data[2]);
                        echo $i++ . '/' . count($lines) . ' - ' . $serial . "\n";

                        // $tor->deleteRealizacijoBySerial($serial);

                        // sleep(2);
                        $tor->openItemBySerial($serial);
                        $tor->setKategorijaOrozja($kategorija);
                        sleep(1);
                        $tor->setTipVrstaOrozja($tipvrsta);
                        $tor->savePage();
                    } else {
                        continue;
                    }
                    $tempcontent = str_replace($line, "", $contents);
                    $contents = $tempcontent;
                    $fp = fopen('/app/lines.csv', "w");
                    fwrite($fp, $contents);
                    fclose($fp);
                }
                break;
            case 'fix2':
                $tor = new TorIskanje($clientName);

                $lines = explode("\n", $contents = file_get_contents('/app/lines.csv'));
                print_r($contents);
                echo "\n";
                $i = 1;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strlen($line) > 4) {
                        $serial = $line;
                        echo $i++ . '/' . count($lines) . ' - ' . $serial . "\n";

                        $tor->openItemBySerial($serial);
                        $tor->setKategorijaOrozja(trim('C7'));
                        $tor->savePage();


                    } else {
                        continue;
                    }
                    $tempcontent = str_replace($line, "", $contents);
                    $contents = $tempcontent;
                    $fp = fopen('/app/lines.csv', "w");
                    fwrite($fp, $contents);
                    fclose($fp);
                }

        }
    } catch (\Exception $e) {
//        echo $e->getMessage();
    }
    deleteJob($clientName, $action);
    $tor->logOut();
}
else {
    exit("Arguments to use the script: php {$argv[0]} clientName spredsheetId action worksheetName [multi]\n\t Example: php main.php rojal 1puasdfmwz--eKGasfasdzuqEw realiziraj orozje2022 multi\n");
}


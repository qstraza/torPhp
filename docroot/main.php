<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 30/11/2017
 * Time: 17:27
 */

require_once __DIR__ . '/vendor/autoload.php';
use qstraza\torphp\Data\Orozje;
use qstraza\torphp\Data\OrozjeItem;
use qstraza\torphp\Realizacija\TorRealizacijaOrozja;
use qstraza\torphp\TorIzdelanoOrozje;
use qstraza\torphp\TorIskanje;

if (count($argv) >= 2) {
  $spreadsheetId = null;
  switch ($argv[1]) {
    case 'rti':
      $spreadsheetId = '1puIWqdHRmwz--eKGxWz50DwAYBkbELNOCQ9dUCzuqEw';
      break;
    case 'rojal':
      $spreadsheetId = '1Z1TnmRwORKK5GiljJFCg9s0ozn6UpSQsJgRjW2bV0KA';
      break;
  }
  try {

    switch ($argv[2]) {
      case 'realiziraj':
        $tor = new TorRealizacijaOrozja($argv[1]);
        $orozje = new Orozje($spreadsheetId, $argv[3], $argv[1]);
        /** @var OrozjeItem $nerealiziranoOrozje */
        // Vrne vse nerealizirane itme iz spreadsheeta
        $nerealiziranoOrozje = $orozje->getNerealizirane();
        /** @var OrozjeItem $item */
        foreach ($nerealiziranoOrozje as $item) {
          try {
            $item->realiziraj($tor);
            $orozje->logs($item->getSerijska(), "Realizirano", $item->getReturnMessage(), $item->isError());
          }
          catch(\Exception $e) {
            $orozje->logs($item->getSerijska(), "Realizirano", $e->getMessage(), true);
          }
        }

        break;
      case 'izdelaj':
        $tor = new TorIzdelanoOrozje($argv[1]);
        $orozje = new Orozje($spreadsheetId, $argv[3], $argv[1]);
        /** @var OrozjeItem $nerealiziranoOrozje */
        // Vrne vse nerealizirane itme iz spreadsheeta
        $neizdelanoOrozje = $orozje->getNeizdelane();
        /** @var OrozjeItem $item */
        foreach ($neizdelanoOrozje as $item) {
          try {
            $item->izdelaj($tor);
            $orozje->logs($item->getSerijska(), "Izdelano", $item->getReturnMessage(), $item->isError());
          }
          catch(\Exception $e) {
            $orozje->logs($item->getSerijska(), "Izdelano", $e->getMessage(), true);
          }
        }
        break;
      case 'trenutnoStrelivo':
        $tor = new TorIskanje($argv[1]);
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
            echo ($i+1);
            echo "\n";
            $tor->goBack();
          }
          sleep(2);
        } while ($tor->prevPage());

        break;
        case 'getModels':
            $tor = new TorIskanje($argv[1]);

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
                }
                else {
                    continue;
                }
                $tempcontent = str_replace($line, "", $contents);
                $contents = $tempcontent;
                $fp = fopen('/app/lines.csv', "w");
                fwrite($fp, $contents);
                fclose($fp);
            }
        case 'fix':
            $tor = new TorIskanje($argv[1]);

            $lines = explode("\n", $contents = file_get_contents('/app/lines.csv'));
            print_r($contents);echo "\n";
            $i = 1;
            foreach ($lines as $line) {
                $data = explode(";", $line);
                if (is_array($data) && count($data) == 3) {
                    $serial = $data[2];
                    $type = $data[1];
                    $kategorija = $data[0];
                    echo $i++ . '/' . count($lines). ' - ' . $serial . "\n";

                    $tor->openItemBySerial(trim($serial));
                    // $tor->setKategorijaOrozja(trim($kategorija));
                    // sleep(2);
                    $tor->setTipVrstaOrozja(trim($type));
                    $tor->savePage();


                }
                else {
                    continue;
                }
                $tempcontent = str_replace($line, "", $contents);
                $contents = $tempcontent;
                $fp = fopen('/app/lines.csv', "w");
                fwrite($fp, $contents);
                fclose($fp);
            }

        case 'brisiRealizacijo':
            $tor = new TorIskanje($argv[1]);
            $tor->deleteRealizacijoBySerial('00728');
            break;
            $lines = explode("\n", $contents = file_get_contents('/app/lines.csv'));
            print_r($contents);echo "\n";
            $i = 1;
            foreach ($lines as $line) {
                $data = explode(";", $line);
                if (is_array($data) && count($data)) {
                    $serial = $data[0];
                    echo $i++ . '/' . count($lines). ' - ' . $serial . "\n";

                    $tor->deleteRealizacijoBySerial(trim($serial), 'Podrobnosti');
                }
                else {
                    continue;
                }
                $tempcontent = str_replace($line, "", $contents);
                $contents = $tempcontent;
                $fp = fopen('/app/lines.csv', "w");
                fwrite($fp, $contents);
                fclose($fp);
            }

        case 'fix2':
            $tor = new TorIskanje($argv[1]);

            $lines = explode("\n", $contents = file_get_contents('/app/lines.csv'));
            print_r($contents);echo "\n";
            $i = 1;
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) > 4) {
                    $serial = $line;
                    echo $i++ . '/' . count($lines). ' - ' . $serial . "\n";

                    $tor->openItemBySerial($serial);
                    $tor->setKategorijaOrozja(trim('C7'));
                    $tor->savePage();


                }
                else {
                    continue;
                }
                $tempcontent = str_replace($line, "", $contents);
                $contents = $tempcontent;
                $fp = fopen('/app/lines.csv', "w");
                fwrite($fp, $contents);
                fclose($fp);
            }

    }
  }
  catch (\Exception $e) {
    echo $e->getMessage();
  }
}
$tor->logOut();

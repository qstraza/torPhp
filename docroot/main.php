<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 30/11/2017
 * Time: 17:27
 */

require_once __DIR__ . '/vendor/autoload.php';
use qstraza\torPhp\Data\Orozje;
use qstraza\torPhp\Realizacija\TorRealizacijaOrozja;
use qstraza\torPhp\TorIzdelanoOrozje;

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
        /** @var \qstraza\torPhp\Data\OrozjeItem $nerealiziranoOrozje */
        // Vrne vse nerealizirane itme iz spreadsheeta
        $nerealiziranoOrozje = $orozje->getNerealizirane();
        /** @var \qstraza\torPhp\Data\OrozjeItem $item */
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
        /** @var \qstraza\torPhp\Data\OrozjeItem $nerealiziranoOrozje */
        // Vrne vse nerealizirane itme iz spreadsheeta
        $neizdelanoOrozje = $orozje->getNeizdelane();
        /** @var \qstraza\torPhp\Data\OrozjeItem $item */
        foreach ($neizdelanoOrozje as $item) {
          try {
            $item->izdelaj($tor);
            $orozje->logs($item->getSerijska(), "Izdelano", $item->getReturnMessage(), $item->isError());
          }
          catch(\Exception $e) {
            $orozje->logs($item->getSerijska(), "Izdelano", $e->getMessage(), true);
          }
        }
    }
  }
  catch (\Exception $e) {
    echo $e->getMessage();
  }
}
$tor->logOut();

<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 06/01/2018
 * Time: 15:45
 */

namespace qstraza\torPhp\Data;


class Orozje extends SpreadSheetData {
  protected $worksheet;
  protected $cellFeed;

  public function __construct($spreadsheetId = null, $worksheetName = null, $clientName = null) {
    parent::__construct($spreadsheetId, $worksheetName, $clientName);
    $this->worksheet = $this->worksheetFeed->getByTitle($this->worksheetName);
    $this->cellFeed = $this->worksheet->getCellFeed();
//    echo $cellFeed->getCell(1,2)->getInputValue();
  }

  public function getNerealizirane() {
    // Get all the rows from spreadsheet which are not yet realizirane (NE)
    /** @var \Google\Spreadsheet\ListFeed $listFeed */
    $listFeed = $this->worksheet->getListFeed(["sq" => "realizirano = Ne"]);
    $rows = [];
    foreach ($listFeed->getEntries() as $entry) {
      $entry->update(['torphp' => ""]);
      $values = $entry->getValues();
      if ($values['realizirano'] != "Ne") continue;
      foreach($this->parseMultipleSerialNumbers($values['serijska']) as $serijska) {
        /** @var \qstraza\torPhp\Data\OrozjeItem $row */
        $row = new OrozjeItem();
        $row->setDate($values['datum']);
        $row->setIme($values['ime']);
        $row->setOrganIzdaje($values['organizdaje']);
        $row->setStevilkaListine($values['številkalistine']);
        $row->setDatumIzdajeListine($values['datumizdajelistine']);
        $row->setProizvajalec($values['proizvajalec']);
        $row->setModel($values['model']);
        $row->setCal($values['kaliber']);
        $row->setSerijska($serijska);
        $row->setIzdelan($values['izdelan'] == "Da" ? TRUE : FALSE);
        $podjetje = $this->getCompanyData($values['ime']);

        if ($podjetje) {
          $row->setIsPodjetje(TRUE);
          $row->setNaslov($podjetje['naslov']);
          $row->setMesto($podjetje['mesto']);
          $row->setDrzava($podjetje['drzava']);
          $row->setVrstaKupca('Trgovec z orožjem');
          $row->setDavcna($podjetje['davčna']);
          $row->setVrstaDovoljenja($values['vrstadovoljenja']);
          $row->setOrganIzdaje($values['organizdaje']);
          $row->setStevilkaListine($values['številkalistine']);
          $row->setDatumIzdajeListine($values['datumizdajelistine']);
        }
        else {
          $row->setNaslov($values['naslov']);
          $row->setMesto($values['mesto']);
          $row->setDrzava($values['drzava']);
          $row->setVrstaKupca($values['vrstakupca']);
          $row->setVrstaDovoljenja($values['vrstadovoljenja']);
          $row->setStevilkaListine($values['stpriglasitvenegalista']);
          $row->getVrstaOrozja($values['vrstaorozja']);
        }


        $row->setIsEU($values['eu'] == 'Da' ? TRUE : FALSE);
        $row->setSpreadsheetEntry($entry);
        $rows[] = $row;
      }
    }
    return $rows;
  }
  public function getNeizdelane() {
    // Get all the rows from spreadsheet which are not yet izdelane (NE)
    /** @var \Google\Spreadsheet\ListFeed $listFeed */
    $listFeed = $this->worksheet->getListFeed(["sq" => "izdelano = Ne"]);
    $rows = [];
    foreach ($listFeed->getEntries() as $entry) {
      $entry->update(['torphp' => ""]);
      $values = $entry->getValues();
      if ($values['izdelano'] != "Ne") continue;
      foreach($this->parseMultipleSerialNumbers($values['serijska']) as $serijska) {
        /** @var \qstraza\torPhp\Data\OrozjeItem $row */
        $row = new OrozjeItem();
        $row->setOrozjeDelOrozja("Orožje");
        $row->setKategorija("D6");
        $row->setVrstaOrozja("Puška");
        $row->setProizvajalec($values['proizvajalec']);
        $row->setModel($values['model']);
        $row->setCal($values['kaliber']);
        $row->setSerijska($serijska);
        $row->setDate($values['datum']);

        $row->setSpreadsheetEntry($entry);
        $rows[] = $row;
      }
    }
    return $rows;
  }

  private function parseMultipleSerialNumbers($serials) {
    $serials = explode(",", $serials);
    $createdSerials = [];
    foreach ($serials as $key => &$serial) {
      $serial = trim($serial);
      if (substr_count($serial, "-") === 1) {
        // We found a "-" which means that serial has "from to", eg:
        // 111-114, which means we need to create 111,112,113,114.
        // eg2: 17A00321-17A00323 which translates to 17A00321,17A00322,17A00323
        // Strangely doing ++ on 17A00321 works as expected, even if you have
        // 17A00999, it will increase it to 17A01000.
        $parts = explode("-", $serial);
        $start = trim($parts[0]);
        $end = trim($parts[1]);
        // First part must be the same length as end part. Reason for this is
        // because some serials have only one - but they are not meant to be
        // continues, eg is Voltran: E4VP-17090299.
        if (strlen($start) === strlen($end)) {
          $createdSerials[] = $start;
          while ($start != $end) {
            $createdSerials[] = ++$start;
          }
          unset($serials[$key]);
        }
      }
    }
    if ($createdSerials) {
      $serials = array_merge($serials, $createdSerials);
    }
    return $serials;
  }

  public function logs($serial, $type, $msg, $isError) {
    static $i = 2;
    /** @var \Google\Spreadsheet\CellFeed $cellFeed */
    $cellFeed = $this->worksheetFeed->getByTitle("logs")->getCellFeed();
    $cellFeed->editCell($i, 1, $type);
    $cellFeed->editCell($i, 2, $serial);
    $cellFeed->editCell($i, 3, $msg);
    $date = new \DateTime();
    $cellFeed->editCell($i, 4, $date->format('d-m-Y H:i:s'));
    $cellFeed->editCell($i, 5, $isError ? "FAIL" : "OK");
    $i++;
  }

}

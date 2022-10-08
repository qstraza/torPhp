<?php
/**
 * Created by PhpStorm.
 * User: rok
 * Date: 06/01/2018
 * Time: 15:45
 */

namespace qstraza\torphp\Data;


use Google_Service_Sheets_ValueRange;

class Orozje extends SpreadSheetData
{
    protected $worksheet;
    protected $method;

    public function __construct($spreadsheetId = null, $worksheetName = null, $clientName = null)
    {
        parent::__construct($spreadsheetId, $worksheetName, $clientName);
    }

    public function getNerealizirane()
    {
        // Get all the rows from spreadsheet which are not yet realizirane (NE)
        $response = $this->getSpreadsheetValues($this->getWorksheetName(),'A:ZZ');
        $this->worksheet = $response;
        $this->method = "nerealizirane";
        $i = 1;
        $rows = [];
        foreach ($response as $entry) {
            $entry['rowIndex'] = $i;
            if ($entry[$this->getZapisnik('Realizirano')] == 'Ne') {
                if ($GLOBALS['multiSerialNumbersOptionEnabled']) {
                    $serialNumbers = $this->parseMultipleSerialNumbers($entry[$this->getZapisnik('Serijska')]);
                } else {
                    $serialNumbers = [$entry[$this->getZapisnik('Serijska')]];
                }
                foreach ($serialNumbers as $serijska) {
                    /** @var OrozjeItem $row */
                    $row = new OrozjeItem();
                    $user = new User();
                    $user->setIme(trim($entry[$this->getZapisnik('Ime')]));
                    $user->setNaslov(trim($entry[$this->getZapisnik('Naslov')]));
                    $user->setMesto(trim($entry[$this->getZapisnik('Mesto')]));
                    $user->setDrzava($entry[$this->getZapisnik('Država')]);
                    $user->setDavcna($entry[$this->getZapisnik('Davčna')]);
                    $user->setVrstaKupca($entry[$this->getZapisnik('Vrsta kupca')]);
                    $row->setUser($user);

                    $row->setDrzava($entry[$this->getZapisnik('Država')]);
                    $row->setDate($entry[$this->getZapisnik('Datum')]);
                    $row->setIsEU($entry[$this->getZapisnik('EU?')] == "Da");
                    $row->setVrstaDovoljenja($entry[$this->getZapisnik('Vrsta dovoljenja')]);
                    $row->setOrganIzdaje($entry[$this->getZapisnik('Organ izdaje')]);
                    $row->setStevilkaListine(trim($entry[$this->getZapisnik('Številka listine')]));
                    $row->setDatumIzdajeListine($entry[$this->getZapisnik('Datum izdaje listine')]);
                    $row->setKategorija($entry[$this->getZapisnik('Kategorija orožja')]);
                    $row->setProizvajalec(trim($entry[$this->getZapisnik('Proizvajalec')]));
                    $row->setZnamka(trim($entry[$this->getZapisnik('Proizvajalec')]));
                    $row->setModel(trim($entry[$this->getZapisnik('Model')]));
                    $row->setCal($entry[$this->getZapisnik('Kaliber')]);
                    $row->setSerijska($serijska);
                    $row->setOpombaTor($entry[$this->getZapisnik('Opomba TOR')]);

                    if ($GLOBALS['multiSerialNumbersOptionEnabled']) {
                        $row->setIzdelan($entry[$this->getZapisnik('Izdelano')] == "Da");
                    }

                    $row->setSpreadsheetEntry($entry);
                    $rows[] = $row;
                }
            }
            $i++;
        }
        return $rows;

    }

    public function getNeizdelane()
    {
        // Get all the rows from spreadsheet which are not yet izdelane (NE)
        $response = $this->getSpreadsheetValues($this->getWorksheetName(),'A:ZZ');
        $this->worksheet = $response;
        $this->method = "neizdelane";
        $i = 1;
        $rows = [];
        foreach ($response as $entry) {
            $entry['rowIndex'] = $i;
            if ($entry[$this->getZapisnik('Izdelano')] == 'Ne') {
                foreach ($this->parseMultipleSerialNumbers($entry[$this->getZapisnik('Serijska')]) as $serijska) {
                    /** @var OrozjeItem $row */
                    $row = new OrozjeItem();
                    $row->setOrozjeDelOrozja($entry[$this->getZapisnik('Orožje/del orožja')]);
                    $row->setKategorija($entry[$this->getZapisnik('Kategorija orožja')]);
                    $row->setVrstaOrozja($entry[$this->getZapisnik('Tip/vrsta orožja')]);
                    $row->setProizvajalec(trim($entry[$this->getZapisnik('Proizvajalec')]));
                    $row->setModel(trim($entry[$this->getZapisnik('Model')]));
                    $row->setCal($entry[$this->getZapisnik('Kaliber')]);
                    $row->setSerijska($serijska);
                    $row->setDate($entry[$this->getZapisnik('Datum')]);
                    $row->setOpombaTor($entry[$this->getZapisnik('Opomba TOR')]);

                    $row->setSpreadsheetEntry($entry);
                    $rows[] = $row;
                }
            }
            $i++;
        }
        return $rows;
    }

    private function parseMultipleSerialNumbers($serials)
    {
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

    public function logs(OrozjeItem $orozjeItem, $type, $msg, $isError)
    {
        $rowIndex = $orozjeItem->getSpreadsheetEntry()['rowIndex'];
        $letter = $this->columnToLetter($this->getZapisnik('TorPHP'));
        $body = date('d-m-Y H:i:s') . " - " . $msg;
        $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", $body);

        if (!$isError) {
            $letter = $this->columnToLetter($this->getZapisnik('Realizirano'));
            $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", "Da");
        }

    }

    public function logsMulti($rowIndex, $serial, $msg, $isError) {
        $letter = $this->columnToLetter($this->getZapisnik('TorPHP'));
        $cellValue = $this->getCellValue($this->getWorksheetName(), $letter . $rowIndex);
        $date = new \DateTime();
        $currentTime = $date->format('d-m-Y H:i:s');

        $text = $isError ? "FAIL - {$serial}" : "OK - {$serial}";
        $text .= " ({$msg}) / {$currentTime}\n";
        $body = $cellValue . $text;
        $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", $body);
    }

    public function setFieldDa($rowIndex, $type)
    {
        if ($type == "Izdelano") {
            $letter = $this->columnToLetter($this->getZapisnik('Izdelano'));
        }
        else {
            $letter = $this->columnToLetter($this->getZapisnik('Realizirano'));
        }
        $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", "Da");
    }

}

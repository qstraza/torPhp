<?php


namespace qstraza\torphp\Data;

use Google_Service_Sheets_ValueRange;

class NabavljenoOrozje extends SpreadSheetData
{
    public function __construct($spreadsheetId = null, $worksheetName = null, $clientName = null)
    {
        parent::__construct($spreadsheetId, $worksheetName, $clientName);
    }

    public function getNabavljeno()
    {
        $responseSupplier = $this->getSpreadsheetValues($this->getWorksheetName(), 'B2:B15');
        $supplierFields = [
            'naziv firme',
            'ulica',
            'mesto',
            'drzava',
            'davcna',
            'isEU',
            'vrsta kupca',
            'st dovoljenja',
            'datum izdaje dovoljenja',
            'datum prevzema',
            'komisijska prodaja',
            'stevilka prevzema',
            'Zazeni TOR',
        ];
        $supplier = [];
        for ($i = 0; $i < count($supplierFields); $i++) {
            $supplier[$supplierFields[$i]] = $responseSupplier[$i][0];
        }
        if (!strlen($supplier['Zazeni TOR'])) {
            return [];
        } else {
            $this->updateCell("{$this->getWorksheetName()}!B14", "");
        }
        $responseOrozje = $this->getSpreadsheetValues($this->getWorksheetName(), 'A16:J55');
        $rows = [];
        $i = 0;
        foreach ($responseOrozje as $entry) {
            if ($i++ == 0) {
                continue;
            }
            $entry['rowIndex'] = $i;
            $serialNumbers = $this->parseMultipleSerialNumbers($entry[$this->getZapisnik('Serijska')]);
            foreach ($serialNumbers as $serijska) {
                /** @var OrozjeItem $row */
                $row = new OrozjeItem();
                $user = new User();
                $user->setIme(trim($supplier['naziv firme']));
                $user->setNaslov(trim($supplier['ulica']));
                $user->setMesto(trim($supplier['mesto']));
                $user->setDavcna($supplier['davcna']);
                $user->setVrstaKupca($supplier['vrsta kupca']);
                $user->setDrzava($supplier['drzava']);
                $row->setUser($user);

                $row->setKomisijskaNabava($supplier['komisijska prodaja']);
                $row->setDrzava($supplier['drzava']);
                $row->setIsEU(strtolower($supplier['isEU']) == "da");
                $row->setStevilkaListine($supplier['st dovoljenja']);
                $row->setDatumIzdajeListine($supplier['datum izdaje dovoljenja']);
//                    $row->setDatumPrevzema($supplier['datum prevzema']);
                $row->setStevilkaPrevzema($supplier['stevilka prevzema']);
                $row->setOrganIzdaje('MNZ');

                $row->setDate($supplier['datum prevzema']);

                $row->setOrozjeDelOrozja($entry[$this->getZapisnik('Orožje/del orožja')]);
                $row->setKategorija($entry[$this->getZapisnik('Kategorija')]);
                $row->setVrstaOrozja($entry[$this->getZapisnik('Tip/Vrsta orožja')]);

                $row->setZnamka(trim($entry[$this->getZapisnik('znamka')]));
                $row->setModel(trim($entry[$this->getZapisnik('model')]));
                $row->setCal($entry[$this->getZapisnik('kaliber')]);
                $row->setSerijska(trim($serijska));
                $row->setProizvajalec(trim($entry[$this->getZapisnik('znamka')]));
                $row->setLetoIzdelave($entry[$this->getZapisnik('Leto izdelave')]);
                $row->setDrzavaProizvajalka($entry[$this->getZapisnik('Država proizvajalka')]);

                $row->setSpreadsheetEntry($entry);
                $rows[] = $row;
            }
        }
        return $rows;
    }

    private function parseMultipleSerialNumbers($serials)
    {
        $serials = explode(",", $serials);
        foreach ($serials as $key => &$serial) {
            $serial = trim($serial);
        }
        return $serials;
    }

    public function logs($rowIndex, $serial, $msg, $isError)
    {
        $rowIndex += 15;
        $letter = $this->columnToLetter($this->getZapisnik('Logs'));
        $cellValue = $this->getCellValue($this->getWorksheetName(), $letter . $rowIndex);

        $date = new \DateTime();
        $currentTime = $date->format('d-m-Y H:i:s');

        $text = $isError ? "FAIL - {$serial}" : "OK - {$serial}";
        $text .= " ({$msg}) / {$currentTime}\n";
        $body = $cellValue . $text;
        $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", $body);
    }
}
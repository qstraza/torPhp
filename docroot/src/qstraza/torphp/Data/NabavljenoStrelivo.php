<?php

namespace qstraza\torphp\Data;

use Google_Service_Sheets_ValueRange;

class NabavljenoStrelivo extends SpreadSheetData
{

    /**
     * NabavljenoStrelivo constructor.
     * @param mixed $spreadsheetId
     * @param mixed $worksheetName
     * @param mixed $clientName
     */
    public function __construct($spreadsheetId, $worksheetName, $clientName)
    {
        parent::__construct($spreadsheetId, $worksheetName, $clientName);
    }

    public function getNabavljeno() {
        $responseSupplier = $this->getSpreadsheetValues($this->getWorksheetName(),'B2:B15');
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
        for ($i = 0; $i <  count($supplierFields); $i++) {
            $supplier[$supplierFields[$i]] = $responseSupplier[$i][0];
        }
        if (!strlen($supplier['Zazeni TOR'])) {
            return [];
        }
        else {
            $this->updateCell("{$this->getWorksheetName()}!B14", "");
        }
        $response = $this->getSpreadsheetValues($this->getWorksheetName(),'A16:H55');
        $rows = [];
        $i = 0;
        foreach ($response as $entry) {
            if ($i++ == 0){
                continue;
            }
            $entry['rowIndex'] = $i;
            /** @var StrelivoItem $row */
            $row = new StrelivoItem();
            $user = new User();
            $user->setIme($supplier['naziv firme']);
            $user->setNaslov($supplier['ulica']);
            $user->setMesto($supplier['mesto']);
            $user->setDrzava($supplier['drzava']);
            $user->setDavcna($supplier['davcna']);
            $user->setVrstaKupca($supplier['vrsta kupca']);
            $row->setUser($user);

            $row->setDrzava($supplier['drzava']);
            $row->setKomisijskaNabava($supplier['komisijska prodaja']);
            $row->setIsEU(strtolower($supplier['isEU']) == "da");
            $row->setStevilkaListine($supplier['st dovoljenja']);
            $row->setDatumIzdajeListine($supplier['datum izdaje dovoljenja']);
            $row->setStevilkaPrevzema($supplier['stevilka prevzema']);
            $row->setOrganIzdaje('MNZ');

            $row->setDate($supplier['datum prevzema']);

            $row->setStrelivoDelStreliva($entry[$this->getZapisnik('Strelivo/Del streliva')]);
            $row->setTipVrstaStreliva($entry[$this->getZapisnik('Tip/Vrsta streliva')]);
            $row->setKolicina($entry[$this->getZapisnik('Količina')]);

            $row->setZnamka($entry[$this->getZapisnik('znamka')]);
//            $row->setEnota($entry[$this->getZapisnik('Enota')]);
            $row->setCal($entry[$this->getZapisnik('Kaliber')]);
            $row->setProizvajalec($entry[$this->getZapisnik('znamka')]);
            $row->setDrzavaProizvajalka($entry[$this->getZapisnik('Država proizvajalca')]);
            $row->setOpombaTor($entry[$this->getZapisnik('Opomba')]);
            $row->setSpreadsheetEntry($entry);
            $rows[] = $row;
        }
        return $rows;
    }

    public function logs($rowIndex, $msg)
    {
        $rowIndex += 15;
        $letter = $this->columnToLetter($this->getZapisnik('Logs'));
        $body = date('d-m-Y H:i:s') . " - " . $msg;
        $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", $body);
    }
}
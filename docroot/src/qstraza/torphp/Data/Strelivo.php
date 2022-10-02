<?php


namespace qstraza\torphp\Data;


class Strelivo extends SpreadSheetData
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
        $response = $this->getSpreadsheetValues($this->getWorksheetName(), 'A:ZZ');
        $this->worksheet = $response;
        $this->method = "nerealizirane";
        $i = 1;
        $rows = [];
        foreach ($response as $entry) {
            $entry['rowIndex'] = $i;
            if ($entry[$this->getZapisnik('Realizirano')] == 'Ne') {

                /** @var StrelivoItem $row */
                $row = new StrelivoItem();
                $user = new User();
                $user->setIme($entry[$this->getZapisnik('Ime')]);
                $user->setNaslov($entry[$this->getZapisnik('Naslov')]);
                $user->setMesto($entry[$this->getZapisnik('Mesto')]);
                $user->setDrzava($entry[$this->getZapisnik('Država')]);
                $user->setDavcna($entry[$this->getZapisnik('Davčna')]);
                $user->setVrstaKupca($entry[$this->getZapisnik('Vrsta kupca')]);
                $row->setUser($user);

                $row->setDrzava($entry[$this->getZapisnik('Država')]);
                $row->setDate($entry[$this->getZapisnik('Datum')]);
                $row->setIsEU($entry[$this->getZapisnik('EU?')] == "Da");
                $row->setVrstaDovoljenja($entry[$this->getZapisnik('Vrsta dovoljenja')]);
                $row->setOrganIzdaje($entry[$this->getZapisnik('Organ izdaje')]);
                $row->setStevilkaListine($entry[$this->getZapisnik('Številka listine')]);
                $row->setDatumIzdajeListine($entry[$this->getZapisnik('Datum izdaje listine')]);
                $row->setStrelivoDelStreliva($entry[$this->getZapisnik('Strelivo / del streliva')]);
                $row->setProizvajalec($entry[$this->getZapisnik('Proizvajalec')]);
                $row->setZnamka($entry[$this->getZapisnik('Proizvajalec')]);
                $row->setKolicina($entry[$this->getZapisnik('Količina')]);
                $row->setOpombaTor($entry[$this->getZapisnik('Opomba TOR')]);

                $row->setSpreadsheetEntry($entry);
                $rows[] = $row;

            }
            $i++;
        }
        return $rows;

    }

    public function logs($rowIndex, $msg, $isError)
    {
        $letter = $this->columnToLetter($this->getZapisnik('TorPHP'));
        $body = date('d-m-Y H:i:s') . " - " . $msg;
        $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", $body);

        if (!$isError) {
            $letter = $this->columnToLetter($this->getZapisnik('Realizirano'));
            $this->updateCell("{$this->getWorksheetName()}!{$letter}{$rowIndex}", "Da");
        }
    }
}
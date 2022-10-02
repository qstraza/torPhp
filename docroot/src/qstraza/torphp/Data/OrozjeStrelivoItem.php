<?php


namespace qstraza\torphp\Data;


class OrozjeStrelivoItem
{
    protected $date;
    protected $vrstaDovoljenja;
    protected $vrstaEvidence;
    protected $organIzdaje;
    protected $stevilkaListine;
    protected $datumIzdajeListine;
    protected $proizvajalec;
    protected $realiziranTor;
    protected $drzava;
    protected $isEU;
    protected $opombaTor;
    protected $cal;

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getVrstaDovoljenja()
    {
        return $this->vrstaDovoljenja;
    }

    /**
     * @param mixed $vrstaDovoljenja
     */
    public function setVrstaDovoljenja($vrstaDovoljenja): void
    {
        $this->vrstaDovoljenja = $vrstaDovoljenja;
    }

    /**
     * @return mixed
     */
    public function getVrstaEvidence()
    {
        return $this->vrstaEvidence;
    }

    /**
     * @param mixed $vrstaEvidence
     */
    public function setVrstaEvidence($vrstaEvidence): void
    {
        $this->vrstaEvidence = $vrstaEvidence;
    }

    /**
     * @return mixed
     */
    public function getOrganIzdaje()
    {
        return $this->organIzdaje;
    }

    /**
     * @param mixed $organIzdaje
     */
    public function setOrganIzdaje($organIzdaje): void
    {
        $this->organIzdaje = $organIzdaje;
    }

    /**
     * @return mixed
     */
    public function getStevilkaListine()
    {
        return $this->stevilkaListine;
    }

    /**
     * @param mixed $stevilkaListine
     */
    public function setStevilkaListine($stevilkaListine): void
    {
        $this->stevilkaListine = $stevilkaListine;
    }

    /**
     * @return mixed
     */
    public function getDatumIzdajeListine()
    {
        return $this->datumIzdajeListine;
    }

    /**
     * @param mixed $datumIzdajeListine
     */
    public function setDatumIzdajeListine($datumIzdajeListine): void
    {
        $this->datumIzdajeListine = $datumIzdajeListine;
    }

    /**
     * @return mixed
     */
    public function getProizvajalec()
    {
        return $this->proizvajalec;
    }

    /**
     * @param mixed $proizvajalec
     */
    public function setProizvajalec($proizvajalec): void
    {
        $this->proizvajalec = $proizvajalec;
    }

    /**
     * @return mixed
     */
    public function getRealiziranTor()
    {
        return $this->realiziranTor;
    }

    /**
     * @param mixed $realiziranTor
     */
    public function setRealiziranTor($realiziranTor): void
    {
        $this->realiziranTor = $realiziranTor;
    }

    /**
     * @return mixed
     */
    public function getDrzava()
    {
        return $this->drzava;
    }

    /**
     * @param mixed $drzava
     */
    public function setDrzava($drzava): void
    {
        $this->drzava = $drzava;
    }

    /**
     * @return mixed
     */
    public function getIsEU()
    {
        return $this->isEU;
    }

    /**
     * @param mixed $isEU
     */
    public function setIsEU($isEU): void
    {
        $this->isEU = $isEU;
    }

    /**
     * @return mixed
     */
    public function getOpombaTor()
    {
        return $this->opombaTor;
    }

    /**
     * @param mixed $opombaTor
     */
    public function setOpombaTor($opombaTor): void
    {
        $this->opombaTor = $opombaTor;
    }

    /**
     * @return mixed
     */
    public function getSpreadsheetEntry()
    {
        return $this->spreadsheetEntry;
    }

    /**
     * @param mixed $spreadsheetEntry
     */
    public function setSpreadsheetEntry($spreadsheetEntry): void
    {
        $this->spreadsheetEntry = $spreadsheetEntry;
    }

    /**
     * @return mixed
     */
    public function getReturnMessage()
    {
        return $this->returnMessage;
    }

    /**
     * @param mixed $returnMessage
     */
    public function setReturnMessage($returnMessage): void
    {
        $this->returnMessage = $returnMessage;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * @param bool $error
     */
    public function setError(bool $error): void
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getKomisijskaNabava()
    {
        return $this->komisijskaNabava;
    }

    /**
     * @param mixed $komisijskaNabava
     */
    public function setKomisijskaNabava($komisijskaNabava): void
    {
        $this->komisijskaNabava = $komisijskaNabava;
    }

    /**
     * @return mixed
     */
    public function getStevilkaPrevzema()
    {
        return $this->stevilkaPrevzema;
    }

    /**
     * @param mixed $stevilkaPrevzema
     */
    public function setStevilkaPrevzema($stevilkaPrevzema): void
    {
        $this->stevilkaPrevzema = $stevilkaPrevzema;
    }

    /**
     * @return mixed
     */
    public function getZnamka()
    {
        return $this->znamka;
    }

    /**
     * @param mixed $znamka
     */
    public function setZnamka($znamka): void
    {
        $this->znamka = $znamka;
    }

    /**
     * @return mixed
     */
    public function getDrzavaProizvajalka()
    {
        return $this->drzavaProizvajalka;
    }

    /**
     * @param mixed $drzavaProizvajalka
     */
    public function setDrzavaProizvajalka($drzavaProizvajalka): void
    {
        $this->drzavaProizvajalka = $drzavaProizvajalka;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }
    protected $spreadsheetEntry;
    protected $returnMessage;
    protected $error = false;
    protected $komisijskaNabava;
    protected $stevilkaPrevzema;
    protected $znamka;
    protected $drzavaProizvajalka;
    protected $user;

    /**
     * @return mixed
     */
    public function getCal()
    {
        return $this->cal;
    }

    /**
     * @param mixed $cal
     */
    public function setCal($cal): void
    {
        $this->cal = $cal;
    }
}
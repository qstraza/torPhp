<?php


namespace qstraza\torphp\Data;


class User
{
    protected $ime;
    protected $naslov;
    protected $mesto;
    protected $vrstaKupca;
    protected $drzava;
    protected $davcna;

    /**
     * @return mixed
     */
    public function getIme()
    {
        return $this->ime;
    }

    /**
     * @param mixed $ime
     */
    public function setIme($ime): void
    {
        $this->ime = $ime;
    }

    /**
     * @return mixed
     */
    public function getNaslov()
    {
        return $this->naslov;
    }

    /**
     * @param mixed $naslov
     */
    public function setNaslov($naslov): void
    {
        $this->naslov = $naslov;
    }

    /**
     * @return mixed
     */
    public function getMesto()
    {
        return $this->mesto;
    }

    /**
     * @param mixed $mesto
     */
    public function setMesto($mesto): void
    {
        $this->mesto = $mesto;
    }

    /**
     * @return mixed
     */
    public function getVrstaKupca()
    {
        return $this->vrstaKupca;
    }

    /**
     * @param mixed $vrstaKupca
     */
    public function setVrstaKupca($vrstaKupca): void
    {
        $this->vrstaKupca = $vrstaKupca;
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
    public function getDavcna()
    {
        return $this->davcna;
    }

    /**
     * @param mixed $davcna
     */
    public function setDavcna($davcna): void
    {
        $this->davcna = $davcna;
    }


}
<?php


namespace qstraza\torphp;


class TorNabava extends TorProxy
{
    public function __construct($clientName = null, $seleniumDriver = null) {
        // If there is not ClientName set yet, set it.
        if ($clientName) {
            $this->setClientName($clientName);
        }
        // If seleniumDriver has been passed upon creation, that means that browser
        // is already initialized and is opened, so we need to just set the driver
        // which was passed upon creation. If there is no seleniumDriver passed,
        // it means that browser is closed so we need to init it.
        if ($seleniumDriver) {
            $this->setSeleniumDriver($seleniumDriver);
        }
        else {
            $this->initBrowser();
        }
        // Go to corensponding menu.
//    $this->menuClick('TO20');
    }
    public function setOpomba($opomba) {
        $this->writeById('contentForm:vno_opombe', $opomba);
        return $this;
    }
    public function setKomisijskaNabava($komisijskaNabava)
    {
        $validOptions = [
            'Ne',
            'Da',
        ];
        if (!in_array($komisijskaNabava, $validOptions)) {
            throw new \Exception("Napačna izbira za komisijsko nabavo: {$komisijskaNabava}");
        }
        $this->clickById("contentForm:vno_komisijska_nabava");
        sleep(0.2);
        $this->clickById("contentForm:vno_komisijska_nabava_" . array_search($komisijskaNabava, $validOptions));
        return $this;
    }
    public function setDrzavaNabave($drzava, $isEU)
    {
        $this->clickById("contentForm:vno_w63_id_drzave_prodaje");
        sleep(0.2);
        $itemFromList = 0;
        if ($drzava == "Slovenia") {
            $itemFromList = 0;
        }
        elseif ($isEU) {
            $itemFromList = 1;
        }
        else {
            $itemFromList = 2;
        }
        $this->clickById("contentForm:vno_w63_id_drzave_prodaje_" . $itemFromList);
        return $this;
    }

    public function setOrganIzdaje($organIzdaje)
    {
        $this->writeById("contentForm:vno_organ_izdaje", $organIzdaje);
        return $this;
    }
    public function setStevilkaListine($stevilkaListine)
    {
        $this->writeById("contentForm:vno_stv_listine", $stevilkaListine);
        return $this;
    }
    public function setDatumIzdajeListine($datumIzdajeListine)
    {
        $this->writeById("contentForm:vno_dtm_izdaje_lst_input", $datumIzdajeListine);
        return $this;
    }
    public function setProizvajalec($proizvajalec)
    {
        $this->writeById("contentForm:vno_proizvajalec", $proizvajalec);
        return $this;
    }
    public function setDatumPrejemaOrozja($date)
    {
        $this->writeById("contentForm:vno_dtm_dog_input", $date);
        return $this;
    }
    public function setStevilkaDobavnice($stevilkaPrevzema)
    {
        $this->writeById("contentForm:vno_stv_dobavnice", $stevilkaPrevzema);
        return $this;
    }
    public function setKolicina($kolicina)
    {
        $this->writeById("contentForm:vno_kolicina", $kolicina);
        $this->clickById("contentForm:j_idt141");
        return $this;
    }
}
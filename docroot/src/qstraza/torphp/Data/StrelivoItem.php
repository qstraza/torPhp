<?php


namespace qstraza\torphp\Data;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use qstraza\torphp\Realizacija\TorRealizacijaStreliva;
use qstraza\torphp\TorNabavljenoStrelivo;

class StrelivoItem extends OrozjeStrelivoItem
{
    protected $strelivoDelStreliva;
    protected $tipVrstaStreliva;
    protected $kolicina;
    protected $enota;

    /**
     * StrelivoItem constructor.
     */
    public function __construct()
    {
    }

    public function nabavi(TorNabavljenoStrelivo $tor)
    {
        $tor->menuClick('nabavljeno prodano strelivo');
        $tor->setKomisijskaNabava($this->getKomisijskaNabava());
        $tor->setDrzavaNabave($this->getDrzava(), $this->getIsEU());
        $tor->selectSeller($this->getUser());

        if ($this->getDrzava() == "Slovenia") {
            $tor->setVrstaDovoljenja("NP - Listina ni potrebna");
        } else {
            if ($this->getIsEU()) {
                $tor->setVrstaDovoljenja("01 - Dovoljenje za vnos orožja v RS iz EU");
            } else {
                $tor->setVrstaDovoljenja("03 - Dovoljenje za uvoz orožja");
            }
            $tor->setOrganIzdaje($this->getOrganIzdaje());
            $tor->setStevilkaListine($this->getStevilkaListine());
            $tor->setDatumIzdajeListine($this->getDatumIzdajeListine());
        }
        $tor->setStrelivoDelStreliva($this->getStrelivoDelStreliva());
        $tor->setVrstaStreliva($this->getTipVrstaStreliva());
        $tor->setZnamka($this->getZnamka());
        $tor->setKaliber($this->getCal());
        // Če ni "2 - Smodnik izražen v kg"
        if (substr($this->getStrelivoDelStreliva(), 0, 1) != "2") {
            $tor->setKolicina((int)$this->getKolicina());
        }
        else { // če je "2 - Smodnik izražen v kg"
            $tor->setKolicina($this->getKolicina());
            $tor->setEnota("kg");
        }
        $tor->setProizvajalec($this->getProizvajalec());
        $tor->setDrzavaProizvajalka($this->getDrzavaProizvajalka());
        $tor->setDatumPrejemaOrozja($this->getDate());
        $tor->setStevilkaDobavnice($this->getStevilkaPrevzema());
        $tor->setOpomba($this->getOpombaTor());

        $error = $tor->confirmPage();

        if ($error !== null) {
            // We have an error.
            $this->returnMessage = $error;
            $this->error = true;
        } else {
            // All good.
            $this->returnMessage = "Nabavljeno";
        }
    }

    public function nabaviOdistegaDobavitelja(TorNabavljenoStrelivo $tor) {
        try {
            $buttonElement = $tor->getElementByCssSelector("#to23DoneDialogForm button:nth-child(2)");
            if (!$buttonElement->isDisplayed()) {
                return $this->nabavi($tor);
            }
            else {
                $buttonElement->click();
            }
            sleep(1);
        } catch (\Exception $e) {
            $this->returnMessage = $e->getMessage();
            $this->error = true;
            return;
        }
        $strelivoDelStrelivaCode = substr($this->getTipVrstaStreliva(), 0, 1);
        if ($strelivoDelStrelivaCode != $tor->getStrelivoDelStreliva()) {
            $tor->setStrelivoDelStreliva($this->getStrelivoDelStreliva());
        }
        if ($tor->getVrstaStreliva() != $this->getTipVrstaStreliva()) {
            $tor->setVrstaStreliva($this->getTipVrstaStreliva());
        }
        if ($tor->getZnamka() != $this->getZnamka()) {
            $tor->setZnamka($this->getZnamka());
        }
        $tor->setKaliber($this->getCal());


        // Če ni "2 - Smodnik izražen v kg"
        if (substr($this->getStrelivoDelStreliva(), 0, 1) != "2") {
            $tor->setKolicina((int)$this->getKolicina());
        }
        else { // če je "2 - Smodnik izražen v kg"
            $tor->setKolicina($this->getKolicina());
            $tor->setEnota("kg");
        }
        if ($tor->getProizvajalec() != $this->getProizvajalec()) {
            $tor->setProizvajalec($this->getProizvajalec());
        }
        if ($tor->getDrzavaProizvajalka() != $this->getDrzavaProizvajalka()) {
            $tor->setDrzavaProizvajalka($this->getDrzavaProizvajalka());
        }

        $tor->setOpomba($this->getOpombaTor());

        $error = $tor->confirmPage();

        if ($error !== null) {
            // We have an error.
            $this->returnMessage = $error;
            $this->error = true;
        } else {
            // All good.
            $this->returnMessage = "Nabavljeno";
        }
    }

    public function realiziraj(TorRealizacijaStreliva $tor)
    {
        $potrdiButtonSelector = "#main_content > div.card.main-frame > div > div:nth-child(4) > div.ui-toolbar.ui-widget.ui-widget-header.ui-corner-all > div > button:nth-child(3)";
        $confirmButtonSelector = "#contentForm\\:confirmDialog > div.ui-dialog-buttonpane.ui-dialog-footer.ui-widget-content > button.ui-confirmdialog-yes";

        $this->setVrstaEvidence("4 - Nabavljeno in prodano strelivo");
        $return = $tor->searchStrelivoDelStrelivaByNameCal($this->getCal(), $this->getStrelivoDelStreliva(), $this->getProizvajalec(), 'Ne', $this->getVrstaEvidence());
        if ($return) {
            $this->returnMessage = $return["error"];
            $this->error = true;
            return;
        }

        $kolicinaZaRealizirati = $this->getKolicina();
        $i = 0;
        while ($kolicinaZaRealizirati) {
            /** @var RemoteWebElement $element */
            $element = $tor->getSeleniumDriver()->findElement(WebDriverBy::cssSelector('#contentForm\:to11DataTable_data tr:nth-child(1)'));
            if (!$element) {
                $error = "Realiziral samo " . ($this->getKolicina() - $kolicinaZaRealizirati);
                $this->returnMessage = $error;
                return;
            }
            $element->findElement(WebDriverBy::cssSelector("td"))->click();
            sleep(1);
            $tor->getElementByCssSelector("#main_content > div.card.main-frame > div > div:nth-child(1) > div > div:nth-child(2) > div > button:nth-child(3)")->click();
            sleep(2);

            $errorStatus = $tor->getErrorStatus();
            if ($errorStatus) {
                $error = $errorStatus . "\nRealiziral samo " . ($this->getKolicina() - $kolicinaZaRealizirati) . " od " . $this->getKolicina();
                $this->returnMessage = $error;
                return;
            }

            $tor->setDrzavaProdaje($this->getDrzava(), $this->getIsEU());
            try {
                $izbiraZadnjegaKupcaElement = $tor->getElementByCssSelector("#contentForm\\:realizacija_panel_grid2_content > div > div:nth-child(2) > div > button:nth-child(2)");
            } catch (\Exception $e) {
                $izbiraZadnjegaKupcaElement = false;
            }
            if ($i > 0 && $izbiraZadnjegaKupcaElement) {
                $izbiraZadnjegaKupcaElement->click();
                sleep(1);
            }
            else {
                $tor->selectBuyer($this->getUser());
            }
            $tor->setVrstaDovoljenja($this->getVrstaDovoljenja());

            if ($this->getVrstaDovoljenja() != "NP - Listina ni potrebna") {
                $tor->setOrganIzdaje($this->getOrganIzdaje());
                $tor->setStevilkaListine($this->getStevilkaListine());
                $tor->setDatumIzdajeListine($this->getDatumIzdajeListine());
            }
            $tor->setDatumProdaje($this->date);
            $tor->setOpomba($this->getOpombaTor());

            $zaloga = $tor->getSeleniumDriver()->findElement(WebDriverBy::cssSelector("#contentForm\:ui_zaloga"))->getAttribute("value");

            if ($kolicinaZaRealizirati > $zaloga) {
                $tor->setProdanaKolicina($zaloga);
                $error = $tor->confirmPage(
                    $potrdiButtonSelector,
                    $confirmButtonSelector
                );
                if ($error !== null) {
                    // We have an error
                    $error = $error . "\nRealiziral samo " . ($this->getKolicina() - $kolicinaZaRealizirati);
                    $this->returnMessage = $error;
                    $this->error = true;
                    return;

                } else {
                    // All good.
                    $kolicinaZaRealizirati = $kolicinaZaRealizirati - $zaloga;
                    $tor->getElementByCssSelector("#to33DoneDialogForm > div > div > div > div:nth-child(3) > div.ui-toolbar-group-right > button")->click();
                    sleep(2);
                    $tor->getElementByCssSelector("#main_content > div.card.main-frame > div > div:nth-child(1) button")->click();
                    sleep(2);
                }
            }
            else {
                $tor->setProdanaKolicina($kolicinaZaRealizirati);
                $error = $tor->confirmPage(
                    $potrdiButtonSelector,
                    $confirmButtonSelector
                );
                if ($error !== null) {
                    // We have an error
                    $error = $error . "\nRealiziral samo " . ($this->getKolicina() - $kolicinaZaRealizirati) . " od " . $kolicinaZaRealizirati;
                    $this->returnMessage = $error;
                    $this->error = true;
                    return;
                }
                $this->returnMessage = "Realizirano";
                return;
            }
            $i++;
        }


        $this->returnMessage = "Realiziral samo " . $this->getKolicina() - $kolicinaZaRealizirati . " od " . $kolicinaZaRealizirati;
        $this->error = true;
        return;

    }

    /**
     * @return mixed
     */
    public function getStrelivoDelStreliva()
    {
        return $this->strelivoDelStreliva;
    }

    /**
     * @param mixed $strelivoDelStreliva
     */
    public function setStrelivoDelStreliva($strelivoDelStreliva): void
    {
        $this->strelivoDelStreliva = $strelivoDelStreliva;
    }

    /**
     * @return mixed
     */
    public function getTipVrstaStreliva()
    {
        return $this->tipVrstaStreliva;
    }

    /**
     * @param mixed $tipVrstaStreliva
     */
    public function setTipVrstaStreliva($tipVrstaStreliva): void
    {
        $this->tipVrstaStreliva = $tipVrstaStreliva;
    }

    /**
     * @return mixed
     */
    public function getKolicina()
    {
        return $this->kolicina;
    }

    /**
     * @param mixed $kolicina
     */
    public function setKolicina($kolicina): void
    {
        $this->kolicina = $kolicina;
    }

    /**
     * @return mixed
     */
    public function getEnota()
    {
        return $this->enota;
    }

    /**
     * @param mixed $enota
     */
    public function setEnota($enota): void
    {
        $this->enota = $enota;
    }



}
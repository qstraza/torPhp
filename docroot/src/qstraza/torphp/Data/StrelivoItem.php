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

        $error = $tor->confirmPage(159, 164);

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
        $potrdiButtonId = 221;
        $confirmButtonId = 226;

        $this->setVrstaEvidence("4 - Nabavljeno in prodano strelivo");
        $return = $tor->searchStrelivoDelStrelivaByName($this->getStrelivoDelStreliva(), $this->getProizvajalec(), 'Ne', $this->getVrstaEvidence());
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
            $tor->clickById('contentForm:j_idt59');
            sleep(2);

            $errorStatus = $tor->getErrorStatus();
            if ($errorStatus) {
                $error = $errorStatus . "\nRealiziral samo " . ($this->getKolicina() - $kolicinaZaRealizirati) . " od " . $this->getKolicina();
                $this->returnMessage = $error;
                return;
            }

            $tor->setDrzavaProdaje($this->getDrzava(), $this->getIsEU());
            try {
                $izbiraZadnjegaKupcaElement = $tor->getSeleniumDriver()->findElement(WebDriverBy::cssSelector("#contentForm\:j_idt184"));
            } catch (\Exception $e) {
                $izbiraZadnjegaKupcaElement = false;
            }
            if ($i > 0 && $izbiraZadnjegaKupcaElement) {
                $izbiraZadnjegaKupcaElement->click();
                sleep(1);
            }
            else {
                $tor->selectBuyer(185, $this->getVrstaEvidence(), $this->getUser());
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
                $error = $tor->confirmPage($potrdiButtonId, $confirmButtonId);
                if ($error !== null) {
                    // We have an error
                    $error = $error . "\nRealiziral samo " . ($this->getKolicina() - $kolicinaZaRealizirati);
                    $this->returnMessage = $error;
                    $this->error = true;
                    return;

                } else {
                    // All good.
                    $kolicinaZaRealizirati = $kolicinaZaRealizirati - $zaloga;
                    $tor->clickById("to33DoneDialogForm:j_idt288");
                    sleep(2);
                    $tor->clickById("contentForm:j_idt50");
                    sleep(2);
                }
            }
            else {
                $tor->setProdanaKolicina($kolicinaZaRealizirati);
                $error = $tor->confirmPage($potrdiButtonId, $confirmButtonId);
                if ($error !== null) {
                    // We have an error
                    $error = $error . "\nRealiziral samo " . ($this->getKolicina() - $kolicinaZaRealizirati) . " od " . $kolicinaZaRealizirati;
                    $this->returnMessage = $error;
                    $this->error = true;

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